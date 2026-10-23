<?php

namespace App\Infrastructure\Persistence\Eloquent\Store;

use Database\Factories\Store\StoreConfigurationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'store_id',
    'logo_path',
    'main_color',
    'second_color',
    'third_color',
    'facebook_url',
    'instagram_url',
    'x_url',
    'tiktok_url',
    'whatsapp_url',
    'address',
    'city',
    'state',
    'postal_code',
    'country',
    'latitude',
    'longitude',
])]
class StoreConfiguration extends Model
{
    use HasFactory;

    /** @var class-string<StoreConfigurationFactory> */
    protected static $factory = StoreConfigurationFactory::class;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
