<?php

namespace App\Infrastructure\Persistence\Eloquent\Catalog;

use App\Infrastructure\Persistence\Eloquent\Sales\OrderItem;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use Database\Factories\Catalog\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['sku', 'name', 'slug', 'description', 'price', 'stock', 'store_id', 'category_id', 'is_active'])]
class Product extends Model
{
    use HasFactory;

    /** @var class-string<ProductFactory> */
    protected static $factory = ProductFactory::class;

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = $product->generateUniqueSlug($product->name);
            }
        });

        static::updating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = $product->generateUniqueSlug($product->name);
            }
        });
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $suffix = static::query()->where('slug', 'like', "{$base}%")->count();

        return $suffix === 0 ? $base : "{$base}-{$suffix}";
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
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
        return $this->hasMany(OrderItem::class);
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
