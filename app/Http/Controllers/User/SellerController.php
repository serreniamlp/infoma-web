<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceTransaction;
use App\Models\MarketplaceProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SellerController extends Controller
{
    /**
     * Halaman aktivasi seller
     */
    public function index()
    {
        $user = Auth::user();

        // ← TAMBAH: cek S&K dulu sebelum apapun
        if (!$user->hasAcceptedTerms()) {
            return redirect()->route('verification.terms', [
                'type'        => 'seller',
                'redirect_to' => route('user.marketplace.sell'),
            ]);
        }

        // Sudah aktif sebagai seller
        if ($user->isSeller()) {
            return redirect()->route('user.marketplace.seller.home');
        }

        // Sedang menunggu approval
        if ($user->seller_status === 'pending') {
            return view('user.marketplace.become-seller', ['status' => 'pending']);
        }

        // Ditolak
        if ($user->seller_status === 'rejected') {
            return view('user.marketplace.become-seller', ['status' => 'rejected']);
        }

        return view('user.marketplace.become-seller', ['status' => 'none']);
    }

    /**
     * Submit pengajuan seller (upload KTP)
     */
// app/Http/Controllers/User/SellerController.php
// Ganti method activate() dengan ini:

    public function activate(Request $request)
    {
        $user = Auth::user();

        if ($user->isSeller()) {
            return redirect()->route('user.marketplace.seller.home')
                ->with('info', 'Anda sudah terdaftar sebagai penjual.');
        }

        if ($user->seller_status === 'pending') {
            return redirect()->back()->with('info', 'Pengajuan Anda sedang ditinjau admin.');
        }

        $request->validate([
            'seller_nik'    => ['required', 'digits:16'],
            'seller_ktp'    => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'seller_selfie' => ['required', 'string'], // base64 dari kamera
        ], [
            'seller_nik.required' => 'NIK wajib diisi.',
            'seller_nik.digits'   => 'NIK harus 16 digit.',
            'seller_ktp.required' => 'Foto KTP wajib diunggah.',
            'seller_ktp.image'    => 'File KTP harus berupa gambar.',
            'seller_ktp.max'      => 'Ukuran foto KTP maksimal 2MB.',
            'seller_selfie.required' => 'Foto selfie wajib diambil.',
        ]);

        // Simpan foto KTP (upload biasa)
        $ktpPath = $request->file('seller_ktp')->store('seller-ktp', 'public');

        // Simpan foto selfie (dari base64 kamera)
        $selfieBase64 = $request->seller_selfie;

        // Validasi format base64
        if (!preg_match('/^data:image\/(\w+);base64,/', $selfieBase64, $matches)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['seller_selfie' => 'Format foto selfie tidak valid. Silakan ambil ulang.']);
        }

        $imageData   = substr($selfieBase64, strpos($selfieBase64, ',') + 1);
        $imageData   = base64_decode($imageData);
        $extension   = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $selfieName  = 'seller-selfie/' . uniqid('selfie_', true) . '.' . $extension;

        \Illuminate\Support\Facades\Storage::disk('public')->put($selfieName, $imageData);

        $user->update([
            'seller_nik'    => $request->seller_nik,
            'seller_ktp'    => $ktpPath,
            'seller_selfie' => $selfieName,
            'seller_status' => 'pending',
        ]);

        return redirect()->route('user.marketplace.sell')
            ->with('success', 'Pengajuan penjual berhasil dikirim! Tunggu konfirmasi admin dalam 1×24 jam.');
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
            'total_products'   => MarketplaceProduct::where('seller_id', $sellerId)->count(),
            'active_products'  => MarketplaceProduct::where('seller_id', $sellerId)->where('status', 'active')->count(),
            'total_orders'     => MarketplaceTransaction::where('seller_id', $sellerId)->count(),
            'pending_orders'   => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'completed_orders' => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'completed')->count(),
            'total_revenue'    => MarketplaceTransaction::where('seller_id', $sellerId)->where('status', 'completed')->sum('total_amount'),
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
        if ($transaction->seller_id != Auth::id()) {
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
        if ($transaction->seller_id != Auth::id()) {
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
        NotificationService::statusPesananDiupdate(
            $transaction->buyer_id,
            $transaction->product->name,
            $request->status,
            route('user.marketplace.transactions.show', $transaction->id)
        );

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    public function report(Request $request)
    {
        if (!Auth::user()->isSeller()) {
            return redirect()->route('user.marketplace.sell');
        }

        $sellerId = Auth::id();
        $period   = $request->period ?? 'this_month';

        $dateFrom = match($period) {
            'this_week'  => Carbon::now()->startOfWeek(),
            'this_month' => Carbon::now()->startOfMonth(),
            'last_month' => Carbon::now()->subMonth()->startOfMonth(),
            'this_year'  => Carbon::now()->startOfYear(),
            'custom'     => ($request->filled('date_from')
                                ? Carbon::parse($request->date_from)->startOfDay()
                                : Carbon::now()->startOfMonth()),
            default      => Carbon::now()->startOfMonth(),
        };

        $dateTo = match($period) {
            'last_month' => Carbon::now()->subMonth()->endOfMonth(),
            'custom'     => ($request->filled('date_to')
                                ? Carbon::parse($request->date_to)->endOfDay()
                                : Carbon::now()->endOfDay()),
            default      => Carbon::now()->endOfDay(),
        };

        // --- Summary ---
        $baseQuery = MarketplaceTransaction::where('seller_id', $sellerId)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        $totalOrders     = (clone $baseQuery)->count();
        $pendingOrders   = (clone $baseQuery)->where('status', 'pending')->count();
        $completedOrders = (clone $baseQuery)->where('status', 'completed')->count();
        $cancelledOrders = (clone $baseQuery)->where('status', 'cancelled')->count();
        $totalRevenue    = (clone $baseQuery)->where('status', 'completed')->sum('total_amount');

        // --- Revenue per produk ---
        $revenuePerProduct = MarketplaceProduct::where('seller_id', $sellerId)
            ->withCount(['transactions as order_count' => fn($q) =>
                $q->whereBetween('created_at', [$dateFrom, $dateTo])
            ])
            ->withSum(['transactions as revenue' => fn($q) =>
                $q->where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
            ], 'total_amount')
            ->orderByDesc('revenue')
            ->get();

        // --- Detail transaksi ---
        $transactions = MarketplaceTransaction::with(['product', 'buyer'])
            ->where('seller_id', $sellerId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // --- Chart: revenue harian ---
        $dailyRevenue = MarketplaceTransaction::where('seller_id', $sellerId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_revenue'    => $totalRevenue,
            'total_orders'     => $totalOrders,
            'pending_orders'   => $pendingOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'confirmed_orders' => (clone $baseQuery)->where('status', 'confirmed')->count(),
        ];

        return view('user.marketplace.seller-report', compact(
            'summary', 'revenuePerProduct', 'transactions',
            'dailyRevenue', 'period', 'dateFrom', 'dateTo'
        ));
    }
}
