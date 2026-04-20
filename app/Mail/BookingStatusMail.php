<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class BookingStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $type;

    public function __construct(Booking $booking, string $type)
    {
        $this->booking = $booking;
        $this->type = $type;
    }

    public function build()
    {
        $subject = $this->getSubject();

        return $this->subject($subject)
                    ->view('emails.booking-status')
                    ->with([
                        'booking' => $this->booking,
                        'type' => $this->type
                    ]);
    }

    private function getSubject()
    {
        switch ($this->type) {
            case 'booking_approved':
                return 'Booking Anda Telah Disetujui - INFOMA';
            case 'booking_rejected':
                return 'Booking Anda Ditolak - INFOMA';
            case 'payment_received':
                return 'Pembayaran Berhasil - INFOMA';
            default:
                return 'Update Status Booking - INFOMA';
        }
    }
}


