<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Str;
use App\Models\Booking;

class NotificationService
{
    /**
     * Kirim notifikasi ke satu user
     */
    public static function send(int $userId, string $type, string $message, string $url, string $icon = 'fa-bell', string $color = 'blue'): void
    {
        Notification::create([
            'id'              => Str::uuid(),
            'type'            => $type,
            'notifiable_type' => 'App\Models\User',
            'notifiable_id'   => $userId,
            'data'            => [
                'message' => $message,
                'url'     => $url,
                'icon'    => $icon,
                'color'   => $color,
            ],
        ]);
    }

    // -------------------------------------------------------
    // BOOKING
    // -------------------------------------------------------

    public static function bookingBaru(int $providerId, string $userName, string $bookableName, string $bookingUrl): void
    {
        self::send(
            $providerId,
            'booking.baru',
            "Booking baru dari {$userName} untuk {$bookableName}",
            $bookingUrl,
            'fa-bookmark',
            'blue'
        );
    }

    public static function bookingDisetujui(int $userId, string $bookableName, string $bookingUrl): void
    {
        self::send(
            $userId,
            'booking.disetujui',
            "Booking kamu di \"{$bookableName}\" telah disetujui",
            $bookingUrl,
            'fa-check-circle',
            'green'
        );
    }

    public static function bookingDitolak(int $userId, string $bookableName, string $bookingUrl): void
    {
        self::send(
            $userId,
            'booking.ditolak',
            "Booking kamu di \"{$bookableName}\" ditolak",
            $bookingUrl,
            'fa-times-circle',
            'red'
        );
    }

    // -------------------------------------------------------
    // MARKETPLACE — PESANAN
    // -------------------------------------------------------

    public static function pesananBaru(int $sellerId, string $buyerName, string $productName, string $orderUrl): void
    {
        self::send(
            $sellerId,
            'pesanan.baru',
            "Pesanan baru dari {$buyerName} untuk \"{$productName}\"",
            $orderUrl,
            'fa-shopping-cart',
            'blue'
        );
    }

    public static function statusPesananDiupdate(int $buyerId, string $productName, string $status, string $orderUrl): void
    {
        $labelStatus = match($status) {
            'confirmed'   => 'dikonfirmasi seller',
            'in_progress' => 'sedang diproses',
            'completed'   => 'selesai',
            'cancelled'   => 'dibatalkan',
            default       => $status,
        };

        $color = match($status) {
            'confirmed'   => 'blue',
            'in_progress' => 'indigo',
            'completed'   => 'green',
            'cancelled'   => 'red',
            default       => 'gray',
        };

        self::send(
            $buyerId,
            'pesanan.update',
            "Pesanan \"{$productName}\" {$labelStatus}",
            $orderUrl,
            'fa-box',
            $color
        );
    }

    // -------------------------------------------------------
    // APPROVAL SELLER
    // -------------------------------------------------------

    public static function sellerDisetujui(int $userId): void
    {
        self::send(
            $userId,
            'seller.disetujui',
            'Selamat! Akun penjual kamu sudah aktif. Mulai jual sekarang!',
            route('user.marketplace.seller.home'),
            'fa-store',
            'green'
        );
    }

    public static function sellerDitolak(int $userId, string $reason): void
    {
        self::send(
            $userId,
            'seller.ditolak',
            "Pengajuan penjual kamu ditolak: {$reason}",
            route('user.marketplace.sell'),
            'fa-store',
            'red'
        );
    }

    // -------------------------------------------------------
    // APPROVAL PROVIDER
    // -------------------------------------------------------

    public static function providerDisetujui(int $userId, string $roleLabel): void
    {
        self::send(
            $userId,
            'provider.disetujui',
            "Selamat! Akun provider {$roleLabel} kamu sudah aktif. Mulai buat listing sekarang!",
            '/',
            'fa-building',
            'green'
        );
    }

    public static function providerDitolak(int $userId, string $roleLabel, string $reason): void
    {
        self::send(
            $userId,
            'provider.ditolak',
            "Pengajuan provider {$roleLabel} kamu ditolak: {$reason}",
            '/',
            'fa-building',
            'red'
        );
    }

    public static function sendBookingNotification(Booking $booking, string $type): void
    {
        // Load relasi kalau belum
        $booking->loadMissing(['user', 'bookable']);

        $bookableName = $booking->bookable->name ?? 'listing';
        $userName     = $booking->user->name ?? 'Pengguna';
        $providerId   = $booking->bookable->provider_id ?? null;
        $userId       = $booking->user_id ?? null;

        $userUrl     = url('/user/bookings');
        $providerUrl = $providerId
            ? url('/provider/residence/bookings/' . $booking->id)
            : url('/');

        // Guard — jangan kirim notif kalau ID tidak valid
        if (!$userId && in_array($type, ['booking_approved', 'booking_rejected', 'booking_cancelled'])) {
            return;
        }

        if (!$providerId && in_array($type, ['new_booking', 'payment_received'])) {
            return;
        }

        match($type) {

            'new_booking' => self::send(
                $providerId,
                'booking.baru',
                "Booking baru dari {$userName} untuk \"{$bookableName}\"",
                $providerUrl,
                'fa-bookmark',
                'blue'
            ),

            'booking_approved' => self::send(
                $userId,
                'booking.disetujui',
                "Booking kamu di \"{$bookableName}\" telah disetujui",
                $userUrl,
                'fa-check-circle',
                'green'
            ),

            'booking_rejected' => self::send(
                $userId,
                'booking.ditolak',
                "Booking kamu di \"{$bookableName}\" ditolak",
                $userUrl,
                'fa-times-circle',
                'red'
            ),

            'booking_cancelled' => $providerId ? self::send(
                $providerId,
                'booking.dibatalkan',
                "Booking dari {$userName} untuk \"{$bookableName}\" dibatalkan",
                $providerUrl,
                'fa-calendar-times',
                'red'
            ) : null,

            'payment_received' => $providerId ? self::send(
                $providerId,
                'booking.pembayaran',
                "Pembayaran diterima dari {$userName} untuk \"{$bookableName}\"",
                $providerUrl,
                'fa-money-bill-wave',
                'green'
            ) : null,

            default => null,
        };
    }

    // Wrapper instance → delegate ke static, agar BookingService bisa inject via constructor
    public function __call(string $method, array $args): mixed
    {
        if (method_exists(static::class, $method)) {
            return static::$method(...$args);
        }

        throw new \BadMethodCallException("Method {$method} tidak ditemukan di NotificationService");
    }
}