<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard with marketplace-style content
     */
    public function index()
    {
        // Get featured/latest residences (limit to 6)
        $residences = Residence::where('is_active', true)
            ->where('available_slots', '>', 0)
            ->with(['provider', 'category', 'ratings'])
            ->latest()
            ->limit(6)
            ->get();

        // Get upcoming activities (limit to 6)
        $activities = Activity::where('is_active', true)
            ->where('available_slots', '>', 0)
            ->where('event_date', '>', now())
            ->with(['provider', 'category', 'ratings'])
            ->orderBy('event_date')
            ->limit(6)
            ->get();

        // Get latest marketplace products (limit to 6)
        $products = MarketplaceProduct::active()
            ->available()
            ->with(['seller', 'category', 'ratings'])
            ->latest()
            ->limit(6)
            ->get();

        // Get user's recent marketplace transactions (limit to 3)
        $recentTransactions = MarketplaceTransaction::where('buyer_id', Auth::id())
            ->with(['product', 'seller'])
            ->latest()
            ->limit(3)
            ->get();

        // Get marketplace transaction statistics for the user
        $marketplaceStats = [
            'total_transactions' => MarketplaceTransaction::where('buyer_id', Auth::id())->count(),
            'pending_transactions' => MarketplaceTransaction::where('buyer_id', Auth::id())->where('status', 'pending')->count(),
            'completed_transactions' => MarketplaceTransaction::where('buyer_id', Auth::id())->where('status', 'completed')->count(),
            'total_spent' => MarketplaceTransaction::where('buyer_id', Auth::id())->where('status', 'completed')->sum('total_amount'),
        ];

        return view('user.dashboard', compact('residences', 'activities', 'products', 'recentTransactions', 'marketplaceStats'));
    }

    /**
     * Display user's history page (consolidated per category & status filter)
     */
    public function history(Request $request)
    {
        $userId   = auth()->id();
        $category = $request->get('category', 'residence');
        $status   = $request->get('status', 'all');

        // Counts for top category tabs
        $residenceCount = Booking::where('user_id', $userId)
            ->where('bookable_type', Residence::class)
            ->count();

        $activityCount = Booking::where('user_id', $userId)
            ->where('bookable_type', Activity::class)
            ->count();

        $marketplaceCount = MarketplaceTransaction::where('buyer_id', $userId)->count();

        // Data query according to category & status filter
        if ($category === 'activity') {
            $query = Booking::where('user_id', $userId)
                ->where('bookable_type', Activity::class)
                ->with(['bookable', 'transaction']);

            if ($status !== 'all') {
                if ($status === 'rejected_cancelled') {
                    $query->whereIn('status', ['rejected', 'cancelled']);
                } else {
                    $query->where('status', $status);
                }
            }

            $items = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        } elseif ($category === 'marketplace') {
            $query = MarketplaceTransaction::where('buyer_id', $userId)
                ->with(['product', 'seller', 'rating']);

            if ($status !== 'all') {
                if ($status === 'approved') {
                    $query->whereIn('status', ['processing', 'shipped']);
                } elseif ($status === 'rejected_cancelled') {
                    $query->where('status', 'cancelled');
                } else {
                    $query->where('status', $status);
                }
            }

            $items = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        } else {
            // Default: Residence
            $category = 'residence';
            $query = Booking::where('user_id', $userId)
                ->where('bookable_type', Residence::class)
                ->with(['bookable', 'transaction']);

            if ($status !== 'all') {
                if ($status === 'rejected_cancelled') {
                    $query->whereIn('status', ['rejected', 'cancelled']);
                } else {
                    $query->where('status', $status);
                }
            }

            $items = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        }

        return view('user.history', compact(
            'items', 'category', 'status', 
            'residenceCount', 'activityCount', 'marketplaceCount'
        ));
    }
}
