<?php

namespace App\Infrastructure\Http\Resources\Catalog;

use App\Infrastructure\Http\Resources\Store\StoreResource;
use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Category */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'store' => new StoreResource($this->whenLoaded('store')),
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
            'children' => self::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
