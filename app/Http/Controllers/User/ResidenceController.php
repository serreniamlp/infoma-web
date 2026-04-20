<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Residence;
use App\Models\Category;
use Illuminate\Http\Request;

class ResidenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Residence::with(['provider', 'category'])
            ->where('is_active', true)
            ->withAvg('ratings', 'rating')
            ->withCount('ratings');

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('rental_period')) {
            $query->where('rental_period', $request->rental_period);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('location')) {
            $query->where('address', 'LIKE', '%' . $request->location . '%');
        }

        if ($request->filled('available_only') && $request->available_only) {
            $query->where('available_slots', '>', 0);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

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
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $residences = $query->paginate(12);
        $categories = Category::where('type', 'residence')->get();

        return view('user.residences.index', compact('residences', 'categories'));
    }

    public function show(Residence $residence)
    {
        $residence->load(['provider', 'category', 'ratings.user']);

        // Check if user bookmarked this residence
        $isBookmarked = auth()->check() && auth()->user()->bookmarks()
            ->where('bookmarkable_type', Residence::class)
            ->where('bookmarkable_id', $residence->id)
            ->exists();

        // Get user's rating if exists
        $userRating = null;
        if (auth()->check()) {
            $userRating = $residence->ratings()
                ->where('user_id', auth()->id())
                ->first();
        }

        // Check if user can rate (has approved booking and paid transaction)
        $canRate = false;
        if (auth()->check()) {
            $canRate = auth()->user()->bookings()
                ->where('bookable_type', Residence::class)
                ->where('bookable_id', $residence->id)
                ->where('status', 'approved')
                ->whereHas('transaction', function ($q) {
                    $q->where('payment_status', 'paid');
                })
                ->exists();
        }

        return view('user.residences.show', compact('residence', 'isBookmarked', 'userRating', 'canRate'));
    }
}



