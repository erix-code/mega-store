<?php

namespace App\Infrastructure\Http\Resources\Store;

use App\Infrastructure\Persistence\Eloquent\Store\StoreConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin StoreConfiguration */
class StoreConfigurationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_path !== null ? Storage::disk('public')->url($this->logo_path) : null,
            'main_color' => $this->main_color,
            'second_color' => $this->second_color,
            'third_color' => $this->third_color,
            'social_links' => [
                'facebook' => $this->facebook_url,
                'instagram' => $this->instagram_url,
                'x' => $this->x_url,
                'tiktok' => $this->tiktok_url,
                'whatsapp' => $this->whatsapp_url,
            ],
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
        ];
    }
}
