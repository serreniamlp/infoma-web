<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Residence;
use App\Models\Activity;
use App\Models\MarketplaceTransaction;
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
                $durationMonths = max(1, (int) ($data['duration_months'] ?? 1));
                $checkOutDate = \Carbon\Carbon::parse($checkInDate)
                    ->addMonths($durationMonths)
                    ->toDateString();
                $pricePerMonth = $bookable->getDiscountedPrice();
                $totalPrice = $pricePerMonth * $durationMonths;
            } else {
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
                'status'           => 'approved',
                'notes'            => $notes,
                // ← BARU: set deadline pembayaran 1 jam dari sekarang
                'payment_deadline' => now()->addHour(),
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

            $booking->update([
                'status'           => 'cancelled',
                'payment_deadline' => null,  // ← clear deadline saat dibatalkan manual
            ]);

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

            // ← BARU: clear payment_deadline setelah berhasil bayar
            $booking->update(['payment_deadline' => null]);

            $this->notificationService->sendBookingNotification($booking, 'payment_received');

            return $transaction;
        });
    }

    /**
     * Auto-cancel booking yang melewati payment_deadline.
     *
     * Dipanggil dari UpdateBookingStatusCommand (scheduled tiap menit).
     * Booking yang di-cancel:
     *   - status = 'approved'
     *   - payment_deadline sudah lewat
     *   - transaction payment_status masih 'pending' (belum bayar)
     *
     * @return int Jumlah booking yang di-cancel
     */
    public function cancelExpiredPayments(): int
    {
        $expiredBookings = Booking::where('status', 'approved')
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', now())
            ->whereHas('transaction', function ($q) {
                $q->where('payment_status', 'pending');
            })
            ->with(['user', 'bookable', 'transaction'])
            ->get();

        $count = 0;

        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking) {
                // Kembalikan slot
                $booking->bookable->increment('available_slots');

                // Update status booking
                $booking->update([
                    'status'           => 'cancelled',
                    'rejection_reason' => 'Dibatalkan otomatis karena pembayaran tidak dilakukan dalam 1 jam setelah booking disetujui.',
                    'payment_deadline' => null,
                ]);

                // Kirim notifikasi ke user
                $this->notificationService->sendBookingNotification($booking, 'payment_expired');
            });

            $count++;
        }

        return $count;
    }

    /**
     * Auto-cancel marketplace transactions yang payment_deadline-nya sudah lewat.
     *
     * Berbeda dengan booking — deadline dihitung dari created_at (bukan approved),
     * karena buyer harus langsung upload bukti bayar setelah buat pesanan.
     *
     * @return int Jumlah transaksi yang di-cancel
     */
    public function cancelExpiredMarketplaceTransactions(): int
    {
        $expired = MarketplaceTransaction::where('status', 'pending')
            ->where('payment_status', 'pending')
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', now())
            ->with(['buyer', 'seller', 'product'])
            ->get();

        $count = 0;

        foreach ($expired as $transaction) {
            DB::transaction(function () use ($transaction) {
                $transaction->update([
                    'status'              => 'cancelled',
                    'cancellation_reason' => 'Dibatalkan otomatis karena bukti pembayaran tidak diunggah dalam 1 jam setelah pesanan dibuat.',
                    'cancelled_at'        => now(),
                    'payment_deadline'    => null,
                ]);

                // Notifikasi ke buyer
                NotificationService::send(
                    $transaction->buyer_id,
                    'pesanan.kadaluarsa',
                    "Pesanan \"{$transaction->product->name}\" dibatalkan otomatis karena batas waktu pembayaran (1 jam) terlewat.",
                    route('user.marketplace.transactions.show', $transaction->id),
                    'fa-clock',
                    'red'
                );

                // Notifikasi ke seller
                NotificationService::send(
                    $transaction->seller_id,
                    'pesanan.kadaluarsa',
                    "Pesanan dari {$transaction->buyer->name} untuk \"{$transaction->product->name}\" dibatalkan otomatis karena pembeli tidak melakukan pembayaran.",
                    route('user.marketplace.seller.orders.show', $transaction->id),
                    'fa-clock',
                    'orange'
                );
            });

            $count++;
        }

        return $count;
    }

    /**
     * Update booking yang sudah selesai (check_out_date lewat) jadi completed.
     */
    public function updateExpiredBookings()
    {
        return Booking::where('status', 'approved')
            ->where('check_out_date', '<', now()->toDateString())
            ->update(['status' => 'completed']);
    }

    /**
     * Buat transaction saat booking disetujui.
     */
    protected function createTransaction(Booking $booking)
    {
        $bookable = $booking->bookable;

        if ($booking->total_price > 0) {
            $durationMonths  = max(1, $booking->duration_months ?: 1);
            $pricePerMonth   = $bookable->price;
            $originalAmount  = $pricePerMonth * $durationMonths;

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
}