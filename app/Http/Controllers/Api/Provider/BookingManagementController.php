<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\Category;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookingManagementController extends Controller
{
    protected $bookingService;
    protected $notificationService;

    public function __construct(BookingService $bookingService, NotificationService $notificationService)
    {
        $this->middleware('auth:sanctum');
        $this->bookingService = $bookingService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $type = $request->get('type');
        $perPage = $request->get('per_page', 15);

        $query = Booking::whereHas('bookable', function ($q) use ($request) {
            $q->where('provider_id', $request->user()->id);
        })->with(['user', 'bookable', 'transaction']);

        // Status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Type filter (residence or activity)
        if ($type) {
            if ($type === 'residence') {
                $query->where('bookable_type', 'App\\Models\\Residence');
            } elseif ($type === 'activity') {
                $query->where('bookable_type', 'App\\Models\\Activity');
            }
        }

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bookable', function ($bookableQuery) use ($search) {
                      $bookableQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'bookings' => [
                    'data' => $bookings->getCollection()->map(function ($booking) {
                        return [
                            'id' => $booking->id,
                            'booking_code' => $booking->booking_code,
                            'status' => $booking->status,
                            'total_amount' => $booking->total_amount,
                            'check_in_date' => $booking->check_in_date ?? null,
                            'check_out_date' => $booking->check_out_date ?? null,
                            'created_at' => $booking->created_at,
                            'updated_at' => $booking->updated_at,
                            'user' => [
                                'id' => $booking->user->id,
                                'name' => $booking->user->name,
                                'email' => $booking->user->email,
                                'phone' => $booking->user->phone,
                            ],
                            'bookable' => [
                                'id' => $booking->bookable->id,
                                'name' => $booking->bookable->name,
                                'type' => class_basename($booking->bookable_type),
                                'price' => $booking->bookable->price,
                            ],
                            'transaction' => $booking->transaction ? [
                                'id' => $booking->transaction->id,
                                'payment_status' => $booking->transaction->payment_status,
                                'payment_method' => $booking->transaction->payment_method,
                                'final_amount' => $booking->transaction->final_amount,
                            ] : null,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $bookings->currentPage(),
                        'last_page' => $bookings->lastPage(),
                        'per_page' => $bookings->perPage(),
                        'total' => $bookings->total(),
                    ]
                ]
            ]
        ], 200);
    }

    public function show(Request $request, Booking $booking)
    {
        // Check if booking belongs to provider's item
        if ($booking->bookable->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this booking'
            ], 403);
        }

        $booking->load(['user', 'bookable', 'transaction']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'status' => $booking->status,
                    'total_amount' => $booking->total_amount,
                    'check_in_date' => $booking->check_in_date ?? null,
                    'check_out_date' => $booking->check_out_date ?? null,
                    'notes' => $booking->notes,
                    'rejection_reason' => $booking->rejection_reason,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                    'user' => [
                        'id' => $booking->user->id,
                        'name' => $booking->user->name,
                        'email' => $booking->user->email,
                        'phone' => $booking->user->phone,
                        'address' => $booking->user->address,
                    ],
                    'bookable' => [
                        'id' => $booking->bookable->id,
                        'name' => $booking->bookable->name,
                        'type' => class_basename($booking->bookable_type),
                        'price' => $booking->bookable->price,
                        'description' => $booking->bookable->description,
                        'images' => $booking->bookable->images,
                        'address' => $booking->bookable->address ?? $booking->bookable->location ?? null,
                    ],
                    'transaction' => $booking->transaction ? [
                        'id' => $booking->transaction->id,
                        'payment_status' => $booking->transaction->payment_status,
                        'payment_method' => $booking->transaction->payment_method,
                        'final_amount' => $booking->transaction->final_amount,
                        'payment_proof' => $booking->transaction->payment_proof,
                        'created_at' => $booking->transaction->created_at,
                    ] : null,
                ]
            ]
        ], 200);
    }

    public function approve(Request $request, Booking $booking)
    {
        // Check if booking belongs to provider's item
        if ($booking->bookable->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking cannot be approved'
            ], 400);
        }

        try {
            $this->bookingService->approveBooking($booking, $request->get('notes'));

            return response()->json([
                'status' => 'success',
                'message' => 'Booking approved successfully',
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'status' => $booking->fresh()->status,
                        'notes' => $booking->fresh()->notes,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        // Check if booking belongs to provider's item
        if ($booking->bookable->provider_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking cannot be rejected'
            ], 400);
        }

        try {
            $this->bookingService->rejectBooking($booking, $request->rejection_reason, $request->get('notes'));

            return response()->json([
                'status' => 'success',
                'message' => 'Booking rejected successfully',
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'status' => $booking->fresh()->status,
                        'rejection_reason' => $booking->fresh()->rejection_reason,
                        'notes' => $booking->fresh()->notes,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject booking: ' . $e->getMessage()
            ], 500);
        }
    }
}
