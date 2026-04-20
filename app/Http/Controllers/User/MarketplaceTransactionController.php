<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceTransactionController extends Controller
{
    /**
     * Display a listing of user's marketplace transactions.
     */
    public function index(Request $request)
    {
        $query = MarketplaceTransaction::with(['product', 'buyer', 'seller'])
            ->where('buyer_id', Auth::id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search by transaction code or product name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        // Statistics for user dashboard
        $stats = [
            'total' => MarketplaceTransaction::where('buyer_id', Auth::id())->count(),
            'pending' => MarketplaceTransaction::where('buyer_id', Auth::id())->where('status', 'pending')->count(),
            'completed' => MarketplaceTransaction::where('buyer_id', Auth::id())->where('status', 'completed')->count(),
            'total_spent' => MarketplaceTransaction::where('buyer_id', Auth::id())
                ->where('status', 'completed')->sum('total_amount'),
        ];

        return view('user.marketplace.transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Display the specified transaction.
     */
    public function show(MarketplaceTransaction $transaction)
    {
        // Check if user is the buyer
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $transaction->load(['product', 'buyer', 'seller', 'rating']);

        return view('user.marketplace.transactions.show', compact('transaction'));
    }

    /**
     * Upload payment proof for transaction.
     */
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
            \Storage::disk('public')->delete($transaction->payment_proof);
        }

        $path = $request->file('payment_proof')->store('marketplace/payment-proofs', 'public');

        $transaction->update([
            'payment_proof' => $path,
            'payment_status' => 'paid',
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload!');
    }

    /**
     * Rate a completed transaction.
     */
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
            'rateable_type' => \App\Models\MarketplaceProduct::class,
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

    /**
     * Cancel a transaction (only if pending).
     */
    public function cancel(Request $request, MarketplaceTransaction $transaction)
    {
        // Check if user is the buyer
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$transaction->canBeCancelled()) {
            return redirect()->back()->with('error', 'Transaksi tidak dapat dibatalkan!');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        $transaction->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at' => now(),
        ]);

        // Restore product stock
        $transaction->product->increment('stock_quantity', $transaction->quantity);

        return redirect()->back()->with('success', 'Transaksi berhasil dibatalkan!');
    }
}
