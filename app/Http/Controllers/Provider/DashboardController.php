<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\Booking;
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

        $totalBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->count();

        $pendingBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', 'pending')->count();

        $approvedBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', 'approved')->count();

        $rejectedBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', 'rejected')->count();

        // Calculate total revenue
        $totalRevenue = $this->calculateTotalRevenue($providerId);

        // Monthly revenue and bookings
        $currentMonthRevenue = $this->getRevenueForMonth($providerId, Carbon::now());
        $monthlyBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Recent bookings for display
        $recentBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->with(['user', 'bookable'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent items (residences and activities)
        $recentResidences = Residence::where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $recentActivities = Activity::where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        $recentItems = $recentResidences->concat($recentActivities)
            ->sortByDesc('created_at')
            ->take(5);

        $stats = [
            'total_residences' => $totalResidences,
            'total_activities' => $totalActivities,
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookings,
            'approved_bookings' => $approvedBookings,
            'rejected_bookings' => $rejectedBookings,
            'total_revenue' => $totalRevenue,
            'monthly_bookings' => $monthlyBookings,
            'monthly_revenue' => $currentMonthRevenue,
            'approval_rate' => $totalBookings > 0 ? round((($approvedBookings) / $totalBookings) * 100, 1) : 0,
        ];

        return view('provider.dashboard', compact('stats', 'recentBookings', 'recentItems'));
    }

    public function getChartsData(Request $request)
    {
        $providerId = auth()->id();
        $type = $request->get('type');

        switch ($type) {
            case 'revenue':
                $revenueData = $this->getMonthlyRevenueData($providerId);
                return response()->json([
                    'status' => 'success',
                    'data' => $revenueData
                ]);

            case 'bookings':
                $bookingData = $this->getMonthlyBookingData($providerId);
                return response()->json([
                    'status' => 'success',
                    'data' => $bookingData
                ]);

            case 'status':
                $statusData = $this->getBookingStatusData($providerId);
                return response()->json([
                    'status' => 'success',
                    'data' => $statusData
                ]);

            default:
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'revenue' => $this->getMonthlyRevenueData($providerId),
                        'bookings' => $this->getMonthlyBookingData($providerId),
                        'status' => $this->getBookingStatusData($providerId)
                    ]
                ]);
        }
    }

    private function calculateTotalRevenue($providerId)
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

    private function getRevenueForMonth($providerId, $month)
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

    private function getMonthlyRevenueData($providerId)
    {
        $revenues = [];
        for ($i = 5; $i >= 0; $i--) {
            $targetMonth = Carbon::now()->subMonths($i);
            $revenue = $this->getRevenueForMonth($providerId, $targetMonth);
            $revenues[] = $revenue;
        }
        return $revenues;
    }

    private function getMonthlyBookingData($providerId)
    {
        $bookings = [];
        for ($i = 5; $i >= 0; $i--) {
            $targetMonth = Carbon::now()->subMonths($i);
            $count = Booking::whereHas('bookable', function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            })->whereMonth('created_at', $targetMonth->month)
                ->whereYear('created_at', $targetMonth->year)
                ->count();
            $bookings[] = $count;
        }
        return $bookings;
    }

    private function getBookingStatusData($providerId)
    {
        $approved = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', 'approved')->count();

        $pending = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', 'pending')->count();

        $rejected = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', 'rejected')->count();

        return [$approved, $pending, $rejected];
    }
}
