<?php

namespace Database\Factories\Store;

use App\Infrastructure\Persistence\Eloquent\Store\Store;
use App\Infrastructure\Persistence\Eloquent\Store\StoreConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreConfigurationFactory extends Factory
{
    protected $model = StoreConfiguration::class;

    public function definition(): array
    {
        $handle = $this->faker->slug(2);

        return [
            'store_id' => Store::factory(),
            'logo_path' => null,
            'main_color' => Str::upper($this->faker->hexcolor()),
            'second_color' => Str::upper($this->faker->hexcolor()),
            'third_color' => Str::upper($this->faker->hexcolor()),
            'facebook_url' => "https://facebook.com/{$handle}",
            'instagram_url' => "https://instagram.com/{$handle}",
            'x_url' => "https://x.com/{$handle}",
            'tiktok_url' => "https://tiktok.com/@{$handle}",
            'whatsapp_url' => 'https://wa.me/52'.$this->faker->numerify('##########'),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->numerify('#####'),
            'country' => 'MX',
            'latitude' => $this->faker->latitude(19.30, 19.60),
            'longitude' => $this->faker->longitude(-99.30, -99.00),
        ];
    }
}
