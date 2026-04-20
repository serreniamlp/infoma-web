<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'category_id',
        'name',
        'description',
        'location',
        'latitude',
        'longitude',
        'event_date',
        'registration_deadline',
        'price',
        'capacity',
        'available_slots',
        'images',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'event_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function getDiscountedPrice(): float
    {
        $basePrice = (float) ($this->price ?? 0);

        if (!$this->discount_type || $this->discount_value === null) {
            return $basePrice;
        }

        if ($this->discount_type === 'percentage') {
            $discount = $basePrice * ((float) $this->discount_value) / 100.0;
            return max(0.0, $basePrice - $discount);
        }

        return max(0.0, $basePrice - (float) $this->discount_value);
    }

    public function provider() {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function bookings() {
        return $this->morphMany(Booking::class, 'bookable');
    }

    public function ratings() {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function bookmarks() {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function approvedRevenue(): float
    {
        return (float) DB::table('transactions')
            ->join('bookings', 'transactions.booking_id', '=', 'bookings.id')
            ->where('bookings.bookable_type', Activity::class)
            ->where('bookings.bookable_id', $this->id)
            ->where('bookings.status', 'approved')
            ->where('transactions.payment_status', 'paid')
            ->sum('transactions.final_amount');
    }
}
