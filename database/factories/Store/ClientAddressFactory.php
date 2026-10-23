<?php

namespace Database\Factories\Store;

use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Infrastructure\Persistence\Eloquent\Store\ClientAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientAddressFactory extends Factory
{
    protected $model = ClientAddress::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'recipient_name' => $this->faker->name(),
            'phone' => $this->faker->numerify('##########'),
            'line1' => $this->faker->streetAddress(),
            'line2' => $this->faker->optional()->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->numerify('#####'),
            'country' => 'MX',
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => ['is_default' => true]);
    }
}
