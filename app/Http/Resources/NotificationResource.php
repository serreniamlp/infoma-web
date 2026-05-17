<?php
// app/Http/Resources/NotificationResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'message'   => $this->data['message'] ?? '',
            'url'       => $this->data['url'] ?? '/',
            'icon'      => $this->data['icon'] ?? 'fa-bell',
            'color'     => $this->data['color'] ?? 'blue',
            'is_unread' => is_null($this->read_at),
            'time'      => $this->created_at->diffForHumans(),
            'created_at'=> $this->created_at,
        ];
    }
}