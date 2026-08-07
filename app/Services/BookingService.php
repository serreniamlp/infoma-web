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

            // Reserve slot mahasiswa saat pending agar menghindari spam
            $bookable->decrement('available_slots');

            // Handle document uploads (residence only)
            $documents = [];
            if (isset($data['documents']) && is_array($data['documents'])) {
                foreach ($data['documents'] as $index => $uploadedFile) {
                    $path = $uploadedFile->store('documents', 'public');
                    $documents[] = [
                        'name' => $uploadedFile->getClientOriginalName(),
                        'type' => $uploadedFile->getClientMimeType(),
                        'path' => $path,
                        'doc_type' => $index === 0 ? 'ktp' : ($index === 1 ? 'kk' : 'lainnya'),
                    ];
                }
            }

            // ── Hitung durasi & total harga ──────────────────────────────
            $checkInDate = $data['check_in_date'];

            if ($bookable instanceof Residence) {
                $isYearly       = $bookable->rental_period === 'yearly';
                $durationMonths = max(1, (int) ($data['duration_months'] ?? ($isYearly ? 12 : 1)));

                $checkOutDate = \Carbon\Carbon::parse($checkInDate)
                    ->addMonths($durationMonths)
                    ->toDateString();

                // Untuk hunian tahunan: harga yang diinput adalah harga/tahun,
                // dan duration_months selalu kelipatan 12. Total = (harga/tahun) × (durasi/12).
                // Untuk hunian bulanan: total = harga/bulan × durasi_bulan.
                $discountedPrice = $bookable->getDiscountedPrice();
                if ($isYearly) {
                    $totalPrice = $discountedPrice * ($durationMonths / 12);
                } else {
                    $totalPrice = $discountedPrice * $durationMonths;
                }
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
            // Slot sudah dikurangi saat createBooking (status pending)
            // untuk menghindari spam, sehingga tidak perlu mengurangi slot lagi di sini.

            $booking->update([
                'status'           => 'approved',
                'notes'            => $notes,
                'payment_deadline' => now()->addHour(),
            ]);

            $this->createTransaction($booking);

            $this->notificationService->sendBookingNotification($booking, 'booking_approved');

            return $booking;
        });
    }

    public function rejectBooking(Booking $booking, $reason, $notes = null)
    {
        return DB::transaction(function () use ($booking, $reason, $notes) {
            $booking->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'notes'            => $notes,
            ]);

            // Kembalikan slot karena penyedia tidak approve
            if ($this->holdsSlotResponsibility($booking)) {
                $booking->bookable->increment('available_slots');
            }

            $this->notificationService->sendBookingNotification($booking, 'booking_rejected');

            return $booking;
        });
    }

    public function cancelBooking(Booking $booking, $reason = null)
    {
        return DB::transaction(function () use ($booking, $reason) {
            if ($booking->status === 'approved' && $booking->check_in_date <= now()->toDateString() && $booking->transaction?->payment_status === 'paid') {
                throw new \Exception('Tidak dapat membatalkan booking yang sudah dimulai dan dibayar');
            }

            $oldStatus = $booking->status;

            $booking->update([
                'status'           => 'cancelled',
                'rejection_reason' => $reason,
                'payment_deadline' => null,  // ← clear deadline saat dibatalkan manual
            ]);

            // Jika status sebelumnya pending atau approved, slot sudah diambil, maka kembalikan
            if (in_array($oldStatus, ['pending', 'approved']) && $this->holdsSlotResponsibility($booking)) {
                $booking->bookable->increment('available_slots');
            }

            $this->notificationService->sendBookingNotification($booking, 'booking_cancelled');

            return $booking;
        });
    }

    /**
     * Generate Snap token Midtrans untuk pembayaran booking.
     *
     * Token disimpan di tabel transactions agar tidak perlu request ulang
     * jika user refresh halaman (sebelum expired dalam 1 jam).
     *
     * @throws \Exception jika Midtrans gagal generate token
     */
    public function getOrCreateSnapToken(Booking $booking): string
    {
        $transaction = $booking->transaction;

        if (! $transaction) {
            throw new \Exception('Transaction tidak ditemukan.');
        }

        // Pakai token lama jika masih ada & belum bayar
        if ($transaction->snap_token && $transaction->payment_status === 'pending') {
            return $transaction->snap_token;
        }

        $midtrans = app(\App\Services\MidtransService::class);
        $token    = $midtrans->createSnapTokenForBooking($booking);

        $transaction->update(['snap_token' => $token]);

        return $token;
    }

    /**
     * processPayment() sekarang tidak digunakan untuk flow online.
     * Pembayaran dikonfirmasi via webhook Midtrans (MidtransController::callback).
     *
     * Method ini dipertahankan untuk kompatibilitas jika ada kode lain yang memanggilnya,
     * atau untuk skenario pembayaran manual oleh admin di masa depan.
     *
     * @deprecated Gunakan getOrCreateSnapToken() + webhook Midtrans
     */
    public function processPayment(Booking $booking, array $paymentData)
    {
        return DB::transaction(function () use ($booking, $paymentData) {
            $transaction = $booking->transaction;

            if (! $transaction) {
                throw new \Exception('Transaction tidak ditemukan');
            }

            $transaction->update([
                'payment_method' => $paymentData['payment_method'] ?? 'manual',
                'payment_status' => 'paid',
            ]);

            $booking->update(['payment_deadline' => null]);

            $this->notificationService->sendBookingNotification($booking, 'payment_received');

            return $transaction;
        });
    }

    /**
     * Konfirmasi pembayaran transfer manual oleh provider/penyedia.
     */
    public function confirmManualPayment(Booking $booking)
    {
        return DB::transaction(function () use ($booking) {
            $transaction = $booking->transaction;

            if (! $transaction) {
                throw new \Exception('Transaksi tidak ditemukan');
            }

            $transaction->update([
                'payment_status' => 'paid',
            ]);

            $booking->update(['payment_deadline' => null]);

            $this->notificationService->sendBookingNotification($booking, 'payment_received');

            return $transaction;
        });
    }

    /**
     * Menolak bukti pembayaran transfer manual.
     */
    public function rejectManualPayment(Booking $booking, $reason)
    {
        return DB::transaction(function () use ($booking, $reason) {
            $transaction = $booking->transaction;
            if (!$transaction) {
                throw new \Exception('Transaksi tidak ditemukan.');
            }

            // Hapus file bukti pembayaran lama dari storage
            if ($transaction->payment_proof) {
                Storage::disk('public')->delete($transaction->payment_proof);
            }

            // Reset status transaksi ke pending
            $transaction->update([
                'payment_proof' => null,
                'payment_status' => 'pending',
            ]);

            // Simpan alasan penolakan pembayaran di kolom rejection_reason booking
            $booking->update([
                'rejection_reason' => $reason,
            ]);

            // Kirim notifikasi ke mahasiswa
            $this->notificationService->send(
                $booking->user_id,
                'booking.pembayaran_ditolak',
                "Bukti transfer untuk booking #{$booking->booking_code} ditolak karena: {$reason}. Harap unggah ulang.",
                url("/user/bookings/{$booking->id}"),
                'fa-exclamation-triangle',
                'red'
            );

            return $transaction;
        });
    }

    /**
     * Perpanjang sewa — buat booking baru dengan check_in = check_out lama.
     *
     * @throws \Exception
     */
    public function renewBooking(Booking $booking, int $durationMonths): Booking
    {
        return DB::transaction(function () use ($booking, $durationMonths) {
            $residence = $booking->bookable;

            if (! ($residence instanceof \App\Models\Residence)) {
                throw new \Exception('Perpanjang sewa hanya tersedia untuk hunian.');
            }

            $isYearly = $residence->rental_period === 'yearly';
            if ($isYearly && $durationMonths % 12 !== 0) {
                throw new \Exception('Durasi untuk hunian tahunan harus kelipatan 12 bulan.');
            }

            $newCheckIn  = $booking->check_out_date->toDateString();
            
            // Pencegahan spam/ganda: Cek apakah sudah ada perpanjangan yang menyambung
            $existingRenewal = Booking::where('user_id', $booking->user_id)
                ->where('bookable_id', $residence->id)
                ->where('is_renewal', true)
                ->where('check_in_date', $newCheckIn)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($existingRenewal) {
                throw new \Exception('Anda sudah mengajukan perpanjangan untuk masa sewa ini.');
            }

            $newCheckOut = $booking->check_out_date->copy()->addMonths($durationMonths)->toDateString();

            $discountedPrice = $residence->getDiscountedPrice();
            $totalPrice      = $isYearly
                ? $discountedPrice * ($durationMonths / 12)
                : $discountedPrice * $durationMonths;

            $newBooking = Booking::create([
                'user_id'         => $booking->user_id,
                'bookable_type'   => \App\Models\Residence::class,
                'bookable_id'     => $residence->id,
                'booking_code'    => $this->generateBookingCode(),
                'check_in_date'   => $newCheckIn,
                'check_out_date'  => $newCheckOut,
                'duration_months' => $durationMonths,
                'total_price'     => $totalPrice,
                'documents'       => $booking->documents ?? [],
                'status'          => 'pending',
                'notes'           => 'Perpanjangan dari booking #' . $booking->booking_code,
                // Flag perpanjangan — saat approve, slot TIDAK dicek & TIDAK dikurangi
                'is_renewal'      => true,
            ]);

            $this->notificationService->sendBookingNotification($newBooking, 'new_booking');

            return $newBooking;
        });
    }

    /**
     * Kirim notifikasi pengingat perpanjang sewa H-7.
     *
     * Dipanggil dari scheduler tiap hari.
     * Hanya untuk booking hunian (Residence) yang status 'approved' dan belum dapat notif hari ini.
     *
     * @return int Jumlah notifikasi yang dikirim
     */
    public function sendRenewalReminders(): int
    {
        $targetDate = now()->addDays(7)->toDateString();

        $bookings = Booking::where('status', 'approved')
            ->where('bookable_type', \App\Models\Residence::class)
            ->whereDate('check_out_date', $targetDate)
            ->whereNull('renewal_reminder_sent_at')   // belum pernah dapat notif
            ->with(['user', 'bookable'])
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            $checkOutFormatted = \Carbon\Carbon::parse($booking->check_out_date)
                ->translatedFormat('d F Y');

            // Cek apakah sudah ada perpanjangan yang menyambung
            $hasRenewal = Booking::where('user_id', $booking->user_id)
                ->where('bookable_id', $booking->bookable_id)
                ->where('is_renewal', true)
                ->where('check_in_date', $booking->check_out_date->toDateString())
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if (! $hasRenewal) {
                NotificationService::perpanjangSewa(
                    $booking->user_id,
                    $booking->bookable->name ?? 'hunian',
                    $checkOutFormatted,
                    '/user/bookings/' . $booking->id
                );
            }

            NotificationService::ratingReminder(
                $booking->user_id,
                $booking->bookable->name ?? 'hunian',
                '/residences/' . $booking->bookable_id
            );

            // Tandai sudah dikirim agar tidak spam
            $booking->update(['renewal_reminder_sent_at' => now()]);

            $count++;
        }

        return $count;
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
                // Kembalikan slot jika bertanggung jawab atas slot tersebut
                if ($this->holdsSlotResponsibility($booking)) {
                    $booking->bookable->increment('available_slots');
                }

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
        $expiredBookings = Booking::where('status', 'approved')
            ->where('check_out_date', '<', now()->toDateString())
            ->get();

        $count = 0;
        foreach ($expiredBookings as $booking) {
            DB::transaction(function () use ($booking) {
                // Cek apakah ada perpanjangan sewa yang aktif (menyambung)
                $hasRenewal = Booking::where('user_id', $booking->user_id)
                    ->where('bookable_id', $booking->bookable_id)
                    ->where('is_renewal', true)
                    ->where('check_in_date', $booking->check_out_date->toDateString())
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                // Jika tidak ada perpanjangan, berarti mahasiswa benar-benar keluar kamar -> kembalikan slot
                if (! $hasRenewal && $booking->bookable) {
                    $booking->bookable->increment('available_slots');
                }

                $booking->update(['status' => 'completed']);
            });
            $count++;
        }

        return $count;
    }

    /**
     * Buat transaction saat booking disetujui.
     */
    protected function createTransaction(Booking $booking)
    {
        $bookable = $booking->bookable;

        $isYearly       = ($bookable instanceof Residence) && $bookable->rental_period === 'yearly';
        $durationMonths = max(1, $booking->duration_months ?: 1);

        // Harga dasar: untuk tahunan = harga/tahun × (durasi/12), untuk bulanan = harga/bulan × durasi
        if ($isYearly) {
            $originalAmount = $bookable->price * ($durationMonths / 12);
        } else {
            $originalAmount = $bookable->price * $durationMonths;
        }

        $discountAmount = 0;
        if ($bookable->discount_type && $bookable->discount_value) {
            if ($bookable->discount_type === 'percentage') {
                $discountAmount = $originalAmount * ($bookable->discount_value / 100);
            } else {
                // Nominal discount: per-tahun untuk yearly, per-bulan untuk monthly
                $discountAmount = $isYearly
                    ? $bookable->discount_value * ($durationMonths / 12)
                    : $bookable->discount_value * $durationMonths;
            }
        }

        $finalAmount = max(0, $originalAmount - $discountAmount);

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

    /**
     * Mengecek apakah booking ini memegang tanggung jawab atas slot yang dikurangi.
     */
    private function holdsSlotResponsibility(Booking $booking): bool
    {
        if (! $booking->is_renewal) {
            return true; // Booking awal selalu menahan slot
        }

        // Jika ini perpanjangan, ia menahan slot HANYA JIKA pesanan sebelumnya
        // sudah 'completed' (tanggung jawab mengembalikan slot dipindahkan ke sini).
        return Booking::where('user_id', $booking->user_id)
            ->where('bookable_id', $booking->bookable_id)
            ->where('check_out_date', $booking->check_in_date)
            ->where('status', 'completed')
            ->exists();
    }
}