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

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('location')) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }

        if ($request->filled('date_from')) {
            $query->where('event_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('event_date', '<=', $request->date_to);
        }

        if ($request->filled('available_only') && $request->available_only) {
            $query->where('available_slots', '>', 0);
        }

        // Sorting
        $sortBy = $request->get('sort', 'event_date');
        $sortOrder = $request->get('order', 'asc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'rating':
                $query->orderBy('ratings_avg_rating', $sortOrder);
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'deadline':
                $query->orderBy('registration_deadline', $sortOrder);
                break;
            default:
                $query->orderBy('event_date', $sortOrder);
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



