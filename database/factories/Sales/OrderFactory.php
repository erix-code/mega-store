<?php

namespace Database\Factories\Sales;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'status' => OrderStatus::Pending,
            'total' => 0,
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
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

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => ['user_id' => null]);
    }
}
