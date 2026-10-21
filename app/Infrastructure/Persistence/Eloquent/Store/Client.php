<?php

namespace App\Infrastructure\Persistence\Eloquent\Store;

use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Models\User;
use Database\Factories\Store\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'email', 'phone'])]
class Client extends Model
{
    use HasFactory;

    /** @var class-string<ClientFactory> */
    protected static $factory = ClientFactory::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
