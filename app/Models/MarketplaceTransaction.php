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
'cancelled_at'
];

protected $casts = [
'unit_price' => 'decimal:2',
'total_amount' => 'decimal:2',
'completed_at' => 'datetime',
'cancelled_at' => 'datetime'
];

protected $appends = [
'status_label',
'payment_status_label',
'pickup_method_label',
'payment_proof_url'
];

/**
* Relationship with buyer (User).
*/
public function buyer(): BelongsTo
{
return $this->belongsTo(User::class, 'buyer_id');
}

/**
* Relationship with seller (User).
*/
public function seller(): BelongsTo
{
return $this->belongsTo(User::class, 'seller_id');
}

/**
* Relationship with product.
*/
public function product(): BelongsTo
{
return $this->belongsTo(MarketplaceProduct::class, 'product_id');
}

/**
* Relationship with rating/review.
*/
public function rating(): HasOne
{
return $this->hasOne(Rating::class, 'transaction_id');
}

/**
* Scope for transactions by buyer.
*/
public function scopeByBuyer($query, $buyerId)
{
return $query->where('buyer_id', $buyerId);
}

/**
* Scope for transactions by seller.
*/
public function scopeBySeller($query, $sellerId)
{
return $query->where('seller_id', $sellerId);
}

/**
* Scope for transactions by status.
*/
public function scopeByStatus($query, $status)
{
return $query->where('status', $status);
}

/**
* Scope for completed transactions.
*/
public function scopeCompleted($query)
{
return $query->where('status', 'completed');
}

/**
* Scope for pending transactions.
*/
public function scopePending($query)
{
return $query->where('status', 'pending');
}

/**
* Get status label.
*/
public function getStatusLabelAttribute()
{
$statuses = [
'pending' => 'Menunggu Konfirmasi',
'confirmed' => 'Dikonfirmasi',
'in_progress' => 'Dalam Proses',
'completed' => 'Selesai',
'cancelled' => 'Dibatalkan',
'refunded' => 'Dikembalikan'
];

return $statuses[$this->status] ?? $this->status;
}

/**
* Get payment status label.
*/
public function getPaymentStatusLabelAttribute()
{
$statuses = [
'pending' => 'Menunggu Pembayaran',
'paid' => 'Sudah Dibayar',
'failed' => 'Pembayaran Gagal',
'refunded' => 'Dikembalikan'
];

return $statuses[$this->payment_status] ?? $this->payment_status;
}

/**
* Get pickup method label.
*/
public function getPickupMethodLabelAttribute()
{
$methods = [
'pickup' => 'Ambil Sendiri',
'delivery' => 'Diantar',
'meetup' => 'Bertemu'
];

return $methods[$this->pickup_method] ?? $this->pickup_method;
}

/**
* Get payment proof URL.
*/
public function getPaymentProofUrlAttribute()
{
if ($this->payment_proof) {
return asset('storage/' . $this->payment_proof);
}

return null;
}

/**
* Check if transaction can be cancelled.
*/
public function canBeCancelled()
{
return in_array($this->status, ['pending', 'confirmed']);
}

/**
* Check if transaction can be completed.
*/
public function canBeCompleted()
{
return in_array($this->status, ['confirmed', 'in_progress']);
}

/**
* Check if buyer can rate this transaction.
*/
public function canBeRated()
{
return $this->status === 'completed' && !$this->rating;
}

/**
* Generate unique transaction code.
*/
public static function generateTransactionCode()
{
do {
$code = 'MP' . date('Ymd') . strtoupper(uniqid());
} while (self::where('transaction_code', $code)->exists());

return $code;
}

/**
* Boot method for model events.
*/
protected static function boot()
{
parent::boot();

static::creating(function ($transaction) {
if (empty($transaction->transaction_code)) {
$transaction->transaction_code = self::generateTransactionCode();
}
});
}
}