<?php

namespace App\Infrastructure\Http\Resources\Store;

use App\Infrastructure\Persistence\Eloquent\Store\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Client */
class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'store' => new StoreResource($this->whenLoaded('store')),
            'addresses' => ClientAddressResource::collection($this->whenLoaded('addresses')),
            'orders_count' => $this->whenCounted('orders'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
