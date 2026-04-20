<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $providerId = $request->user()->id;

        // Statistics
        $totalResidences = Residence::where('provider_id', $providerId)->count();
        $totalActivities = Activity::where('provider_id', $providerId)->count();

        $totalBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->count();

        $pendingBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', 'pending')->count();

        // Calculate total revenue
        $totalRevenue = DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('residences', function ($join) use ($providerId) {
                $join->on('bookings.bookable_id', '=', 'residences.id')
                     ->where('bookings.bookable_type', 'like', '%Residence%')
                     ->where('residences.provider_id', $providerId);
            })
            ->where('transactions.payment_status', 'paid')
            ->sum('transactions.final_amount');

        $totalRevenue += DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->join('activities', function ($join) use ($providerId) {
                $join->on('bookings.bookable_id', '=', 'activities.id')
                     ->where('bookings.bookable_type', 'like', '%Activity%')
                     ->where('activities.provider_id', $providerId);
            })
            ->where('transactions.payment_status', 'paid')
            ->sum('transactions.final_amount');

        // Recent bookings
        $recentBookings = Booking::whereHas('bookable', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->with(['user', 'bookable'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'status' => $booking->status,
                    'total_amount' => $booking->total_amount,
                    'created_at' => $booking->created_at,
                    'user' => [
                        'id' => $booking->user->id,
                        'name' => $booking->user->name,
                        'email' => $booking->user->email,
                    ],
                    'bookable' => [
                        'id' => $booking->bookable->id,
                        'name' => $booking->bookable->name,
                        'type' => class_basename($booking->bookable_type),
                    ]
                ];
            });

        // Popular items
        $popularResidences = Residence::where('provider_id', $providerId)
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($residence) {
                return [
                    'id' => $residence->id,
                    'name' => $residence->name,
                    'type' => 'Residence',
                    'bookings_count' => $residence->bookings_count,
                    'created_at' => $residence->created_at,
                ];
            });

        $popularActivities = Activity::where('provider_id', $providerId)
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'type' => 'Activity',
                    'bookings_count' => $activity->bookings_count,
                    'created_at' => $activity->created_at,
                ];
            });

        $recentItems = $popularResidences->concat($popularActivities)->sortByDesc('created_at')->take(5)->values();

        $stats = [
            'total_residences' => $totalResidences,
            'total_activities' => $totalActivities,
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookings,
            'total_revenue' => $totalRevenue,
            'monthly_bookings' => $totalBookings,
            'monthly_revenue' => $totalRevenue,
            'approval_rate' => $totalBookings > 0 ? round((($totalBookings - $pendingBookings) / $totalBookings) * 100, 1) : 0,
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'recent_bookings' => $recentBookings,
                'recent_items' => $recentItems,
            ]
        ], 200);
    }
}
