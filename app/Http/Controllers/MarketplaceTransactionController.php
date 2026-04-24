<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceProduct;
use App\Models\MarketplaceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MarketplaceTransactionController extends Controller
{
    public function index()
    {
        $transactions = MarketplaceTransaction::with(['product', 'buyer', 'seller'])
            ->where('buyer_id', Auth::id())
            ->orWhere('seller_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('marketplace.transactions.index', compact('transactions'));
    }

    public function show(MarketplaceTransaction $transaction)
    {
        // Check if user is involved in this transaction
        if ($transaction->buyer_id !== Auth::id() && $transaction->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $transaction->load(['product', 'buyer', 'seller', 'rating']);

        return view('marketplace.transactions.show', compact('transaction'));
    }

    public function create(MarketplaceProduct $product)
    {
        if ($product->seller_id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat membeli produk sendiri!');
        }

        if (!$product->is_available) {
            return redirect()->back()->with('error', 'Produk tidak tersedia!');
        }

        return view('marketplace.transactions.create', compact('product'));
    }

    public function store(Request $request, MarketplaceProduct $product)
    {
        if ($product->seller_id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat membeli produk sendiri!');
        }

        if (!$product->is_available) {
            return redirect()->back()->with('error', 'Produk tidak tersedia!');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->stock_quantity,
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'required|string|max:20',
            'buyer_address' => 'required|string',
            'pickup_method' => 'required|in:pickup,delivery,meetup',
            'pickup_address' => 'nullable|string',
            'pickup_notes' => 'nullable|string',
            'payment_method' => 'required|string|max:100',
        ]);

        $totalAmount = $product->price * $request->quantity;

        $transaction = MarketplaceTransaction::create([
            'buyer_id' => Auth::id(),
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'unit_price' => $product->price,
            'total_amount' => $totalAmount,
            'buyer_name' => $request->buyer_name,
            'buyer_phone' => $request->buyer_phone,
            'buyer_address' => $request->buyer_address,
            'pickup_method' => $request->pickup_method,
            'pickup_address' => $request->pickup_address,
            'pickup_notes' => $request->pickup_notes,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        // Update product stock
        $product->decrement('stock_quantity', $request->quantity);

        return redirect()->route('marketplace.transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil dibuat! Silakan lakukan pembayaran.');
    }

    public function updateStatus(Request $request, MarketplaceTransaction $transaction)
    {
        // Check if user is the seller
        if ($transaction->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:confirmed,in_progress,completed,cancelled',
            'seller_notes' => 'nullable|string',
            'cancellation_reason' => 'nullable|string|required_if:status,cancelled',
        ]);

        $data = $request->only(['status', 'seller_notes']);

        if ($request->status === 'cancelled') {
            $data['cancellation_reason'] = $request->cancellation_reason;
            $data['cancelled_at'] = now();

            // Restore product stock
            $transaction->product->increment('stock_quantity', $transaction->quantity);
        } elseif ($request->status === 'completed') {
            $data['completed_at'] = now();
        }

        $transaction->update($data);

        return redirect()->back()->with('success', 'Status transaksi berhasil diperbarui!');
    }

    public function uploadPaymentProof(Request $request, MarketplaceTransaction $transaction)
    {
        // Check if user is the buyer
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old payment proof
        if ($transaction->payment_proof) {
            Storage::disk('public')->delete($transaction->payment_proof);
        }

        $path = $request->file('payment_proof')->store('marketplace/payment-proofs', 'public');

        $transaction->update([
            'payment_proof' => $path,
            'payment_status' => 'paid',
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload!');
    }

    public function rate(Request $request, MarketplaceTransaction $transaction)
    {
        // Check if user is the buyer and transaction is completed
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($transaction->status !== 'completed') {
            return redirect()->back()->with('error', 'Anda hanya dapat memberikan rating untuk transaksi yang sudah selesai!');
        }

        if ($transaction->rating) {
            return redirect()->back()->with('error', 'Anda sudah memberikan rating untuk transaksi ini!');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_recommended' => 'nullable|boolean',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'rateable_type' => MarketplaceProduct::class,
            'rateable_id' => $transaction->product_id,
            'transaction_id' => $transaction->id,
            'rating' => $request->rating,
            'review' => $request->review,
            'is_recommended' => $request->boolean('is_recommended'),
        ];

        // Handle rating images
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('marketplace/ratings', 'public');
                $images[] = $path;
            }
            $data['images'] = $images;
        }

        $transaction->rating()->create($data);

        return redirect()->back()->with('success', 'Rating berhasil diberikan!');
    }
}
