<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BookingService;
use App\Models\Activity;

class UpdateBookingStatusCommand extends Command
{
    protected $signature = 'bookings:update-status';
    protected $description = 'Update booking status for expired activities and completed periods';

    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        parent::__construct();
        $this->bookingService = $bookingService;
    }

    public function handle()
    {
        $this->info('Starting booking status update...');

        // Update expired bookings to completed
        $completedBookings = $this->bookingService->updateExpiredBookings();
        $this->info("Updated {$completedBookings} bookings to completed status");

        // Deactivate expired activities
        $expiredActivities = Activity::where('is_active', true)
            ->where('registration_deadline', '<', now())
            ->update(['is_active' => false]);

        $this->info("Deactivated {$expiredActivities} expired activities");

        $this->info('Booking status update completed successfully');
    }
}





















