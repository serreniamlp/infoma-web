<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Residence;
use App\Models\Activity;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = auth()->user()->bookings()
            ->with(['bookable', 'transaction'])
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc');

        $bookings = $query->paginate(10);

        return view('user.bookings.index', compact('bookings', 'status'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['bookable', 'transaction']);

        return view('user.bookings.show', compact('booking'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type'); // 'residence' or 'activity'
        $id = $request->get('id');

        if ($type === 'residence') {
            $bookable = Residence::findOrFail($id);
        } else {
            $bookable = Activity::findOrFail($id);
        }

        // Check if available
        if ($bookable->available_slots <= 0) {
            return redirect()->back()->with('error', 'Tidak ada slot tersedia');
        }

        // Check if user already has active booking for this item
        $existingBooking = auth()->user()->bookings()
            ->where('bookable_type', get_class($bookable))
            ->where('bookable_id', $bookable->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingBooking) {
            return redirect()->back()->with('error', 'Anda sudah memiliki booking aktif untuk item ini');
        }

        return view('user.bookings.create', compact('bookable', 'type'));
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated());

            return redirect()->route('user.bookings.show', $booking)
                ->with('success', 'Booking berhasil dibuat. Menunggu persetujuan penyedia.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat booking: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Booking tidak dapat dibatalkan');
        }

        $this->bookingService->cancelBooking($booking);

        return redirect()->back()->with('success', 'Booking berhasil dibatalkan');
    }

    public function renewForm(Booking $booking)
    {
        $this->authorize('view', $booking);

        if ($booking->bookable_type !== \App\Models\Residence::class) {
            return redirect()->route('user.bookings.show', $booking)
                ->with('error', 'Perpanjang sewa hanya tersedia untuk hunian.');
        }

        if ($booking->status !== 'approved') {
            return redirect()->route('user.bookings.show', $booking)
                ->with('error', 'Hanya booking yang aktif yang bisa diperpanjang.');
        }

        $residence = $booking->bookable;
        return view('user.bookings.renew', compact('booking', 'residence'));
    }

    public function renew(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $request->validate([
            'duration_months' => 'required|integer|min:1',
        ]);

        try {
            $newBooking = $this->bookingService->renewBooking($booking, (int) $request->duration_months);

            return redirect()->route('user.bookings.show', $newBooking)
                ->with('success', 'Perpanjang sewa berhasil diajukan! Menunggu persetujuan penyedia.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengajukan perpanjangan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function payment(Booking $booking)
    {
        $this->authorize('view', $booking);

        if ($booking->status !== 'approved') {
            return redirect()->back()->with('error', 'Booking belum disetujui.');
        }

        if ($booking->transaction && $booking->transaction->payment_status === 'paid') {
            return redirect()->route('user.bookings.show', $booking)
                ->with('info', 'Pembayaran untuk booking ini sudah selesai.');
        }

        try {
            $snapToken = $this->bookingService->getOrCreateSnapToken($booking);
        } catch (\Exception $e) {
            return redirect()->route('user.bookings.show', $booking)
                ->with('error', 'Gagal menghubungi payment gateway: ' . $e->getMessage());
        }

        $snapUrl    = config('midtrans.snap_url');
        $clientKey  = config('midtrans.client_key');

        return view('user.bookings.payment', compact('booking', 'snapToken', 'snapUrl', 'clientKey'));
    }

    /**
     * processPayment() tidak lagi digunakan untuk flow Midtrans.
     * Konfirmasi pembayaran sekarang masuk via webhook (MidtransController::callback).
     *
     * Method ini dipertahankan agar route tidak error selama masa transisi.
     */
    public function processPayment(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        return redirect()->route('user.bookings.payment', $booking)
            ->with('info', 'Gunakan tombol Bayar di halaman pembayaran.');
    }
}



