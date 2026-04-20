<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bookable_type',
        'bookable_id',
        'booking_code',
        'check_in_date',
        'check_out_date',
        'documents',
        'status',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'documents' => 'array',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function bookable() {
        return $this->morphTo();
    }

    public function transaction() {
        return $this->hasOne(Transaction::class);
    }
}
