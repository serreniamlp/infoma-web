<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BookingService;
use App\Models\Activity;

class UpdateBookingStatusCommand extends Command
{
    protected $signature = 'bookings:update-status';
    protected $description = 'Update booking status: complete expired stays, auto-cancel unpaid bookings, deactivate expired activities';

    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        parent::__construct();
        $this->bookingService = $bookingService;
    }

    public function handle()
    {
        $this->info('Starting booking status update...');

        // 1. Auto-cancel booking hunian/event yang payment_deadline-nya sudah lewat
        $cancelledCount = $this->bookingService->cancelExpiredPayments();
        $this->info("Auto-cancelled {$cancelledCount} booking(s) karena melewati batas waktu pembayaran.");

        // 2. Auto-cancel marketplace transactions yang payment_deadline-nya sudah lewat
        $cancelledMpCount = $this->bookingService->cancelExpiredMarketplaceTransactions();
        $this->info("Auto-cancelled {$cancelledMpCount} marketplace transaction(s) karena melewati batas waktu pembayaran.");

        // 3. Update approved bookings yang check_out_date-nya sudah lewat jadi completed
        $completedBookings = $this->bookingService->updateExpiredBookings();
        $this->info("Updated {$completedBookings} booking(s) to completed status.");

        // 4. Kirim notifikasi pengingat perpanjang sewa H-7
        $reminderCount = $this->bookingService->sendRenewalReminders();
        $this->info("Sent {$reminderCount} renewal reminder(s) for leases expiring in 7 days.");

        // 4. Nonaktifkan event yang sudah lewat registration_deadline-nya
        $expiredActivities = Activity::where('is_active', true)
            ->where('registration_deadline', '<', now())
            ->update(['is_active' => false]);

        $this->info("Deactivated {$expiredActivities} expired activity/activities.");

        $this->info('Booking status update completed successfully.');
    }
}