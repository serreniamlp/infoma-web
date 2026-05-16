<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\MarketplaceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $providerId = auth()->id();

        // Statistics
        $totalResidences = Residence::where('provider_id', $providerId)->count();
        $totalActivities = Activity::where('provider_id', $providerId)->count();

        $totalBookings = Booking::whereHas('bookable', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        })->count();

        $pendingBookings = Booking::whereHas('bookable', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        })->where('status', 'pending')->count();

        $approvedBookings = Booking::whereHas('bookable', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        })->where('status', 'approved')->count();

        $rejectedBookings = Booking::whereHas('bookable', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        })->where('status', 'rejected')->count();

        // Revenue — booking + marketplace
        $bookingRevenue     = $this->calculateBookingRevenue($providerId);
        $marketplaceRevenue = $this->calculateMarketplaceRevenue($providerId);
        $totalRevenue       = $bookingRevenue + $marketplaceRevenue;

        // Bulan ini
        $currentMonthBookingRevenue     = $this->getBookingRevenueForMonth($providerId, Carbon::now());
        $currentMonthMarketplaceRevenue = $this->getMarketplaceRevenueForMonth($providerId, Carbon::now());
        $currentMonthRevenue            = $currentMonthBookingRevenue + $currentMonthMarketplaceRevenue;

        $monthlyBookings = Booking::whereHas('bookable', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        })->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year)
          ->count();

        // Marketplace stats (hanya jika user adalah seller)
        $marketplaceStats = null;
        if (auth()->user()->isSeller()) {
            $marketplaceStats = [
                'total_orders'     => MarketplaceTransaction::where('seller_id', $providerId)->count(),
                'pending_orders'   => MarketplaceTransaction::where('seller_id', $providerId)->where('status', 'pending')->count(),
                'completed_orders' => MarketplaceTransaction::where('seller_id', $providerId)->where('status', 'completed')->count(),
                'total_revenue'    => $marketplaceRevenue,
                'month_revenue'    => $currentMonthMarketplaceRevenue,
            ];
        }

        // Recent bookings
        $recentBookings = Booking::whereHas('bookable', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        })->with(['user', 'bookable'])
          ->orderBy('created_at', 'desc')
          ->limit(5)
          ->get();

        // Recent items
        $recentResidences = Residence::where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')->limit(3)->get();
        $recentActivities = Activity::where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')->limit(2)->get();
        $recentItems = $recentResidences->concat($recentActivities)
            ->sortByDesc('created_at')->take(5);

        $stats = [
            'total_residences'   => $totalResidences,
            'total_activities'   => $totalActivities,
            'total_bookings'     => $totalBookings,
            'pending_bookings'   => $pendingBookings,
            'approved_bookings'  => $approvedBookings,
            'rejected_bookings'  => $rejectedBookings,
            'total_revenue'      => $totalRevenue,
            'booking_revenue'    => $bookingRevenue,
            'marketplace_revenue'=> $marketplaceRevenue,
            'monthly_bookings'   => $monthlyBookings,
            'monthly_revenue'    => $currentMonthRevenue,
            'approval_rate'      => $totalBookings > 0
                ? round(($approvedBookings / $totalBookings) * 100, 1)
                : 0,
        ];

        $viewName = auth()->user()->hasRole('provider_residence')
            ? 'provider_residence.dashboard'
            : 'provider_event.dashboard';

        return view($viewName, compact('stats', 'recentBookings', 'recentItems', 'marketplaceStats'));
    }

    public function getChartsData(Request $request)
    {
        $providerId = auth()->id();
        $type       = $request->get('type');

        switch ($type) {
            case 'revenue':
                return response()->json([
                    'status' => 'success',
                    'data'   => $this->getMonthlyRevenueData($providerId),
                ]);
            case 'bookings':
                return response()->json([
                    'status' => 'success',
                    'data'   => $this->getMonthlyBookingData($providerId),
                ]);
            case 'status':
                return response()->json([
                    'status' => 'success',
                    'data'   => $this->getBookingStatusData($providerId),
                ]);
            default:
                return response()->json([
                    'status' => 'success',
                    'data'   => [
                        'revenue'  => $this->getMonthlyRevenueData($providerId),
                        'bookings' => $this->getMonthlyBookingData($providerId),
                        'status'   => $this->getBookingStatusData($providerId),
                    ],
                ]);
        }
    }

    // -------------------------------------------------------
    // BOOKING REVENUE
    // -------------------------------------------------------

    private function calculateBookingRevenue($providerId)
    {
        $residenceRevenue = DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('residences', function ($join) use ($providerId) {
                $join->on('bookings.bookable_id', '=', 'residences.id')
                     ->where('bookings.bookable_type', 'like', '%Residence%')
                     ->where('residences.provider_id', $providerId);
            })
            ->where('transactions.payment_status', 'paid')
            ->sum('transactions.final_amount');

        $activityRevenue = DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('activities', function ($join) use ($providerId) {
                $join->on('bookings.bookable_id', '=', 'activities.id')
                     ->where('bookings.bookable_type', 'like', '%Activity%')
                     ->where('activities.provider_id', $providerId);
            })
            ->where('transactions.payment_status', 'paid')
            ->sum('transactions.final_amount');

        return $residenceRevenue + $activityRevenue;
    }

    private function getBookingRevenueForMonth($providerId, $month)
    {
        $residenceRevenue = DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('residences', function ($join) use ($providerId) {
                $join->on('bookings.bookable_id', '=', 'residences.id')
                     ->where('bookings.bookable_type', 'like', '%Residence%')
                     ->where('residences.provider_id', $providerId);
            })
            ->where('transactions.payment_status', 'paid')
            ->whereMonth('transactions.created_at', $month->month)
            ->whereYear('transactions.created_at', $month->year)
            ->sum('transactions.final_amount');

        $activityRevenue = DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('activities', function ($join) use ($providerId) {
                $join->on('bookings.bookable_id', '=', 'activities.id')
                     ->where('bookings.bookable_type', 'like', '%Activity%')
                     ->where('activities.provider_id', $providerId);
            })
            ->where('transactions.payment_status', 'paid')
            ->whereMonth('transactions.created_at', $month->month)
            ->whereYear('transactions.created_at', $month->year)
            ->sum('transactions.final_amount');

        return $residenceRevenue + $activityRevenue;
    }

    // -------------------------------------------------------
    // MARKETPLACE REVENUE — ini yang sebelumnya tidak ada
    // -------------------------------------------------------

    private function calculateMarketplaceRevenue($providerId)
    {
        return MarketplaceTransaction::where('seller_id', $providerId)
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    private function getMarketplaceRevenueForMonth($providerId, $month)
    {
        return MarketplaceTransaction::where('seller_id', $providerId)
            ->where('status', 'completed')
            ->whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year)
            ->sum('total_amount');
    }

    // -------------------------------------------------------
    // CHART DATA
    // -------------------------------------------------------

    private function getMonthlyRevenueData($providerId)
    {
        $revenues = [];
        for ($i = 5; $i >= 0; $i--) {
            $targetMonth = Carbon::now()->subMonths($i);
            $revenues[]  = $this->getBookingRevenueForMonth($providerId, $targetMonth)
                         + $this->getMarketplaceRevenueForMonth($providerId, $targetMonth);
        }
        return $revenues;
    }

    private function getMonthlyBookingData($providerId)
    {
        $bookings = [];
        for ($i = 5; $i >= 0; $i--) {
            $targetMonth = Carbon::now()->subMonths($i);
            $bookings[]  = Booking::whereHas('bookable', function ($q) use ($providerId) {
                $q->where('provider_id', $providerId);
            })->whereMonth('created_at', $targetMonth->month)
              ->whereYear('created_at', $targetMonth->year)
              ->count();
        }
        return $bookings;
    }

    private function getBookingStatusData($providerId)
    {
        $approved = Booking::whereHas('bookable', fn($q) => $q->where('provider_id', $providerId))
            ->where('status', 'approved')->count();
        $pending  = Booking::whereHas('bookable', fn($q) => $q->where('provider_id', $providerId))
            ->where('status', 'pending')->count();
        $rejected = Booking::whereHas('bookable', fn($q) => $q->where('provider_id', $providerId))
            ->where('status', 'rejected')->count();

        return [$approved, $pending, $rejected];
    }
    
    public function report(Request $request)
    {
        $providerId = auth()->id();
        $period     = $request->period ?? 'this_month';

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

        // --- Booking dalam periode ---
        $bookingsQuery = Booking::whereHas('bookable', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        })->whereBetween('created_at', [$dateFrom, $dateTo]);

        $totalBookings    = (clone $bookingsQuery)->count();
        $approvedBookings = (clone $bookingsQuery)->where('status', 'approved')->count();
        $rejectedBookings = (clone $bookingsQuery)->where('status', 'rejected')->count();
        $pendingBookings  = (clone $bookingsQuery)->where('status', 'pending')->count();
        $completedBookings= (clone $bookingsQuery)->where('status', 'completed')->count();

        // --- Booking revenue dalam periode ---
        $bookingRevenue = DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->whereIn('bookings.bookable_type', [
                'App\\Models\\Residence',
                'App\\Models\\Activity',
            ])
            ->whereExists(function ($q) use ($providerId) {
                $q->select(DB::raw(1))
                ->from('residences')
                ->whereColumn('residences.id', 'bookings.bookable_id')
                ->where('residences.provider_id', $providerId)
                ->where('bookings.bookable_type', 'like', '%Residence%')
                ->union(
                    DB::table('activities')
                        ->select(DB::raw(1))
                        ->whereColumn('activities.id', 'bookings.bookable_id')
                        ->where('activities.provider_id', $providerId)
                );
            })
            ->where('transactions.payment_status', 'paid')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->sum('transactions.final_amount');

        // Cara lebih simpel pakai 2 query terpisah
        $residenceRevenue = DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('residences', function ($join) use ($providerId) {
                $join->on('bookings.bookable_id', '=', 'residences.id')
                    ->where('bookings.bookable_type', 'like', '%Residence%')
                    ->where('residences.provider_id', $providerId);
            })
            ->where('transactions.payment_status', 'paid')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->sum('transactions.final_amount');

        $activityRevenue = DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('activities', function ($join) use ($providerId) {
                $join->on('bookings.bookable_id', '=', 'activities.id')
                    ->where('bookings.bookable_type', 'like', '%Activity%')
                    ->where('activities.provider_id', $providerId);
            })
            ->where('transactions.payment_status', 'paid')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->sum('transactions.final_amount');

        $bookingRevenue = $residenceRevenue + $activityRevenue;

        // --- Marketplace revenue dalam periode ---
        $marketplaceRevenue = 0;
        $marketplaceOrders  = 0;
        if (auth()->user()->isSeller()) {
            $marketplaceRevenue = MarketplaceTransaction::where('seller_id', $providerId)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('total_amount');

            $marketplaceOrders = MarketplaceTransaction::where('seller_id', $providerId)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count();
        }

        $totalRevenue = $bookingRevenue + $marketplaceRevenue;

        // --- Tabel booking detail ---
        $bookingDetails = Booking::whereHas('bookable', function ($q) use ($providerId) {
            $q->where('provider_id', $providerId);
        })->with(['user', 'bookable', 'transaction'])
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->orderBy('created_at', 'desc')
        ->paginate(15)
        ->withQueryString();

        // --- Revenue per item ---
        $isResidence = auth()->user()->hasRole('provider_residence');

        if ($isResidence) {
            $revenuePerItem = DB::table('residences')
                ->leftJoin('bookings', function ($join) {
                    $join->on('residences.id', '=', 'bookings.bookable_id')
                        ->where('bookings.bookable_type', 'like', '%Residence%');
                })
                ->leftJoin('transactions', function ($join) use ($dateFrom, $dateTo) {
                    $join->on('transactions.booking_id', '=', 'bookings.id')
                        ->where('transactions.payment_status', 'paid')
                        ->whereBetween('transactions.created_at', [$dateFrom, $dateTo]);
                })
                ->where('residences.provider_id', $providerId)
                ->groupBy('residences.id', 'residences.name')
                ->select(
                    'residences.id',
                    'residences.name',
                    DB::raw('COUNT(DISTINCT bookings.id) as booking_count'),
                    DB::raw('COALESCE(SUM(transactions.final_amount), 0) as revenue')
                )
                ->orderByDesc('revenue')
                ->get();
        } else {
            $revenuePerItem = DB::table('activities')
                ->leftJoin('bookings', function ($join) {
                    $join->on('activities.id', '=', 'bookings.bookable_id')
                        ->where('bookings.bookable_type', 'like', '%Activity%');
                })
                ->leftJoin('transactions', function ($join) use ($dateFrom, $dateTo) {
                    $join->on('transactions.booking_id', '=', 'bookings.id')
                        ->where('transactions.payment_status', 'paid')
                        ->whereBetween('transactions.created_at', [$dateFrom, $dateTo]);
                })
                ->where('activities.provider_id', $providerId)
                ->groupBy('activities.id', 'activities.name')
                ->select(
                    'activities.id',
                    'activities.name',
                    DB::raw('COUNT(DISTINCT bookings.id) as booking_count'),
                    DB::raw('COALESCE(SUM(transactions.final_amount), 0) as revenue')
                )
                ->orderByDesc('revenue')
                ->get();
        }

        $summary = [
            'total_revenue'      => $totalRevenue,
            'booking_revenue'    => $bookingRevenue,
            'marketplace_revenue'=> $marketplaceRevenue,
            'total_bookings'     => $totalBookings,
            'approved_bookings'  => $approvedBookings,
            'rejected_bookings'  => $rejectedBookings,
            'pending_bookings'   => $pendingBookings,
            'completed_bookings' => $completedBookings,
            'marketplace_orders' => $marketplaceOrders,
        ];

        $viewName = $isResidence
            ? 'provider_residence.report'
            : 'provider_event.report';

        return view($viewName, compact(
            'summary', 'bookingDetails', 'revenuePerItem',
            'period', 'dateFrom', 'dateTo'
        ));
    }
}