<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResidenceRequest;
use App\Http\Requests\UpdateResidenceRequest;
use App\Models\Residence;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResidenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Residence::where('provider_id', $request->user()->id)
            ->with(['category'])
            ->withCount(['bookings', 'ratings'])
            ->withAvg('ratings', 'rating');

        // Filters
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $perPage = $request->get('per_page', 10);
        $residences = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $categories = Category::where('type', 'residence')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ];
        });

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
                    'latitude' => $residence->latitude,
                    'longitude' => $residence->longitude,
                            'latitude' => $residence->latitude,
                            'longitude' => $residence->longitude,
                            'capacity' => $residence->capacity,
                            'available_slots' => $residence->available_slots,
                            'is_active' => $residence->is_active,
                            'images' => $residence->images,
                            'facilities' => $residence->facilities,
                            'bookings_count' => $residence->bookings_count,
                            'ratings_count' => $residence->ratings_count,
                            'average_rating' => round($residence->ratings_avg_rating ?? 0, 1),
                            'category' => $residence->category ? [
                                'id' => $residence->category->id,
                                'name' => $residence->category->name,
                            ] : null,
                            'created_at' => $residence->created_at,
                            'updated_at' => $residence->updated_at,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $residences->currentPage(),
                        'last_page' => $residences->lastPage(),
                        'per_page' => $residences->perPage(),
                        'total' => $residences->total(),
                    ]
                ],
                'categories' => $categories,
            ]
        ], 200);
    }

    public function show(Request $request, Residence $residence)
    {
        if ($residence->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this residence'
            ], 403);
        }

        $residence->load(['category', 'bookings.user', 'ratings.user']);

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
                    'is_active' => $residence->is_active,
                    'images' => $residence->images,
                    'facilities' => $residence->facilities,
                    'category' => $residence->category ? [
                        'id' => $residence->category->id,
                        'name' => $residence->category->name,
                    ] : null,
                    'bookings' => $residence->bookings->map(function ($booking) {
                        return [
                            'id' => $booking->id,
                            'booking_code' => $booking->booking_code,
                            'status' => $booking->status,
                            'total_amount' => $booking->total_amount,
                            'check_in_date' => $booking->check_in_date ?? null,
                            'check_out_date' => $booking->check_out_date ?? null,
                            'created_at' => $booking->created_at,
                            'user' => [
                                'id' => $booking->user->id,
                                'name' => $booking->user->name,
                                'email' => $booking->user->email,
                            ]
                        ];
                    }),
                    'ratings' => $residence->ratings->map(function ($rating) {
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
                    'created_at' => $residence->created_at,
                    'updated_at' => $residence->updated_at,
                ]
            ]
        ], 200);
    }

    public function create(Request $request)
    {
        $categories = Category::where('type', 'residence')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'categories' => $categories,
            ]
        ], 200);
    }

    public function store(StoreResidenceRequest $request)
    {
        try {
            $data = $request->validated();
            $data['provider_id'] = $request->user()->id;

            // Ensure price is set from price_per_month (validated key)
            $data['price'] = $request->input('price', $request->input('price_per_month'));

            // Handle image uploads
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('residences', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images;
            }

            // Handle facilities
            if (isset($data['facilities'])) {
                $data['facilities'] = array_values($data['facilities']);
            }

            // Set available_slots to capacity initially
            $data['available_slots'] = $data['capacity'];

            $residence = Residence::create($data);
            $residence->load('category');

            return response()->json([
                'status' => 'success',
                'message' => 'Residence created successfully',
                'data' => [
                    'residence' => [
                        'id' => $residence->id,
                        'name' => $residence->name,
                        'description' => $residence->description,
                        'price' => $residence->price,
                        'address' => $residence->address,
                        'latitude' => $residence->latitude,
                        'longitude' => $residence->longitude,
                    'latitude' => $residence->latitude,
                    'longitude' => $residence->longitude,
                        'capacity' => $residence->capacity,
                        'available_slots' => $residence->available_slots,
                        'is_active' => $residence->is_active,
                        'images' => $residence->images,
                        'facilities' => $residence->facilities,
                        'category' => $residence->category ? [
                            'id' => $residence->category->id,
                            'name' => $residence->category->name,
                        ] : null,
                        'created_at' => $residence->created_at,
                        'updated_at' => $residence->updated_at,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create residence: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, Residence $residence)
    {
        if ($residence->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this residence'
            ], 403);
        }

        $categories = Category::where('type', 'residence')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ];
        });

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
                    'is_active' => $residence->is_active,
                    'images' => $residence->images,
                    'facilities' => $residence->facilities,
                    'category_id' => $residence->category_id,
                    'created_at' => $residence->created_at,
                    'updated_at' => $residence->updated_at,
                ],
                'categories' => $categories,
            ]
        ], 200);
    }

    public function update(UpdateResidenceRequest $request, Residence $residence)
    {
        if ($residence->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this residence'
            ], 403);
        }

        try {
            $data = $request->validated();

            // Ensure price is set from price_per_month (validated key)
            $data['price'] = $request->input('price', $request->input('price_per_month'));

            // Handle image uploads
            if ($request->hasFile('images')) {
                // Delete old images
                $oldImages = $residence->images ?? [];
                foreach ($oldImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }

                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('residences', 'public');
                    $images[] = $path;
                }
                $data['images'] = $images;
            }

            // Handle facilities
            if (isset($data['facilities'])) {
                $data['facilities'] = array_values($data['facilities']);
            }

            $residence->update($data);
            $residence->load('category');

            return response()->json([
                'status' => 'success',
                'message' => 'Residence updated successfully',
                'data' => [
                    'residence' => [
                        'id' => $residence->id,
                        'name' => $residence->name,
                        'description' => $residence->description,
                        'price' => $residence->price,
                        'address' => $residence->address,
                        'latitude' => $residence->latitude,
                        'longitude' => $residence->longitude,
                    'latitude' => $residence->latitude,
                    'longitude' => $residence->longitude,
                        'capacity' => $residence->capacity,
                        'available_slots' => $residence->available_slots,
                        'is_active' => $residence->is_active,
                        'images' => $residence->images,
                        'facilities' => $residence->facilities,
                        'category' => $residence->category ? [
                            'id' => $residence->category->id,
                            'name' => $residence->category->name,
                        ] : null,
                        'created_at' => $residence->created_at,
                        'updated_at' => $residence->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update residence: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, Residence $residence)
    {
        if ($residence->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this residence'
            ], 403);
        }

        try {
            // Check if there are active bookings
            $activeBookings = $residence->bookings()
                ->whereIn('status', ['pending', 'approved'])
                ->count();

            if ($activeBookings > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete residence with active bookings'
                ], 400);
            }

            // Delete images
            $images = $residence->images ?? [];
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }

            $residence->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Residence deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete residence: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Request $request, Residence $residence)
    {
        if ($residence->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this residence'
            ], 403);
        }

        $residence->update([
            'is_active' => !$residence->is_active
        ]);

        $status = $residence->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'status' => 'success',
            'message' => "Residence successfully {$status}",
            'data' => [
                'residence' => [
                    'id' => $residence->id,
                    'name' => $residence->name,
                    'is_active' => $residence->is_active,
                ]
            ]
        ], 200);
    }
}
