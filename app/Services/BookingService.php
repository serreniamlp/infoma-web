<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Residence;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function createBooking(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Resolve bookable class & item
            $bookableClass = in_array($data['bookable_type'], ['residence', Residence::class])
                ? Residence::class
                : Activity::class;
            $bookable = $bookableClass::findOrFail($data['bookable_id']);

            // Check availability
            if ($bookable->available_slots <= 0) {
                throw new \Exception('Tidak ada slot tersedia');
            }

            // Handle document uploads (residence only)
            $documents = [];
            if (isset($data['documents']) && is_array($data['documents'])) {
                foreach ($data['documents'] as $uploadedFile) {
                    $path = $uploadedFile->store('documents', 'public');
                    $documents[] = [
                        'name' => $uploadedFile->getClientOriginalName(),
                        'type' => $uploadedFile->getClientMimeType(),
                        'path' => $path,
                    ];
                }
            }

            // ── Hitung durasi & total harga ──────────────────────────────
            $checkInDate = $data['check_in_date'];

            if ($bookable instanceof Residence) {
                // Ambil durasi yang dikirim dari form (sudah divalidasi)
                $durationMonths = max(1, (int) ($data['duration_months'] ?? 1));

                // Hitung check-out dari check-in + durasi
                $checkOutDate = \Carbon\Carbon::parse($checkInDate)
                    ->addMonths($durationMonths)
                    ->toDateString();

                // Harga per bulan setelah diskon
                $pricePerMonth = $bookable->getDiscountedPrice();

                // Total harga = harga per bulan × durasi
                $totalPrice = $pricePerMonth * $durationMonths;

            } else {
                // Activity: single day, harga flat
                $durationMonths = 0;
                $checkOutDate   = $data['check_out_date'] ?? $checkInDate;
                $totalPrice     = $bookable->getDiscountedPrice();
            }
            // ─────────────────────────────────────────────────────────────

            $booking = Booking::create([
                'user_id'           => auth()->id(),
                'bookable_type'     => $bookableClass,
                'bookable_id'       => $bookable->id,
                'booking_code'      => $this->generateBookingCode(),
                'check_in_date'     => $checkInDate,
                'check_out_date'    => $checkOutDate,
                'duration_months'   => $durationMonths,
                'total_price'       => $totalPrice,
                'documents'         => $documents,
                'status'            => 'pending',
                'notes'             => $data['notes'] ?? null,
                // Field pendaftaran event
                'participant_name'  => $data['participant_name'] ?? null,
                'participant_email' => $data['participant_email'] ?? null,
                'participant_phone' => $data['participant_phone'] ?? null,
            ]);

            // Notifikasi ke provider
            $this->notificationService->sendBookingNotification($booking, 'new_booking');

            return $booking;
        });
    }

    public function approveBooking(Booking $booking, $notes = null)
    {
        return DB::transaction(function () use ($booking, $notes) {
            if ($booking->bookable->available_slots <= 0) {
                throw new \Exception('Slot sudah tidak tersedia');
            }

            $booking->update([
                'status' => 'approved',
                'notes'  => $notes,
            ]);

            $booking->bookable->decrement('available_slots');

            $this->createTransaction($booking);

            $this->notificationService->sendBookingNotification($booking, 'booking_approved');

            return $booking;
        });
    }

    public function rejectBooking(Booking $booking, $reason, $notes = null)
    {
        $booking->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'notes'            => $notes,
        ]);

        $this->notificationService->sendBookingNotification($booking, 'booking_rejected');

        return $booking;
    }

    public function cancelBooking(Booking $booking)
    {
        return DB::transaction(function () use ($booking) {
            if ($booking->status === 'approved' && $booking->check_in_date <= now()->toDateString()) {
                throw new \Exception('Tidak dapat membatalkan booking yang sudah dimulai');
            }

            $oldStatus = $booking->status;

            $booking->update(['status' => 'cancelled']);

            if ($oldStatus === 'approved') {
                $booking->bookable->increment('available_slots');
            }

            $this->notificationService->sendBookingNotification($booking, 'booking_cancelled');

            return $booking;
        });
    }

    public function processPayment(Booking $booking, array $paymentData)
    {
        return DB::transaction(function () use ($booking, $paymentData) {
            $transaction = $booking->transaction;

            if (!$transaction) {
                throw new \Exception('Transaction tidak ditemukan');
            }

            $updateData = [
                'payment_method' => $paymentData['payment_method'],
                'payment_status' => 'paid',
            ];

            if (isset($paymentData['payment_proof'])) {
                $path = $paymentData['payment_proof']->store('payment_proofs', 'public');
                $updateData['payment_proof'] = $path;
            }

            $transaction->update($updateData);

            $this->notificationService->sendBookingNotification($booking, 'payment_received');

            return $transaction;
        });
    }

    /**
     * Buat transaction saat booking disetujui.
     * Menggunakan total_price yang sudah disimpan di booking
     * (sudah termasuk durasi × harga per bulan).
     */
    protected function createTransaction(Booking $booking)
    {
        $bookable = $booking->bookable;

        // Gunakan total_price dari booking jika sudah tersimpan (residence dengan durasi)
        if ($booking->total_price > 0) {
            $durationMonths  = max(1, $booking->duration_months ?: 1);
            $pricePerMonth   = $bookable->price;
            $originalAmount  = $pricePerMonth * $durationMonths;

            // Hitung diskon per bulan × durasi
            $discountAmount = 0;
            if ($bookable->discount_type && $bookable->discount_value) {
                if ($bookable->discount_type === 'percentage') {
                    $discountAmount = $originalAmount * ($bookable->discount_value / 100);
                } else {
                    $discountAmount = $bookable->discount_value * $durationMonths;
                }
            }

            $finalAmount = $booking->total_price;
        } else {
            // Fallback untuk activity atau booking lama
            $originalAmount = $bookable->price;
            $discountAmount = 0;
            if ($bookable->discount_type && $bookable->discount_value) {
                if ($bookable->discount_type === 'percentage') {
                    $discountAmount = $originalAmount * ($bookable->discount_value / 100);
                } else {
                    $discountAmount = $bookable->discount_value;
                }
            }
            $finalAmount = max(0, $originalAmount - $discountAmount);
        }

        return Transaction::create([
            'booking_id'       => $booking->id,
            'transaction_code' => $this->generateTransactionCode(),
            'original_amount'  => $originalAmount,
            'discount_amount'  => $discountAmount,
            'final_amount'     => $finalAmount,
            'payment_method'   => 'pending',
            'payment_status'   => 'pending',
        ]);
    }

    protected function generateBookingCode(): string
    {
        return 'BK-' . now()->format('Ymd') . '-' . Str::random(6);
    }

    protected function generateTransactionCode(): string
    {
        return 'TR-' . now()->format('Ymd') . '-' . Str::random(6);
    }

    public function updateExpiredBookings()
    {
        return Booking::where('status', 'approved')
            ->where('check_out_date', '<', now()->toDateString())
            ->update(['status' => 'completed']);
    }
}
