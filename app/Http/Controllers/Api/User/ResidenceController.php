<?php

namespace App\Http\Controllers\Api\User;

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

        return response()->json([
            'status' => 'success',
            'data' => [
                'residences' => [
                    'data' => $residences->getCollection()->map(function ($residence) {
                        return [
                            'id' => $residence->id,
                            'name' => $residence->name,
                            'description' => $residence->description,
                            'price' => $residence->price,
                            'address' => $residence->address,
                            'latitude' => $residence->latitude,
                            'longitude' => $residence->longitude,
                            'capacity' => $residence->capacity,
                            'available_slots' => $residence->available_slots,
                            'images' => $residence->images,
                            'facilities' => $residence->facilities,
                            'rating' => round($residence->ratings_avg_rating ?? 0, 1),
                            'category' => $residence->category ? [
                                'id' => $residence->category->id,
                                'name' => $residence->category->name,
                            ] : null,
                            'provider' => [
                                'id' => $residence->provider->id,
                                'name' => $residence->provider->name,
                            ],
                            'created_at' => $residence->created_at,
                            'updated_at' => $residence->updated_at,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $residences->currentPage(),
                        'last_page' => $residences->lastPage(),
                        'per_page' => $residences->perPage(),
                        'total' => $residences->total(),
                        'from' => $residences->firstItem(),
                        'to' => $residences->lastItem(),
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

    public function show(Residence $residence)
    {
        $residence->load(['provider', 'category', 'ratings.user']);

        // Check if user bookmarked this residence
        $isBookmarked = false;
        if (auth()->check()) {
            $isBookmarked = auth()->user()->bookmarks()
                ->where('bookmarkable_type', Residence::class)
                ->where('bookmarkable_id', $residence->id)
                ->exists();
        }

        // Get user's rating if exists
        $userRating = null;
        if (auth()->check()) {
            $userRating = $residence->ratings()
                ->where('user_id', auth()->id())
                ->first();
        }

        // Check if user can rate (approved booking + paid)
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

        return response()->json([
            'status' => 'success',
            'data' => [
                'residence' => [
                    'id' => $residence->id,
                    'name' => $residence->name,
                    'description' => $residence->description,
                    'price' => $residence->price,
                    'address' => $residence->address,
                    'latitude' => $residence->latitude,
                    'longitude' => $residence->longitude,
                    'capacity' => $residence->capacity,
                    'available_slots' => $residence->available_slots,
                    'images' => $residence->images,
                    'facilities' => $residence->facilities,
                    'is_active' => $residence->is_active,
                    'rating' => round($residence->ratings_avg_rating ?? 0, 1),
                    'ratings_count' => $residence->ratings->count(),
                    'category' => $residence->category ? [
                        'id' => $residence->category->id,
                        'name' => $residence->category->name,
                    ] : null,
                    'provider' => [
                        'id' => $residence->provider->id,
                        'name' => $residence->provider->name,
                        'email' => $residence->provider->email,
                        'phone' => $residence->provider->phone,
                    ],
                    'ratings' => $residence->ratings->map(function ($rating) {
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
                    'created_at' => $residence->created_at,
                    'updated_at' => $residence->updated_at,
                ]
            ]
        ], 200);
    }
}
