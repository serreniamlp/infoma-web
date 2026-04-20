<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Activity::where('provider_id', $request->user()->id)
            ->with(['category'])
            ->withCount(['bookings', 'ratings'])
            ->withAvg('ratings', 'rating');

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                      ->where('registration_deadline', '>', now());
            } elseif ($request->status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                      ->orWhere('registration_deadline', '<=', now());
                });
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $perPage = $request->get('per_page', 10);
        $activities = $query->orderBy('event_date', 'asc')->paginate($perPage);

        $categories = Category::where('type', 'activity')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ];
        });

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
                            'is_active' => $activity->is_active,
                            'images' => $activity->images,
                            'bookings_count' => $activity->bookings_count,
                            'ratings_count' => $activity->ratings_count,
                            'average_rating' => round($activity->ratings_avg_rating ?? 0, 1),
                            'category' => $activity->category ? [
                                'id' => $activity->category->id,
                                'name' => $activity->category->name,
                            ] : null,
                            'created_at' => $activity->created_at,
                            'updated_at' => $activity->updated_at,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $activities->currentPage(),
                        'last_page' => $activities->lastPage(),
                        'per_page' => $activities->perPage(),
                        'total' => $activities->total(),
                    ]
                ],
                'categories' => $categories,
            ]
        ], 200);
    }

    public function show(Request $request, Activity $activity)
    {
        if ($activity->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this activity'
            ], 403);
        }

        $activity->load(['category', 'bookings.user', 'ratings.user']);

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
                    'category' => $activity->category ? [
                        'id' => $activity->category->id,
                        'name' => $activity->category->name,
                    ] : null,
                    'bookings' => $activity->bookings->map(function ($booking) {
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
                            ]
                        ];
                    }),
                    'ratings' => $activity->ratings->map(function ($rating) {
                        return [
                            'id' => $rating->id,
                            'rating' => $rating->rating,
                            'comment' => $rating->comment,
                            'created_at' => $rating->created_at,
                            'user' => [
                                'id' => $rating->user->id,
                                'name' => $rating->user->name,
                            ]
                        ];
                    }),
                    'created_at' => $activity->created_at,
                    'updated_at' => $activity->updated_at,
                ]
            ]
        ], 200);
    }

    public function store(StoreActivityRequest $request)
    {
        try {
            $data = $request->validated();
            $data['provider_id'] = $request->user()->id;

            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('activities', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images;
            }

            // Set available_slots to capacity initially
            $data['available_slots'] = $data['capacity'];

            $activity = Activity::create($data);
            $activity->load('category');

            return response()->json([
                'status' => 'success',
                'message' => 'Activity created successfully',
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
                        'category' => $activity->category ? [
                            'id' => $activity->category->id,
                            'name' => $activity->category->name,
                        ] : null,
                        'created_at' => $activity->created_at,
                        'updated_at' => $activity->updated_at,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create activity: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        if ($activity->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this activity'
            ], 403);
        }

        try {
            $data = $request->validated();

            // Handle image uploads
            if ($request->hasFile('images')) {
                // Delete old images
                $oldImages = $activity->images ?? [];
                foreach ($oldImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }

                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('activities', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images;
            }

            $activity->update($data);
            $activity->load('category');

            return response()->json([
                'status' => 'success',
                'message' => 'Activity updated successfully',
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
                        'category' => $activity->category ? [
                            'id' => $activity->category->id,
                            'name' => $activity->category->name,
                        ] : null,
                        'created_at' => $activity->created_at,
                        'updated_at' => $activity->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update activity: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, Activity $activity)
    {
        if ($activity->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this activity'
            ], 403);
        }

        try {
            // Check if there are active bookings
            $activeBookings = $activity->bookings()
                ->whereIn('status', ['pending', 'approved'])
                ->count();

            if ($activeBookings > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete activity with active bookings'
                ], 400);
            }

            // Delete images
            $images = $activity->images ?? [];
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }

            $activity->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Activity deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete activity: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Request $request, Activity $activity)
    {
        if ($activity->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this activity'
            ], 403);
        }

        $activity->update([
            'is_active' => !$activity->is_active
        ]);

        $status = $activity->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => 'success',
            'message' => "Activity successfully {$status}",
            'data' => [
                'activity' => [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'is_active' => $activity->is_active,
                ]
            ]
        ], 200);
    }
}
