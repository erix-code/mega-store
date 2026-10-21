<?php

namespace Database\Factories\Catalog;

use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = Str::title($this->faker->unique()->words(3, true));

        return [
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-#####')),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 5000),
            'stock' => $this->faker->numberBetween(0, 100),
            'store_id' => Store::factory(),
            'category_id' => Category::factory(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock' => 0]);
    }
}
