<?php
// app/Http/Controllers/Api/User/SellerController.php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketplaceProductResource;
use App\Http\Resources\MarketplaceTransactionResource;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceTransaction;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerController extends Controller
{
    // Status seller
    public function status()
    {
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'is_seller'        => $user->is_seller,
                'seller_status'    => $user->seller_status ?? 'none',
                'rejection_reason' => $user->seller_rejection_reason,
            ],
        ]);
    }

    // Ajukan jadi seller
    public function activate(Request $request)
    {
        $user = Auth::user();

        if ($user->isSeller()) {
            return response()->json(['status' => 'error', 'message' => 'Kamu sudah terdaftar sebagai seller.'], 422);
        }

        if ($user->seller_status === 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Pengajuan sedang ditinjau admin.'], 422);
        }

        $request->validate([
            'seller_nik'    => 'required|digits:16',
            'seller_ktp'    => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'seller_selfie' => 'required|string', // base64
        ]);

        $ktpPath = $request->file('seller_ktp')->store('seller-ktp', 'public');

        $selfieBase64 = $request->seller_selfie;
        if (!preg_match('/^data:image\/(\w+);base64,/', $selfieBase64, $matches)) {
            return response()->json(['status' => 'error', 'message' => 'Format foto selfie tidak valid.'], 422);
        }

        $imageData   = base64_decode(substr($selfieBase64, strpos($selfieBase64, ',') + 1));
        $extension   = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $selfiePath  = 'seller-selfie/' . uniqid('selfie_', true) . '.' . $extension;
        Storage::disk('public')->put($selfiePath, $imageData);

        $user->update([
            'seller_nik'    => $request->seller_nik,
            'seller_ktp'    => $ktpPath,
            'seller_selfie' => $selfiePath,
            'seller_status' => 'pending',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengajuan seller berhasil dikirim. Tunggu konfirmasi admin.',
        ]);
    }

    // Dashboard seller
    public function home()
    {
        if (!Auth::user()->isSeller()) {
            return response()->json(['status' => 'error', 'message' => 'Kamu belum terdaftar sebagai seller.'], 403);
        }

        $sellerId = Auth::id();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_products'   => MarketplaceProduct::where('seller_id', $sellerId)->count(),
                'active_products'  => MarketplaceProduct::where('seller_id', $sellerId)->where('status', 'active')->count(),
                'total_orders'     => MarketplaceTransaction::where('seller_id', $sellerId)->count(),
                'pending_orders'   => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'pending')->count(),
                'completed_orders' => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'completed')->count(),
                'total_revenue'    => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'completed')->sum('total_amount'),
            ],
        ]);
    }

    // List produk seller
    public function products(Request $request)
    {
        if (!Auth::user()->isSeller()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $products = MarketplaceProduct::with('category')
            ->where('seller_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return MarketplaceProductResource::collection($products);
    }

    // Tambah produk
    public function storeProduct(Request $request)
    {
        if (!Auth::user()->isSeller()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'required|string',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id'    => 'required|exists:product_categories,id',
            'condition'      => 'nullable|in:new,used',
            'images'         => 'nullable|array|max:5',
            'images.*'       => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except(['images']);
        $data['seller_id'] = Auth::id();
        $data['status']    = 'active';

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $img) {
                $images[] = $img->store('marketplace/products', 'public');
            }
            $data['images'] = $images;
        }

        $product = MarketplaceProduct::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk berhasil ditambahkan.',
            'data'    => new MarketplaceProductResource($product->load('category')),
        ], 201);
    }

    // Update produk
    public function updateProduct(Request $request, MarketplaceProduct $product)
    {
        if ($product->seller_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name'           => 'sometimes|string|max:255',
            'description'    => 'sometimes|string',
            'price'          => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'category_id'    => 'sometimes|exists:product_categories,id',
            'condition'      => 'nullable|in:new,used',
            'status'         => 'sometimes|in:active,inactive',
        ]);

        $product->update($request->except(['images', '_method']));

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk berhasil diupdate.',
            'data'    => new MarketplaceProductResource($product->load('category')),
        ]);
    }

    // Hapus produk
    public function destroyProduct(MarketplaceProduct $product)
    {
        if ($product->seller_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $product->delete();

        return response()->json(['status' => 'success', 'message' => 'Produk berhasil dihapus.']);
    }

    // List pesanan masuk
    public function orders(Request $request)
    {
        if (!Auth::user()->isSeller()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $query = MarketplaceTransaction::with(['product', 'buyer'])
            ->where('seller_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return MarketplaceTransactionResource::collection($orders);
    }

    // Detail pesanan
    public function orderShow(MarketplaceTransaction $transaction)
    {
        if ($transaction->seller_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $transaction->load(['product', 'buyer']);

        return response()->json([
            'status' => 'success',
            'data'   => new MarketplaceTransactionResource($transaction),
        ]);
    }

    // Update status pesanan
    public function updateOrderStatus(Request $request, MarketplaceTransaction $transaction)
    {
        if ($transaction->seller_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'status'              => 'required|in:confirmed,in_progress,completed,cancelled',
            'seller_notes'        => 'nullable|string|max:500',
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string|max:500',
        ]);

        $data = [
            'status'       => $request->status,
            'seller_notes' => $request->seller_notes,
        ];

        if ($request->status === 'confirmed') {
            $transaction->product->decrement('stock_quantity', $transaction->quantity);
        } elseif ($request->status === 'cancelled') {
            $data['cancellation_reason'] = $request->cancellation_reason;
            $data['cancelled_at']        = now();
        } elseif ($request->status === 'completed') {
            $data['completed_at'] = now();
        }

        $transaction->update($data);

        NotificationService::statusPesananDiupdate(
            $transaction->buyer_id,
            $transaction->product->name,
            $request->status,
            '/transactions/' . $transaction->id
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Status pesanan berhasil diupdate.',
            'data'    => new MarketplaceTransactionResource($transaction->fresh()->load(['product', 'buyer'])),
        ]);
    }
}