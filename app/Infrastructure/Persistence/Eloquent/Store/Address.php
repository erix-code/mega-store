<?php

namespace App\Infrastructure\Persistence\Eloquent\Store;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    /** @var class-string<\Database\Factories\Store\AddressFactory> */
    protected static $factory = \Database\Factories\Store\AddressFactory::class;

    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone',
        'line1',
        'line2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
