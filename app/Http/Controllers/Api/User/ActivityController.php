<?php

namespace App\Http\Controllers\Api\User;

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
            ->withAvg('ratings', 'rating');

        // Apply filters
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

        return response()->json([
            'status' => 'success',
            'data' => [
                'activities' => [
                    'data' => $activities->getCollection()->map(function ($activity) {
                        return [
                            'id' => $activity->id,
                            'name' => $activity->name,
                            'description' => $activity->description,
                            'price' => $activity->price,
                            'location' => $activity->location,
                            'latitude' => $activity->latitude,
                            'longitude' => $activity->longitude,
                            'capacity' => $activity->capacity,
                            'available_slots' => $activity->available_slots,
                            'event_date' => $activity->event_date,
                            'registration_deadline' => $activity->registration_deadline,
                            'images' => $activity->images,
                            'rating' => round($activity->ratings_avg_rating ?? 0, 1),
                            'category' => $activity->category ? [
                                'id' => $activity->category->id,
                                'name' => $activity->category->name,
                            ] : null,
                            'provider' => [
                                'id' => $activity->provider->id,
                                'name' => $activity->provider->name,
                            ],
                            'created_at' => $activity->created_at,
                            'updated_at' => $activity->updated_at,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $activities->currentPage(),
                        'last_page' => $activities->lastPage(),
                        'per_page' => $activities->perPage(),
                        'total' => $activities->total(),
                        'from' => $activities->firstItem(),
                        'to' => $activities->lastItem(),
                    ]
                ],
                'categories' => $categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'type' => $category->type,
                    ];
                }),
            ]
        ], 200);
    }

    public function show(Activity $activity)
    {
        $activity->load(['provider', 'category', 'ratings.user']);

        // Check if registration is still open
        $isRegistrationOpen = $activity->registration_deadline > now();

        // Check if user bookmarked this activity
        $isBookmarked = false;
        if (auth()->check()) {
            $isBookmarked = auth()->user()->bookmarks()
                ->where('bookmarkable_type', Activity::class)
                ->where('bookmarkable_id', $activity->id)
                ->exists();
        }

        // Get user's rating if exists
        $userRating = null;
        if (auth()->check()) {
            $userRating = $activity->ratings()
                ->where('user_id', auth()->id())
                ->first();
        }

        // Check if user can rate (approved booking + paid)
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

        return response()->json([
            'status' => 'success',
            'data' => [
                'activity' => [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'description' => $activity->description,
                    'price' => $activity->price,
                    'location' => $activity->location,
                    'latitude' => $activity->latitude,
                    'longitude' => $activity->longitude,
                    'capacity' => $activity->capacity,
                    'available_slots' => $activity->available_slots,
                    'event_date' => $activity->event_date,
                    'registration_deadline' => $activity->registration_deadline,
                    'is_active' => $activity->is_active,
                    'images' => $activity->images,
                    'rating' => round($activity->ratings_avg_rating ?? 0, 1),
                    'ratings_count' => $activity->ratings->count(),
                    'category' => $activity->category ? [
                        'id' => $activity->category->id,
                        'name' => $activity->category->name,
                    ] : null,
                    'provider' => [
                        'id' => $activity->provider->id,
                        'name' => $activity->provider->name,
                        'email' => $activity->provider->email,
                        'phone' => $activity->provider->phone,
                    ],
                    'ratings' => $activity->ratings->map(function ($rating) {
                        return [
                            'id' => $rating->id,
                            'rating' => $rating->rating,
                            'review' => $rating->review,
                            'created_at' => $rating->created_at,
                            'user' => [
                                'id' => $rating->user->id,
                                'name' => $rating->user->name,
                            ]
                        ];
                    }),
                    'is_bookmarked' => $isBookmarked,
                    'user_rating' => $userRating ? [
                        'id' => $userRating->id,
                        'rating' => $userRating->rating,
                        'review' => $userRating->review,
                        'created_at' => $userRating->created_at,
                    ] : null,
                    'can_rate' => $canRate,
                    'is_registration_open' => $isRegistrationOpen,
                    'created_at' => $activity->created_at,
                    'updated_at' => $activity->updated_at,
                ]
            ]
        ], 200);
    }
}
