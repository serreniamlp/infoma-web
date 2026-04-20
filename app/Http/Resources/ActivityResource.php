<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'event_date' => $this->event_date,
            'registration_deadline' => $this->registration_deadline,
            'price' => $this->price,
            'capacity' => $this->capacity,
            'available_slots' => $this->available_slots,
            'images' => $this->images,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discounted_price' => $this->getDiscountedPrice(),
            'average_rating' => round($this->ratings_avg_rating ?? 0, 1),
            'is_active' => $this->is_active,
            'registration_status' => $this->registration_deadline > now() ? 'open' : 'closed',
            'provider' => [
                'id' => $this->provider->id,
                'name' => $this->provider->name,
                'email' => $this->provider->email,
                'phone' => $this->provider->phone
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'type' => $this->category->type
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

