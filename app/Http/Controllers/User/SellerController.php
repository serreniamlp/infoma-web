<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceTransaction;
use App\Models\MarketplaceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    /**
     * Halaman aktivasi seller
     */
    public function index()
    {
        if (Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.seller.home');
        }

        return view('user.marketplace.become-seller');
    }

    /**
     * Aktivasi is_seller
     */
    public function activate()
    {
        $user = Auth::user();

        if ($user->isSeller()) {
            return redirect()->route('user.marketplace.seller.home')
                ->with('info', 'Anda sudah terdaftar sebagai penjual.');
        }

        $user->update(['is_seller' => true]);

        return redirect()->route('user.marketplace.seller.home')
            ->with('success', 'Selamat! Akun penjual Anda sudah aktif.');
    }

    /**
     * Beranda seller
     */
    public function home()
    {
        if (!Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell')
                ->with('error', 'Aktifkan akun penjual terlebih dahulu.');
        }

        $sellerId = Auth::id();

        $stats = [
            'total_products'     => MarketplaceProduct::where('seller_id', $sellerId)->count(),
            'active_products'    => MarketplaceProduct::where('seller_id', $sellerId)->where('status', 'active')->count(),
            'total_orders'       => MarketplaceTransaction::where('seller_id', $sellerId)->count(),
            'pending_orders'     => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'completed_orders'   => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'completed')->count(),
            'total_revenue'      => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'completed')->sum('total_amount'),
        ];

        $recentOrders = MarketplaceTransaction::with(['product', 'buyer'])
            ->where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentProducts = MarketplaceProduct::where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        return view('user.marketplace.seller-home', compact('stats', 'recentOrders', 'recentProducts'));
    }

    /**
     * Kelola pesanan (dari sisi seller)
     */
    public function orders(Request $request)
    {
        if (!Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell');
        }

        $query = MarketplaceTransaction::with(['product', 'buyer'])
            ->where('seller_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'pending'   => MarketplaceTransaction::where('seller_id', Auth::id())->where('status', 'pending')->count(),
            'confirmed' => MarketplaceTransaction::where('seller_id', Auth::id())->where('status', 'confirmed')->count(),
            'completed' => MarketplaceTransaction::where('seller_id', Auth::id())->where('status', 'completed')->count(),
            'cancelled' => MarketplaceTransaction::where('seller_id', Auth::id())->where('status', 'cancelled')->count(),
        ];

        return view('user.marketplace.orders.index', compact('orders', 'stats'));
    }

    /**
     * Detail pesanan
     */
    public function orderShow(MarketplaceTransaction $transaction)
    {
        if ($transaction->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $transaction->load(['product', 'buyer', 'rating']);

        return view('user.marketplace.orders.show', compact('transaction'));
    }

    /**
     * Update status pesanan
     */
    public function updateOrderStatus(Request $request, MarketplaceTransaction $transaction)
    {
        if ($transaction->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
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

            if (in_array($transaction->status, ['confirmed', 'in_progress'])) {
                $transaction->product->increment('stock_quantity', $transaction->quantity);
            }

        } elseif ($request->status === 'completed') {
            $data['completed_at'] = now();
        }

        $transaction->update($data);

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }
}