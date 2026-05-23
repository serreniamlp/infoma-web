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
            'images'         => $this->images,
            'condition'      => $this->condition ?? null,
            'category'       => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
            'seller'         => $this->whenLoaded('seller', fn() => [
                'id'    => $this->seller->id,
                'name'  => $this->seller->name,
                'phone' => $this->seller->phone,
            ]),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
