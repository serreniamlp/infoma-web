<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookingManagementController extends Controller
{
    protected $bookingService;
    protected $notificationService;

    public function __construct(BookingService $bookingService, NotificationService $notificationService)
    {
        $this->bookingService = $bookingService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $type = $request->get('type');

        $query = Booking::whereHas('bookable', function ($q) {
            $q->where('provider_id', auth()->id());
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

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('provider.bookings.index', compact('bookings', 'status', 'search', 'type'));
    }

    public function show(Booking $booking)
    {
        // Check if booking belongs to provider's item
        if ($booking->bookable->provider_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['user', 'bookable', 'transaction']);

        return view('provider.bookings.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking)
    {
        // Check if booking belongs to provider's item
        if ($booking->bookable->provider_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Booking tidak dapat disetujui');
        }

        try {
            $this->bookingService->approveBooking($booking, $request->get('notes'));

            return redirect()->back()->with('success', 'Booking berhasil disetujui');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyetujui booking: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        // Check if booking belongs to provider's item
        if ($booking->bookable->provider_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Booking tidak dapat ditolak');
        }

        try {
            $this->bookingService->rejectBooking($booking, $request->rejection_reason, $request->get('notes'));

            return redirect()->back()->with('success', 'Booking berhasil ditolak');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menolak booking: ' . $e->getMessage());
        }
    }
}



