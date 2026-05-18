<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketplaceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'buyer_id',
        'seller_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'buyer_name',
        'buyer_phone',
        'buyer_address',
        'pickup_method',
        'pickup_address',
        'pickup_notes',
        'status',
        'payment_method',
        'payment_status',
        'payment_proof',
        'seller_notes',
        'cancellation_reason',
        'completed_at',
        'cancelled_at',
        'payment_deadline',      // ← BARU: deadline upload bukti bayar (1 jam dari created_at)
    ];

    protected $casts = [
        'unit_price'       => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'completed_at'     => 'datetime',
        'cancelled_at'     => 'datetime',
        'payment_deadline' => 'datetime',   // ← BARU
    ];

    protected $appends = [
        'status_label',
        'payment_status_label',
        'pickup_method_label',
        'payment_proof_url',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MarketplaceProduct::class, 'product_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class, 'transaction_id');
    }

    public function scopeByBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    public function scopeBySeller($query, $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending'     => 'Menunggu Konfirmasi',
            'confirmed'   => 'Dikonfirmasi',
            'in_progress' => 'Dalam Proses',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
            'refunded'    => 'Dikembalikan',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute()
    {
        $statuses = [
            'pending'  => 'Menunggu Pembayaran',
            'paid'     => 'Sudah Dibayar',
            'failed'   => 'Pembayaran Gagal',
            'refunded' => 'Dikembalikan',
        ];

        return $statuses[$this->payment_status] ?? $this->payment_status;
    }

    public function getPickupMethodLabelAttribute()
    {
        $methods = [
            'pickup'   => 'Ambil Sendiri',
            'delivery' => 'Diantar',
            'meetup'   => 'Bertemu',
        ];

        return $methods[$this->pickup_method] ?? $this->pickup_method;
    }

    public function getPaymentProofUrlAttribute()
    {
        if ($this->payment_proof) {
            return asset('storage/' . $this->payment_proof);
        }

        return null;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeCompleted()
    {
        return in_array($this->status, ['confirmed', 'in_progress']);
    }

    public function canBeRated()
    {
        return $this->status === 'completed' && !$this->rating;
    }

    /**
     * Apakah deadline pembayaran sudah terlewat?
     */
    public function isPaymentExpired(): bool
    {
        return $this->payment_deadline !== null
            && $this->payment_deadline->isPast();
    }

    public static function generateTransactionCode()
    {
        do {
            $code = 'MP' . date('Ymd') . strtoupper(uniqid());
        } while (self::where('transaction_code', $code)->exists());

        return $code;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = self::generateTransactionCode();
            }

            // ← BARU: set payment_deadline 1 jam dari sekarang saat transaksi dibuat
            if (empty($transaction->payment_deadline)) {
                $transaction->payment_deadline = now()->addHour();
            }
        });
    }
}