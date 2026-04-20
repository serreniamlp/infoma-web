<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Residence;
use App\Models\Activity;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->middleware('auth:sanctum');
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $perPage = $request->get('per_page', 10);

        $query = $request->user()->bookings()
            ->with(['bookable', 'transaction'])
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc');

        $bookings = $query->paginate($perPage);

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
                            'notes' => $booking->notes,
                            'rejection_reason' => $booking->rejection_reason,
                            'created_at' => $booking->created_at,
                            'updated_at' => $booking->updated_at,
                            'bookable' => [
                                'id' => $booking->bookable->id,
                                'name' => $booking->bookable->name,
                                'type' => class_basename($booking->bookable_type),
                                'price' => $booking->bookable->price,
                                'images' => $booking->bookable->images,
                            ],
                            'transaction' => $booking->transaction ? [
                                'id' => $booking->transaction->id,
                                'payment_status' => $booking->transaction->payment_status,
                                'payment_method' => $booking->transaction->payment_method,
                                'final_amount' => $booking->transaction->final_amount,
                                'created_at' => $booking->transaction->created_at,
                            ] : null,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $bookings->currentPage(),
                        'last_page' => $bookings->lastPage(),
                        'per_page' => $bookings->perPage(),
                        'total' => $bookings->total(),
                        'from' => $bookings->firstItem(),
                        'to' => $bookings->lastItem(),
                    ]
                ]
            ]
        ], 200);
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this booking'
            ], 403);
        }

        $booking->load(['bookable', 'transaction']);

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

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated());
            $booking->load(['bookable', 'transaction']);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'status' => $booking->status,
                        'total_amount' => $booking->total_amount,
                        'check_in_date' => $booking->check_in_date ?? null,
                        'check_out_date' => $booking->check_out_date ?? null,
                        'notes' => $booking->notes,
                        'created_at' => $booking->created_at,
                        'bookable' => [
                            'id' => $booking->bookable->id,
                            'name' => $booking->bookable->name,
                            'type' => class_basename($booking->bookable_type),
                            'price' => $booking->bookable->price,
                        ],
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this booking'
            ], 403);
        }

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking cannot be updated'
            ], 400);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $booking->update($request->only(['notes']));

            return response()->json([
                'status' => 'success',
                'message' => 'Booking updated successfully',
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'status' => $booking->status,
                        'notes' => $booking->notes,
                        'updated_at' => $booking->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this booking'
            ], 403);
        }

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking cannot be cancelled'
            ], 400);
        }

        try {
            $this->bookingService->cancelBooking($booking);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'status' => $booking->fresh()->status,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function payment(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this booking'
            ], 403);
        }

        if ($booking->status !== 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking is not approved yet'
            ], 400);
        }

        if ($booking->transaction && $booking->transaction->payment_status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment already completed'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'total_amount' => $booking->total_amount,
                    'bookable' => [
                        'id' => $booking->bookable->id,
                        'name' => $booking->bookable->name,
                        'type' => class_basename($booking->bookable_type),
                    ],
                ],
                'payment_methods' => [
                    'bank_transfer' => 'Bank Transfer',
                    'credit_card' => 'Credit Card',
                    'e_wallet' => 'E-Wallet',
                ]
            ]
        ], 200);
    }

    public function processPayment(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this booking'
            ], 403);
        }

        $request->validate([
            'payment_method' => 'required|string|in:bank_transfer,credit_card,e_wallet',
            'payment_proof' => 'nullable|image|max:2048'
        ]);

        try {
            $this->bookingService->processPayment($booking, $request->all());
            $booking->load('transaction');

            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'status' => $booking->status,
                        'transaction' => $booking->transaction ? [
                            'id' => $booking->transaction->id,
                            'payment_status' => $booking->transaction->payment_status,
                            'payment_method' => $booking->transaction->payment_method,
                            'final_amount' => $booking->transaction->final_amount,
                        ] : null,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
