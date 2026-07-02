<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['provider', 'category'])
            ->where('is_active', true)
            ->where('registration_deadline', '>', now())
            ->withAvg('ratings', 'rating')
            ->withCount('ratings');

        // Filter: kata kunci pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Filter: kategori — view mengirim 'category' (bukan 'category_id')
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter: harga
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter: lokasi
        if ($request->filled('location')) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }

        // Filter: rentang tanggal — view mengirim 'start_date' dan 'end_date'
        if ($request->filled('start_date')) {
            $query->whereDate('event_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('event_date', '<=', $request->end_date);
        }

        // Filter: hanya yang masih tersedia
        if ($request->filled('available_only') && $request->available_only) {
            $query->where('available_slots', '>', 0);
        }

        // Sorting — view mengirim nilai: date_asc, date_desc, price_low, price_high, rating
        $sort = $request->get('sort', 'date_asc');
        switch ($sort) {
            case 'date_desc':
                $query->orderBy('event_date', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('ratings_avg_rating', 'desc');
                break;
            default: // date_asc
                $query->orderBy('event_date', 'asc');
        }

        $activities = $query->paginate(12);
        $categories = Category::where('type', 'activity')->get();

        return view('user.activities.index', compact('activities', 'categories'));
    }

    public function show(Activity $activity)
    {
        $activity->load(['provider', 'category', 'ratings.user']);

        // Check if registration is still open
        $isRegistrationOpen = $activity->registration_deadline > now();

        // Check if user bookmarked this activity
        $isBookmarked = auth()->check() && auth()->user()->bookmarks()
            ->where('bookmarkable_type', Activity::class)
            ->where('bookmarkable_id', $activity->id)
            ->exists();

        // Get user's rating if exists
        $userRating = null;
        if (auth()->check()) {
            $userRating = $activity->ratings()
                ->where('user_id', auth()->id())
                ->first();
        }

        // Check if user can rate (has approved booking and paid transaction)
        $canRate = false;
        if (auth()->check()) {
            $canRate = auth()->user()->bookings()
                ->where('bookable_type', Activity::class)
                ->where('bookable_id', $activity->id)
                ->where('status', 'approved')
                ->whereHas('transaction', function ($q) {
                    $q->where('payment_status', 'paid');
                })
                ->exists();
        }

        return view('user.activities.show', compact('activity', 'isBookmarked', 'userRating', 'canRate', 'isRegistrationOpen'));
    }
}



