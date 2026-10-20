<?php

namespace App\Infrastructure\Persistence\Eloquent\Sales;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @var class-string<\Database\Factories\Sales\OrderFactory> */
    protected static $factory = \Database\Factories\Sales\OrderFactory::class;

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'customer_name',
        'customer_email',
        'shipping_address',
    ];

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
