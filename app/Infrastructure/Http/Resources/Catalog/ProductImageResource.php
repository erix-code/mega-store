<?php

namespace App\Infrastructure\Http\Resources\Catalog;

use App\Infrastructure\Persistence\Eloquent\Catalog\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductImage */
class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'is_main' => $this->is_main,
        ];
    }
}
