<?php

namespace Database\Factories\Sales;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'status' => OrderStatus::Pending,
            'total' => 0,
            'shipping_address' => [
                'line1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'postal_code' => $this->faker->numerify('#####'),
                'country' => 'MX',
            ],
        ];
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }

    public function forClient(Client $client): static
    {
        return $this->state(fn (array $attributes) => ['client_id' => $client->id]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => Client::factory()->guest(),
        ]);
    }
}
