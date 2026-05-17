<?php
// app/Http/Controllers/Api/Provider/DashboardController.php
// TULIS ULANG

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\MarketplaceTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $providerId  = Auth::id();
        $user        = Auth::user();
        $isResidence = $user->hasRole('provider_residence');

        $totalItems = $isResidence
            ? Residence::where('provider_id', $providerId)->count()
            : Activity::where('provider_id', $providerId)->count();

        $totalBookings     = Booking::whereHas('bookable', fn($q) => $q->where('provider_id', $providerId))->count();
        $pendingBookings   = Booking::whereHas('bookable', fn($q) => $q->where('provider_id', $providerId))->where('status', 'pending')->count();
        $approvedBookings  = Booking::whereHas('bookable', fn($q) => $q->where('provider_id', $providerId))->where('status', 'approved')->count();

        $monthlyBookings = Booking::whereHas('bookable', fn($q) => $q->where('provider_id', $providerId))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Revenue booking
        $bookingRevenue = $this->getBookingRevenue($providerId);

        // Revenue marketplace
        $marketplaceRevenue = $user->isSeller()
            ? MarketplaceTransaction::where('seller_id', $providerId)->where('status', 'completed')->sum('total_amount')
            : 0;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'role'                => $isResidence ? 'provider_residence' : 'provider_event',
                'provider_status'     => $user->provider_status,
                'total_items'         => $totalItems,
                'total_bookings'      => $totalBookings,
                'pending_bookings'    => $pendingBookings,
                'approved_bookings'   => $approvedBookings,
                'monthly_bookings'    => $monthlyBookings,
                'booking_revenue'     => $bookingRevenue,
                'marketplace_revenue' => $marketplaceRevenue,
                'total_revenue'       => $bookingRevenue + $marketplaceRevenue,
                'approval_rate'       => $totalBookings > 0
                    ? round($approvedBookings / $totalBookings * 100, 1)
                    : 0,
            ],
        ]);
    }

    private function getBookingRevenue($providerId)
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
}