<?php

namespace App\Infrastructure\Persistence\Eloquent\Sales;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use Database\Factories\Sales\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['client_id', 'status', 'total', 'shipping_address'])]
class Order extends Model
{
    use HasFactory;

    /** @var class-string<OrderFactory> */
    protected static $factory = OrderFactory::class;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
            'shipping_address' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    #[Scope]
    protected function status(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }
}
