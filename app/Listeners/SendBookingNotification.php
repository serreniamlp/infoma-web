<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;
use App\Services\NotificationService;

class SendBookingNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(BookingStatusChanged $event)
    {
        $this->notificationService->sendBookingNotification(
            $event->booking,
            $event->status
        );
    }
}


