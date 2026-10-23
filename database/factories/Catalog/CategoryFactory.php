<?php

namespace Database\Factories\Catalog;

use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = Str::title($this->faker->unique()->words(2, true));

        return [
            'store_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
            'is_active' => true,
        ];
    }

    public function forStore(Store $store): static
    {
        return $this->state(fn (array $attributes) => ['store_id' => $store->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
