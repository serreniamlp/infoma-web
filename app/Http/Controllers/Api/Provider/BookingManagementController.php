<?php
// app/Http/Controllers/Api/Provider/BookingManagementController.php
// TULIS ULANG

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingManagementController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $query = Booking::whereHas('bookable', fn($q) =>
            $q->where('provider_id', Auth::id())
        )->with(['user', 'bookable', 'transaction']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return BookingResource::collection($bookings);
    }

    public function show(Booking $booking)
    {
        if ($booking->bookable->provider_id != Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $booking->load(['user', 'bookable', 'transaction']);

        return response()->json([
            'status' => 'success',
            'data'   => new BookingResource($booking),
        ]);
    }

    public function approve(Request $request, Booking $booking)
    {
        if ($booking->bookable->provider_id != Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Booking tidak dapat disetujui.'], 422);
        }

        try {
            $this->bookingService->approveBooking($booking, $request->notes);

            return response()->json([
                'status'  => 'success',
                'message' => 'Booking berhasil disetujui.',
                'data'    => new BookingResource($booking->fresh()->load(['user', 'bookable', 'transaction'])),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        if ($booking->bookable->provider_id != Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Booking tidak dapat ditolak.'], 422);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $this->bookingService->rejectBooking($booking, $request->rejection_reason, $request->notes);

            return response()->json([
                'status'  => 'success',
                'message' => 'Booking berhasil ditolak.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}