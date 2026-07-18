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

        // Cek apakah user bisa memberi rating:
        // booking harus completed dan transaksinya sudah paid
        $canRate = $booking->status === 'completed'
            && $booking->transaction
            && $booking->transaction->payment_status === 'paid';

        return view('user.bookings.show', compact('booking', 'canRate'));
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

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Booking tidak dapat dibatalkan');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
            'reason.max'      => 'Alasan pembatalan maksimal 500 karakter.',
        ]);

        $this->bookingService->cancelBooking($booking, $request->reason);

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

    public function processPayment(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'payment_proof.required' => 'File bukti transfer wajib diunggah.',
            'payment_proof.image'    => 'File harus berupa gambar.',
            'payment_proof.mimes'    => 'Format gambar harus berupa jpg, jpeg, png, atau webp.',
            'payment_proof.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $transaction = $booking->transaction;
        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }

        // Simpan bukti transfer
        if ($request->hasFile('payment_proof')) {
            if ($transaction->payment_proof) {
                Storage::disk('public')->delete($transaction->payment_proof);
            }
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            
            $transaction->update([
                'payment_method' => 'manual_transfer',
                'payment_proof'  => $path,
            ]);

            // Reset alasan penolakan bukti pembayaran lama
            $booking->update([
                'rejection_reason' => null,
            ]);

            // Kirim notifikasi ke provider
            $providerId = $booking->bookable->provider_id ?? null;
            if ($providerId) {
                $isResidence = ($booking->bookable_type === \App\Models\Residence::class);
                $providerUrl = $isResidence
                    ? url("/provider/residence/bookings/{$booking->id}")
                    : url("/provider/event/bookings/{$booking->id}");
                
                \App\Services\NotificationService::buktiTransferDiunggah(
                    $providerId,
                    auth()->user()->name,
                    $booking->booking_code,
                    $providerUrl
                );
            }
        }

        return redirect()->route('user.bookings.show', $booking)
            ->with('success', 'Bukti transfer berhasil diunggah! Menunggu verifikasi dari penyedia.');
    }
}



