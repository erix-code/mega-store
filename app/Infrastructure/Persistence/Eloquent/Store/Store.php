<?php

namespace App\Infrastructure\Persistence\Eloquent\Store;

use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use Database\Factories\Store\StoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'email', 'phone', 'is_active'])]
class Store extends Model
{
    use HasFactory;

    /** @var class-string<StoreFactory> */
    protected static $factory = StoreFactory::class;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
