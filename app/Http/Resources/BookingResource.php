<?php
// app/Http/Resources/BookingResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'booking_code'      => $this->booking_code,
            'bookable_id'       => $this->bookable_id,
            'status'            => $this->status,
            'payment_deadline'  => $this->payment_deadline,
            'payment_expired'   => $this->payment_deadline
                ? now()->isAfter($this->payment_deadline)
                : false,
            'payment_remaining' => $this->payment_deadline && now()->isBefore($this->payment_deadline)
                ? now()->diffForHumans($this->payment_deadline, true)
                : null,
            'check_in_date'     => $this->check_in_date,
            'check_out_date'    => $this->check_out_date,
            'duration_months'   => $this->duration_months,
            'total_price'       => $this->total_price,
            'notes'             => $this->notes,
            'rejection_reason'  => $this->rejection_reason,
            'is_renewal'        => (bool) $this->is_renewal,
            'bookable_type'     => class_basename($this->bookable_type),
            'bookable'          => $this->whenLoaded('bookable', fn() => [
                'id'    => $this->bookable->id,
                'name'  => $this->bookable->name,
                'address' => $this->bookable->address ?? $this->bookable->location,
                'images'  => $this->bookable->images,
                'price'   => $this->bookable->price,
                'provider' => $this->bookable->provider ? [
                    'id' => $this->bookable->provider->id,
                    'name' => $this->bookable->provider->name,
                    'email' => $this->bookable->provider->email,
                    'phone' => $this->bookable->provider->phone,
                ] : null,
            ]),
            'user'              => $this->whenLoaded('user', fn() => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),
            'transaction'       => new TransactionResource($this->whenLoaded('transaction')),
            // Field khusus event
            'participant_name'  => $this->participant_name,
            'participant_email' => $this->participant_email,
            'participant_phone' => $this->participant_phone,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
