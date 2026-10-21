<?php

namespace App\Infrastructure\Http\Resources\Sales;

use App\Infrastructure\Persistence\Eloquent\Sales\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderItem */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'unit_price' => (float) $this->unit_price,
            'quantity' => $this->quantity,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
