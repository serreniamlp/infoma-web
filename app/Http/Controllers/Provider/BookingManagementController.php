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
        $status = $request->get('status', '');
        $search = $request->get('search');
        $type   = $request->get('type');

        $query = Booking::whereHas('bookable', function ($q) {
            $q->where('provider_id', auth()->id());
        })->with(['user', 'bookable', 'transaction']);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if ($type) {
            if ($type === 'residence') {
                $query->where('bookable_type', 'App\\Models\\Residence');
            } elseif ($type === 'activity') {
                $query->where('bookable_type', 'App\\Models\\Activity');
            }
        }

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

        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('created_at', '<=', $dateTo);

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        $viewName = auth()->user()->hasRole('provider_residence')
            ? 'provider_residence.bookings.index'
            : 'provider_event.bookings.index';

        return view($viewName, compact('bookings', 'status', 'search', 'type', 'dateFrom', 'dateTo'));
    }

    public function show(Booking $booking)
    {
        if ($booking->bookable->provider_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['user', 'bookable', 'transaction']);

        $viewName = auth()->user()->hasRole('provider_residence')
            ? 'provider_residence.bookings.show'
            : 'provider_event.bookings.show';

        return view($viewName, compact('booking'));
    }

    public function approve(Request $request, Booking $booking)
    {
        if ($booking->bookable->provider_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Booking tidak dapat disetujui');
        }

        try {
            $this->bookingService->approveBooking($booking, $request->get('notes'));

            // HAPUS baris NotificationService::bookingDisetujui(...) yang ada di sini

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

        if ($booking->bookable->provider_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Booking tidak dapat ditolak');
        }

        try {
            $this->bookingService->rejectBooking($booking, $request->rejection_reason, $request->get('notes'));

            // HAPUS baris NotificationService::bookingDitolak(...) yang ada di sini

            return redirect()->back()->with('success', 'Booking berhasil ditolak');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menolak booking: ' . $e->getMessage());
        }
    }

    /**
     * Memverifikasi pembayaran manual transfer bank dari mahasiswa.
     */
    public function verifyPayment(Booking $booking)
    {
        if ($booking->bookable->provider_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'approved' || !$booking->transaction || $booking->transaction->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Transaksi tidak dapat diverifikasi.');
        }

        try {
            $this->bookingService->confirmManualPayment($booking);
            return redirect()->back()->with('success', 'Pembayaran manual berhasil diverifikasi & disetujui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memverifikasi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Menolak bukti pembayaran manual transfer bank dari mahasiswa.
     */
    public function rejectPayment(Request $request, Booking $booking)
    {
        $request->validate([
            'payment_rejection_reason' => 'required|string|max:500',
        ], [
            'payment_rejection_reason.required' => 'Alasan penolakan pembayaran wajib diisi.',
            'payment_rejection_reason.max'      => 'Alasan penolakan pembayaran maksimal 500 karakter.',
        ]);

        if ($booking->bookable->provider_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'approved' || !$booking->transaction || $booking->transaction->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Transaksi tidak dapat ditolak.');
        }

        try {
            $this->bookingService->rejectManualPayment($booking, $request->payment_rejection_reason);
            return redirect()->back()->with('success', 'Bukti pembayaran berhasil ditolak dan mahasiswa telah dinotifikasi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menolak pembayaran: ' . $e->getMessage());
        }
    }
}