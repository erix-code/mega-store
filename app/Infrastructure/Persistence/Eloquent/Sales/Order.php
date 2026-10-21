<?php

namespace App\Infrastructure\Persistence\Eloquent\Sales;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Models\User;
use Database\Factories\Sales\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'status', 'total', 'customer_name', 'customer_email', 'shipping_address'])]
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
