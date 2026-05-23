<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'transaction_code',
        'original_amount',
        'discount_amount',
        'final_amount',
        'payment_method',
        'payment_status',
        'payment_proof',
        'snap_token',               // ← Midtrans: token untuk Snap popup
        'midtrans_transaction_id',  // ← Midtrans: ID transaksi dari Midtrans
        'midtrans_payment_type',    // ← Midtrans: tipe pembayaran (bank_transfer, gopay, dll)
    ];

    public function booking() {
        return $this->belongsTo(Booking::class);
    }
}
