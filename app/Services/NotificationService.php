<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Str;

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
}