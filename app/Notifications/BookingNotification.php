<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class BookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $category;
    protected Booking $booking;

    public function __construct(string $title, string $message, string $category, Booking $booking)
    {
        $this->title = $title;
        $this->message = $message;
        $this->category = $category;
        $this->booking = $booking;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => $this->title,
            'message' => $this->message,
            'category' => $this->category,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'status' => $this->booking->status,
            'bookable_type' => $this->booking->bookable_type,
            'bookable_id' => $this->booking->bookable_id,
        ]);
    }
}





















