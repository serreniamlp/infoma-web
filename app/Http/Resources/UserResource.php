<?php
// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'address'          => $this->address,
            'profile_picture'  => $this->profile_picture
                ? url('/api/v1/file/' . $this->profile_picture)
                : null,
            'roles'            => $this->roles->pluck('name'),
            'is_seller'        => $this->is_seller,
            'seller_status'    => $this->seller_status,
            'provider_status'  => $this->provider_status,
            'is_active'        => $this->is_active ?? true,
            'created_at'       => $this->created_at,
        ];
    }
}