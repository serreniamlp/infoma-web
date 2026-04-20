<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'category_id',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'rental_period',
        'price',
        'capacity',
        'available_slots',
        'facilities',
        'images',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    protected $casts = [
        'facilities' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function getPricePerMonthAttribute()
    {
        return $this->attributes['price'] ?? null;
    }

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

        // flat
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
}
