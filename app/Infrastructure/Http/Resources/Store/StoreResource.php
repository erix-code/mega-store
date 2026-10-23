<?php

namespace App\Infrastructure\Http\Resources\Store;

use App\Infrastructure\Persistence\Eloquent\Store\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Store */
class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'configuration' => new StoreConfigurationResource($this->whenLoaded('configuration')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
