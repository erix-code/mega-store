<?php

namespace Database\Factories\Sales;

use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Sales\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->words(3, true),
            'unit_price' => $this->faker->randomFloat(2, 10, 5000),
            'quantity' => $this->faker->numberBetween(1, 5),
            'subtotal' => fn (array $attributes) => round($attributes['unit_price'] * $attributes['quantity'], 2),
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
        ]);
    }
}
