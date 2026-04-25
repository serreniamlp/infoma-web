<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketplaceManagementController extends Controller
{
    // --- PRODUK ---

    public function products(Request $request)
    {
        $query = MarketplaceProduct::with(['seller']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('seller', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->withCount('transactions')->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total'    => MarketplaceProduct::count(),
            'active'   => MarketplaceProduct::where('status', 'active')->count(),
            'inactive' => MarketplaceProduct::where('status', 'inactive')->count(),
            'sold_out' => MarketplaceProduct::where('stock_quantity', 0)->count(),
        ];

        return view('admin.marketplace.products', compact('products', 'stats'));
    }

    public function toggleProductStatus(MarketplaceProduct $product)
    {
        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        $product->update(['status' => $newStatus]);
        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Produk \"{$product->name}\" berhasil {$label}.");
    }

    public function destroyProduct(MarketplaceProduct $product)
    {
        $name = $product->name;
        $product->delete();
        return redirect()->route('admin.marketplace.products')->with('success', "Produk \"{$name}\" berhasil dihapus.");
    }

    // --- TRANSAKSI ---

    public function transactions(Request $request)
    {
        $query = MarketplaceTransaction::with(['buyer', 'seller', 'product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('buyer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total'     => MarketplaceTransaction::count(),
            'pending'   => MarketplaceTransaction::where('status', 'pending')->count(),
            'completed' => MarketplaceTransaction::where('status', 'completed')->count(),
            'cancelled' => MarketplaceTransaction::where('status', 'cancelled')->count(),
            'revenue'   => MarketplaceTransaction::where('status', 'completed')->sum('total_amount'),
        ];

        return view('admin.marketplace.transactions', compact('transactions', 'stats'));
    }

    public function showTransaction(MarketplaceTransaction $transaction)
    {
        $transaction->load(['buyer', 'seller', 'product']);
        return view('admin.marketplace.transaction-show', compact('transaction'));
    }

    // --- LAPORAN ---

    public function report(Request $request)
    {
        $period   = $request->period ?? 'this_month';
        $dateFrom = match ($period) {
            'this_week'  => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'this_year'  => now()->startOfYear(),
            'custom'     => ($request->filled('date_from') ? \Carbon\Carbon::parse($request->date_from) : now()->startOfMonth()),
            default      => now()->startOfMonth(),
        };
        $dateTo = match ($period) {
            'last_month' => now()->subMonth()->endOfMonth(),
            'custom'     => ($request->filled('date_to') ? \Carbon\Carbon::parse($request->date_to) : now()),
            default      => now(),
        };

        $summary = [
            'total_transactions' => MarketplaceTransaction::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'completed'          => MarketplaceTransaction::where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'cancelled'          => MarketplaceTransaction::where('status', 'cancelled')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'revenue'            => MarketplaceTransaction::where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('total_amount'),
        ];

        $dailyRevenue = MarketplaceTransaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as revenue')
            )->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')->orderBy('date')->get();

        $topProducts = MarketplaceProduct::withCount(['transactions as order_count' => fn($q) =>
                $q->whereBetween('created_at', [$dateFrom, $dateTo])
            ])
            ->withSum(['transactions as revenue' => fn($q) =>
                $q->where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo])
            ], 'total_amount')
            ->orderByDesc('order_count')->limit(10)->get();

        $topSellers = \App\Models\User::where('is_seller', true)
            ->withCount(['marketplaceTransactionsAsSeller as order_count' => fn($q) =>
                $q->whereBetween('created_at', [$dateFrom, $dateTo])
            ])
            ->withSum(['marketplaceTransactionsAsSeller as revenue' => fn($q) =>
                $q->where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo])
            ], 'total_amount')
            ->orderByDesc('revenue')->limit(10)->get();

        return view('admin.marketplace.report', compact(
            'summary', 'dailyRevenue', 'topProducts', 'topSellers',
            'period', 'dateFrom', 'dateTo'
        ));
    }
}
