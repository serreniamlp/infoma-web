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
            // Get bookable item
            $bookableClass = in_array($data['bookable_type'], ['residence', Residence::class]) ? Residence::class : Activity::class;
            $bookable = $bookableClass::findOrFail($data['bookable_id']);

            // Check availability
            if ($bookable->available_slots <= 0) {
                throw new \Exception('Tidak ada slot tersedia');
            }

            // Handle document uploads (store structured info)
            $documents = [];
            if (isset($data['documents'])) {
                foreach ($data['documents'] as $uploadedFile) {
                    $path = $uploadedFile->store('documents', 'public');
                    $documents[] = [
                        'name' => $uploadedFile->getClientOriginalName(),
                        'type' => $uploadedFile->getClientMimeType(),
                        'path' => $path,
                    ];
                }
            }

            // Calculate dates based on type
            $checkInDate = $data['check_in_date'];
            $checkOutDate = $data['check_out_date'] ?? $this->calculateCheckOutDate($bookable, $checkInDate);

            // Create booking
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'bookable_type' => $bookableClass,
                'bookable_id' => $bookable->id,
                'booking_code' => $this->generateBookingCode(),
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'documents' => $documents,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null
            ]);

            // Send notification to provider
            $this->notificationService->sendBookingNotification($booking, 'new_booking');

            return $booking;
        });
    }

    public function approveBooking(Booking $booking, $notes = null)
    {
        return DB::transaction(function () use ($booking, $notes) {
            // Check if still available
            if ($booking->bookable->available_slots <= 0) {
                throw new \Exception('Slot sudah tidak tersedia');
            }

            // Update booking status
            $booking->update([
                'status' => 'approved',
                'notes' => $notes
            ]);

            // Decrease available slots
            $booking->bookable->decrement('available_slots');

            // Create transaction
            $transaction = $this->createTransaction($booking);

            // Send notification to user
            $this->notificationService->sendBookingNotification($booking, 'booking_approved');

            return $booking;
        });
    }

    public function rejectBooking(Booking $booking, $reason, $notes = null)
    {
        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'notes' => $notes
        ]);

        // Send notification to user
        $this->notificationService->sendBookingNotification($booking, 'booking_rejected');

        return $booking;
    }

    public function cancelBooking(Booking $booking)
    {
        return DB::transaction(function () use ($booking) {
            // Only allow cancellation for pending bookings or approved bookings before check-in
            if ($booking->status === 'approved' && $booking->check_in_date <= now()->toDateString()) {
                throw new \Exception('Tidak dapat membatalkan booking yang sudah dimulai');
            }

            $oldStatus = $booking->status;

            $booking->update([
                'status' => 'cancelled'
            ]);

            // If booking was approved, increment available slots back
            if ($oldStatus === 'approved') {
                $booking->bookable->increment('available_slots');
            }

            // Send notification to provider
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
                'payment_status' => 'paid' // In real app, this would be 'pending' until verified
            ];

            // Handle payment proof upload
            if (isset($paymentData['payment_proof'])) {
                $path = $paymentData['payment_proof']->store('payment_proofs', 'public');
                $updateData['payment_proof'] = $path;
            }

            $transaction->update($updateData);

            // Send notification
            $this->notificationService->sendBookingNotification($booking, 'payment_received');

            return $transaction;
        });
    }

    protected function createTransaction(Booking $booking)
    {
        $bookable = $booking->bookable;
        $originalAmount = $bookable->price;

        // Calculate discount
        $discountAmount = 0;
        if ($bookable->discount_type && $bookable->discount_value) {
            if ($bookable->discount_type === 'percentage') {
                $discountAmount = $originalAmount * ($bookable->discount_value / 100);
            } else {
                $discountAmount = $bookable->discount_value;
            }
        }

        $finalAmount = $originalAmount - $discountAmount;

        return Transaction::create([
            'booking_id' => $booking->id,
            'transaction_code' => $this->generateTransactionCode(),
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'payment_method' => 'pending', // Will be updated when user makes payment
            'payment_status' => 'pending'
        ]);
    }

    protected function calculateCheckOutDate($bookable, $checkInDate)
    {
        if ($bookable instanceof Activity) {
            return $checkInDate; // Activities are single day
        }

        // For residences
        $checkIn = \Carbon\Carbon::parse($checkInDate);

        if ($bookable->rental_period === 'monthly') {
            return $checkIn->addMonth()->toDateString();
        } else {
            return $checkIn->addYear()->toDateString();
        }
    }

    protected function generateBookingCode()
    {
        return 'BK-' . now()->format('Ymd') . '-' . Str::random(6);
    }

    protected function generateTransactionCode()
    {
        return 'TR-' . now()->format('Ymd') . '-' . Str::random(6);
    }

    public function updateExpiredBookings()
    {
        // Mark completed bookings
        $completedBookings = Booking::where('status', 'approved')
            ->where('check_out_date', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        return $completedBookings;
    }
}

