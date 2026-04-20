<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('calculateDiscount')) {
    function calculateDiscount($originalPrice, $discountType, $discountValue)
    {
        if (!$discountType || !$discountValue) {
            return $originalPrice;
        }

        if ($discountType === 'percentage') {
            return $originalPrice - ($originalPrice * $discountValue / 100);
        } else {
            return max(0, $originalPrice - $discountValue);
        }
    }
}

if (!function_exists('getDiscountAmount')) {
    function getDiscountAmount($originalPrice, $discountType, $discountValue)
    {
        if (!$discountType || !$discountValue) {
            return 0;
        }

        if ($discountType === 'percentage') {
            return $originalPrice * $discountValue / 100;
        } else {
            return min($originalPrice, $discountValue);
        }
    }
}

if (!function_exists('getBookingStatusBadge')) {
    function getBookingStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Menunggu</span>',
            'approved' => '<span class="badge badge-success">Disetujui</span>',
            'rejected' => '<span class="badge badge-danger">Ditolak</span>',
            'completed' => '<span class="badge badge-info">Selesai</span>',
            'cancelled' => '<span class="badge badge-secondary">Dibatalkan</span>'
        ];

        return $badges[$status] ?? '<span class="badge badge-light">Unknown</span>';
    }
}

if (!function_exists('getPaymentStatusBadge')) {
    function getPaymentStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Menunggu Pembayaran</span>',
            'paid' => '<span class="badge badge-success">Sudah Dibayar</span>',
            'failed' => '<span class="badge badge-danger">Gagal</span>'
        ];

        return $badges[$status] ?? '<span class="badge badge-light">Unknown</span>';
    }
}


