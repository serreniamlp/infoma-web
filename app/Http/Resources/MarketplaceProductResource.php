<?php
// app/Http/Resources/MarketplaceProductResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'price'          => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'status'         => $this->status,
            'is_available'   => $this->is_available,
            'average_rating' => round($this->ratings_avg_rating ?? 0, 1),
            'ratings_count'  => $this->ratings_count ?? 0,
            'views_count'    => $this->views_count ?? 0,
            'images'         => $this->images,
            'condition'      => $this->condition ?? null,
            'tags'           => $this->tags ?? [],
            'pickup_methods' => $this->pickup_methods ?? ['pickup'], // Default 'pickup' if empty
            'pickup_address' => $this->pickup_address,
            'pickup_methods_label' => $this->pickup_methods_label,
            'location'       => $this->location,
            'category'       => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
            'seller'         => $this->whenLoaded('seller', fn() => [
                'id'           => $this->seller->id,
                'name'         => $this->seller->name,
                'phone'        => $this->seller->phone,
                'last_seen_at' => $this->seller->last_seen_at,
                'created_at'   => $this->seller->created_at,
            ]),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
