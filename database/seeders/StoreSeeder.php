<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Store\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::where('email', '!=', 'admin@megatienda.test')->get()->each(
            fn (User $user) => Address::factory()->default()->for($user)->create()
        );
    }
}
