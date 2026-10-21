<?php

namespace App\Infrastructure\Persistence\Eloquent\Catalog;

use Database\Factories\Catalog\ProductImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'path', 'is_main'])]
class ProductImage extends Model
{
    use HasFactory;

    /** @var class-string<ProductImageFactory> */
    protected static $factory = ProductImageFactory::class;

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
