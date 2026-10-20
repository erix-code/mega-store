<?php

namespace App\Infrastructure\Persistence\Eloquent\Catalog;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @var class-string<\Database\Factories\Catalog\ProductFactory> */
    protected static $factory = \Database\Factories\Catalog\ProductFactory::class;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'category_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(\App\Infrastructure\Persistence\Eloquent\Sales\OrderItem::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function inStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }
}
