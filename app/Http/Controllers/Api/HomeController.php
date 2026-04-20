<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Get featured residences
        $featuredResidences = Residence::with(['provider', 'category'])
            ->where('is_active', true)
            ->where('available_slots', '>', 0)
            ->withAvg('ratings', 'rating')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($residence) {
                return [
                    'id' => $residence->id,
                    'name' => $residence->name,
                    'description' => $residence->description,
                    'price' => $residence->price,
                    'address' => $residence->address,
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
                    ]
                ];
            });

        // Get featured activities
        $featuredActivities = Activity::with(['provider', 'category'])
            ->where('is_active', true)
            ->where('available_slots', '>', 0)
            ->where('registration_deadline', '>', now())
            ->withAvg('ratings', 'rating')
            ->orderBy('event_date', 'asc')
            ->limit(6)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'description' => $activity->description,
                    'price' => $activity->price,
                    'location' => $activity->location,
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
                    ]
                ];
            });

        $categories = Category::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'featured_residences' => $featuredResidences,
                'featured_activities' => $featuredActivities,
                'categories' => $categories,
            ]
        ], 200);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, residence, activity
        $categoryId = $request->get('category_id');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $location = $request->get('location');
        $perPage = $request->get('per_page', 12);

        $residences = null;
        $activities = null;

        // Search residences
        if ($type === 'all' || $type === 'residence') {
            $residenceQuery = Residence::with(['provider', 'category'])
                ->where('is_active', true)
                ->where('available_slots', '>', 0)
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($subQuery) use ($query) {
                        $subQuery->where('name', 'LIKE', "%{$query}%")
                                ->orWhere('description', 'LIKE', "%{$query}%")
                                ->orWhere('address', 'LIKE', "%{$query}%");
                    });
                })
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                })
                ->when($minPrice, function ($q) use ($minPrice) {
                    $q->where('price', '>=', $minPrice);
                })
                ->when($maxPrice, function ($q) use ($maxPrice) {
                    $q->where('price', '<=', $maxPrice);
                })
                ->when($location, function ($q) use ($location) {
                    $q->where('address', 'LIKE', "%{$location}%");
                })
                ->withAvg('ratings', 'rating')
                ->orderBy('created_at', 'desc');

            $residences = $residenceQuery->paginate($perPage, ['*'], 'residences_page');
        }

        // Search activities
        if ($type === 'all' || $type === 'activity') {
            $activityQuery = Activity::with(['provider', 'category'])
                ->where('is_active', true)
                ->where('available_slots', '>', 0)
                ->where('registration_deadline', '>', now())
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($subQuery) use ($query) {
                        $subQuery->where('name', 'LIKE', "%{$query}%")
                                ->orWhere('description', 'LIKE', "%{$query}%")
                                ->orWhere('location', 'LIKE', "%{$query}%");
                    });
                })
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                })
                ->when($minPrice, function ($q) use ($minPrice) {
                    $q->where('price', '>=', $minPrice);
                })
                ->when($maxPrice, function ($q) use ($maxPrice) {
                    $q->where('price', '<=', $maxPrice);
                })
                ->when($location, function ($q) use ($location) {
                    $q->where('location', 'LIKE', "%{$location}%");
                })
                ->withAvg('ratings', 'rating')
                ->orderBy('event_date', 'asc');

            $activities = $activityQuery->paginate($perPage, ['*'], 'activities_page');
        }

        // Transform residences data
        $residencesData = $residences ? [
            'data' => $residences->getCollection()->map(function ($residence) {
                return [
                    'id' => $residence->id,
                    'name' => $residence->name,
                    'description' => $residence->description,
                    'price' => $residence->price,
                    'address' => $residence->address,
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
                    ]
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
        ] : null;

        // Transform activities data
        $activitiesData = $activities ? [
            'data' => $activities->getCollection()->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'description' => $activity->description,
                    'price' => $activity->price,
                    'location' => $activity->location,
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
                    ]
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
        ] : null;

        return response()->json([
            'status' => 'success',
            'data' => [
                'residences' => $residencesData,
                'activities' => $activitiesData,
                'search_params' => [
                    'query' => $query,
                    'type' => $type,
                    'category_id' => $categoryId,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'location' => $location,
                ]
            ]
        ], 200);
    }

    public function categories(Request $request)
    {
        $type = $request->get('type'); // residence, activity

        $query = Category::query();

        if ($type) {
            $query->where('type', $type);
        }

        $categories = $query->orderBy('name', 'asc')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
                'description' => $category->description ?? null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'categories' => $categories
            ]
        ], 200);
    }
}
