<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
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
        $query = $request->user()->bookings()
            ->with(['bookable', 'transaction'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate($request->get('per_page', 10));

        return BookingResource::collection($bookings);
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id != auth()->id()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $booking->load(['bookable', 'transaction']);

        return response()->json([
            'status' => 'success',
            'data'   => new BookingResource($booking),
        ]);
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request->validated());
            $booking->load(['bookable', 'transaction']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Booking berhasil dibuat.',
                'data'    => new BookingResource($booking),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat booking: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id != auth()->id()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Booking tidak dapat dibatalkan.',
            ], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->bookingService->cancelBooking($booking, $request->reason);

            return response()->json([
                'status'  => 'success',
                'message' => 'Booking berhasil dibatalkan.',
                'data'    => new BookingResource($booking->fresh()->load(['bookable', 'transaction'])),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membatalkan booking: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function renew(Request $request, Booking $booking)
    {
        if ($booking->user_id != auth()->id()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($booking->bookable_type !== \App\Models\Residence::class) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Perpanjang sewa hanya tersedia untuk hunian.',
            ], 422);
        }

        if ($booking->status !== 'approved') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya booking yang aktif yang bisa diperpanjang.',
            ], 422);
        }

        $request->validate([
            'duration_months' => 'required|integer|min:1',
        ]);

        try {
            $newBooking = $this->bookingService->renewBooking($booking, (int) $request->duration_months);
            $newBooking->load(['bookable', 'transaction']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Perpanjang sewa berhasil diajukan! Menunggu persetujuan penyedia.',
                'data'    => new BookingResource($newBooking),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengajukan perpanjangan: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function payment(Booking $booking)
    {
        if ($booking->user_id != auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        if ($booking->status !== 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Booking belum disetujui.'], 422);
        }

        if ($booking->transaction?->payment_status === 'paid') {
            return response()->json(['status' => 'error', 'message' => 'Pembayaran sudah selesai.'], 422);
        }

        $booking->load(['bookable', 'transaction']);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'booking'         => new BookingResource($booking),
                'payment_methods' => [
                    'bank_transfer' => 'Transfer Bank',
                    'e_wallet'      => 'E-Wallet',
                    'cash'          => 'Tunai',
                ],
            ],
        ]);
    }

    public function processPayment(Request $request, Booking $booking)
    {
        if ($booking->user_id != auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        if ($booking->status !== 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Booking belum disetujui.'], 422);
        }

        $request->validate([
            'payment_method' => 'required|string|in:bank_transfer,e_wallet,cash',
            'payment_proof'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $paymentData = ['payment_method' => $request->payment_method];

            if ($request->hasFile('payment_proof')) {
                $paymentData['payment_proof'] = $request->file('payment_proof');
            }

            $this->bookingService->processPayment($booking, $paymentData);
            $booking->load(['bookable', 'transaction']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran berhasil diproses.',
                'data'    => new BookingResource($booking),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 422);
        }
    }
}