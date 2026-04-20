<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'transaction_code' => $this->transaction_code,
            'original_amount' => $this->original_amount,
            'discount_amount' => $this->discount_amount,
            'final_amount' => $this->final_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_proof' => $this->payment_proof,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}





















