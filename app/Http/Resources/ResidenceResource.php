<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResidenceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'rental_period' => $this->rental_period,
            'price' => $this->price,
            'capacity' => $this->capacity,
            'available_slots' => $this->available_slots,
            'facilities' => $this->facilities,
            'images' => $this->images,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discounted_price' => $this->getDiscountedPrice(),
            'average_rating' => round($this->ratings_avg_rating ?? 0, 1),
            'has_active_booking' => auth('sanctum')->check() ? \App\Models\Booking::where('user_id', auth('sanctum')->id())
                ->where('bookable_type', \App\Models\Residence::class)
                ->where('bookable_id', $this->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists() : false,
            'is_active' => $this->is_active,
            'residence_type' => $this->residence_type,
            'kos_type'       => $this->kos_type,
            'room_size'      => $this->room_size,
            'bedroom_count'  => $this->bedroom_count,
            'bathroom_count' => $this->bathroom_count,
            'building_size'  => $this->building_size,
            'land_size'      => $this->land_size,
            'unit_type'      => $this->unit_type,
            'floor_number'   => $this->floor_number,
            'tower_name'     => $this->tower_name,
            'furnish_status' => $this->furnish_status,
            'ratings' => $this->whenLoaded('ratings'),
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
