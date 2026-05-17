<?php
// app/Http/Controllers/Api/User/MarketplaceTransactionController.php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketplaceTransactionResource;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceTransaction;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MarketplaceTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceTransaction::with(['product', 'seller'])
            ->where('buyer_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return MarketplaceTransactionResource::collection($transactions);
    }

    public function show(MarketplaceTransaction $transaction)
    {
        if ($transaction->buyer_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $transaction->load(['product', 'buyer', 'seller']);

        return response()->json([
            'status' => 'success',
            'data'   => new MarketplaceTransactionResource($transaction),
        ]);
    }

    public function store(Request $request, MarketplaceProduct $product)
    {
        if ($product->seller_id === Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak bisa membeli produk sendiri.'], 422);
        }

        if (!$product->is_available) {
            return response()->json(['status' => 'error', 'message' => 'Produk tidak tersedia.'], 422);
        }

        $request->validate([
            'quantity'       => 'required|integer|min:1|max:' . $product->stock_quantity,
            'buyer_name'     => 'required|string|max:255',
            'buyer_phone'    => 'required|string|max:20',
            'buyer_address'  => 'required|string',
            'pickup_method'  => 'required|in:pickup,delivery,meetup',
            'pickup_address' => 'nullable|string',
            'pickup_notes'   => 'nullable|string',
            'payment_method' => 'required|string|max:100',
        ]);

        $transaction = MarketplaceTransaction::create([
            'buyer_id'       => Auth::id(),
            'seller_id'      => $product->seller_id,
            'product_id'     => $product->id,
            'quantity'       => $request->quantity,
            'unit_price'     => $product->price,
            'total_amount'   => $product->price * $request->quantity,
            'buyer_name'     => $request->buyer_name,
            'buyer_phone'    => $request->buyer_phone,
            'buyer_address'  => $request->buyer_address,
            'pickup_method'  => $request->pickup_method,
            'pickup_address' => $request->pickup_address,
            'pickup_notes'   => $request->pickup_notes,
            'payment_method' => $request->payment_method,
            'status'         => 'pending',
            'payment_status' => 'pending',
        ]);

        NotificationService::pesananBaru(
            $transaction->seller_id,
            Auth::user()->name,
            $product->name,
            '/seller/orders/' . $transaction->id
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Transaksi berhasil dibuat.',
            'data'    => new MarketplaceTransactionResource($transaction->load(['product', 'seller'])),
        ], 201);
    }

    public function cancel(Request $request, MarketplaceTransaction $transaction)
    {
        if ($transaction->buyer_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        if (!$transaction->canBeCancelled()) {
            return response()->json(['status' => 'error', 'message' => 'Transaksi tidak dapat dibatalkan.'], 422);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        $transaction->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at'        => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Transaksi berhasil dibatalkan.',
        ]);
    }

    public function uploadPaymentProof(Request $request, MarketplaceTransaction $transaction)
    {
        if ($transaction->buyer_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($transaction->payment_proof) {
            Storage::disk('public')->delete($transaction->payment_proof);
        }

        $path = $request->file('payment_proof')->store('marketplace/payment-proofs', 'public');

        $transaction->update([
            'payment_proof'  => $path,
            'payment_status' => 'paid',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Bukti pembayaran berhasil diupload.',
            'data'    => ['payment_proof_url' => asset('storage/' . $path)],
        ]);
    }
}