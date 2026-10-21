<?php

namespace Database\Factories\Store;

use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('##########'),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => ['user_id' => null]);
    }
}
