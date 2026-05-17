<?php
// app/Http/Resources/MarketplaceTransactionResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            'transaction_code'    => $this->transaction_code,
            'quantity'            => $this->quantity,
            'unit_price'          => $this->unit_price,
            'total_amount'        => $this->total_amount,
            'status'              => $this->status,
            'payment_status'      => $this->payment_status,
            'payment_method'      => $this->payment_method,
            'pickup_method'       => $this->pickup_method,
            'pickup_address'      => $this->pickup_address,
            'pickup_notes'        => $this->pickup_notes,
            'buyer_name'          => $this->buyer_name,
            'buyer_phone'         => $this->buyer_phone,
            'buyer_address'       => $this->buyer_address,
            'seller_notes'        => $this->seller_notes,
            'cancellation_reason' => $this->cancellation_reason,
            'payment_proof'       => $this->payment_proof
                ? asset('storage/' . $this->payment_proof)
                : null,
            'product'             => new MarketplaceProductResource($this->whenLoaded('product')),
            'buyer'               => $this->whenLoaded('buyer', fn() => [
                'id'    => $this->buyer->id,
                'name'  => $this->buyer->name,
                'phone' => $this->buyer->phone,
            ]),
            'seller'              => $this->whenLoaded('seller', fn() => [
                'id'    => $this->seller->id,
                'name'  => $this->seller->name,
                'phone' => $this->seller->phone,
            ]),
            'completed_at'        => $this->completed_at,
            'cancelled_at'        => $this->cancelled_at,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}