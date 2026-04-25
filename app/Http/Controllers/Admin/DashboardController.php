<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceTransaction;
use App\Models\UserActivity;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // FIX: pakai role baru, bukan 'provider' yang sudah dihapus
        $totalUsers              = User::count();
        $totalMahasiswa          = User::whereHas('roles', fn($q) => $q->where('name', 'user'))->count();
        $totalProviderResidence  = User::whereHas('roles', fn($q) => $q->where('name', 'provider_residence'))->count();
        $totalProviderEvent      = User::whereHas('roles', fn($q) => $q->where('name', 'provider_event'))->count();
        $totalSeller             = User::where('is_seller', true)->count();

        // Pending approvals
        $pendingSellerApproval   = User::where('seller_status', 'pending')->count();
        $pendingProviderApproval = User::where('provider_status', 'pending')->count();

        // Konten
        $totalResidences  = Residence::count();
        $activeResidences = Residence::where('is_active', true)->count();
        $totalActivities  = Activity::count();
        $activeActivities = Activity::where('is_active', true)->where('registration_deadline', '>', now())->count();
        $totalProducts    = MarketplaceProduct::count();
        $activeProducts   = MarketplaceProduct::where('status', 'active')->count();

        // Booking
        $totalBookings     = Booking::count();
        $pendingBookings   = Booking::where('status', 'pending')->count();
        $completedBookings = Booking::where('status', 'completed')->count();

        // Marketplace
        $totalTransactions     = MarketplaceTransaction::count();
        $completedTransactions = MarketplaceTransaction::where('status', 'completed')->count();
        $marketplaceRevenue    = MarketplaceTransaction::where('status', 'completed')->sum('total_amount');

        // Bulan ini
        $bookingsThisMonth     = Booking::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $transactionsThisMonth = MarketplaceTransaction::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $revenueThisMonth      = MarketplaceTransaction::where('status', 'completed')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount');

        // Chart bulanan
        $monthlyBookings = Booking::select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('created_at', now()->year)->groupBy('month')->orderBy('month')
            ->get()->pluck('count', 'month')->toArray();

        $monthlyTransactions = MarketplaceTransaction::select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('created_at', now()->year)->groupBy('month')->orderBy('month')
            ->get()->pluck('count', 'month')->toArray();

        // Recent data
        $recentUsers        = User::with('roles')->orderBy('created_at', 'desc')->limit(8)->get();
        $recentBookings     = Booking::with(['user', 'bookable'])->orderBy('created_at', 'desc')->limit(5)->get();
        $recentTransactions = MarketplaceTransaction::with(['buyer', 'product'])->orderBy('created_at', 'desc')->limit(5)->get();

        // Pending approvals untuk tampil di dashboard
        $pendingApprovals = User::where(function ($q) {
                $q->where('seller_status', 'pending')->orWhere('provider_status', 'pending');
            })->with('roles')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalMahasiswa', 'totalSeller',
            'totalProviderResidence', 'totalProviderEvent',
            'totalResidences', 'activeResidences',
            'totalActivities', 'activeActivities',
            'totalProducts', 'activeProducts',
            'totalBookings', 'pendingBookings', 'completedBookings',
            'totalTransactions', 'completedTransactions', 'marketplaceRevenue',
            'bookingsThisMonth', 'transactionsThisMonth', 'revenueThisMonth',
            'recentUsers', 'recentBookings', 'recentTransactions',
            'monthlyBookings', 'monthlyTransactions',
            'pendingApprovals',
            'pendingSellerApproval', 'pendingProviderApproval'
        ));
    }

    public function analytics()
    {
        $userGrowth = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')->orderBy('date')->get();

        $bookingStatus = Booking::select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->get();

        $transactionStatus = MarketplaceTransaction::select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->get();

        $topSellers = User::where('is_seller', true)
            ->withCount('marketplaceProducts as product_count')
            ->withSum(['marketplaceTransactionsAsSeller as total_revenue' => fn($q) => $q->where('status', 'completed')], 'total_amount')
            ->orderByDesc('total_revenue')->limit(10)->get();

        $monthlyBookings = Booking::select(
                DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count')
            )->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get();

        $monthlyTransactions = MarketplaceTransaction::select(
                DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as revenue')
            )->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get();

        return view('admin.analytics', compact(
            'userGrowth', 'bookingStatus', 'transactionStatus',
            'topSellers', 'monthlyBookings', 'monthlyTransactions'
        ));
    }
}
