<?php

namespace App\Infrastructure\Persistence\Eloquent\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    /** @var class-string<\Database\Factories\Catalog\ProductImageFactory> */
    protected static $factory = \Database\Factories\Catalog\ProductImageFactory::class;

    protected $fillable = [
        'product_id',
        'path',
        'is_main',
    ];

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
