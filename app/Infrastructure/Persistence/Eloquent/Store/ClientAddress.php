<?php

namespace App\Infrastructure\Persistence\Eloquent\Store;

use Database\Factories\Store\ClientAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'recipient_name', 'phone', 'line1', 'line2', 'city', 'state', 'postal_code', 'country', 'is_default'])]
class ClientAddress extends Model
{
    use HasFactory;

    /** @var class-string<ClientAddressFactory> */
    protected static $factory = ClientAddressFactory::class;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
