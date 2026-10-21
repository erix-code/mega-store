<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Store\Address;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Store::factory()->create([
            'name' => 'Mega Tienda Centro',
            'slug' => 'mega-tienda-centro',
        ]);

        Store::factory()->create([
            'name' => 'Mega Tienda Norte',
            'slug' => 'mega-tienda-norte',
        ]);

        Store::factory()->create([
            'name' => 'Mega Tienda Sur',
            'slug' => 'mega-tienda-sur',
        ]);

        User::where('is_admin', false)->get()->each(function (User $user): void {
            Address::factory()->default()->for($user)->create();
            Client::factory()->forUser($user)->create();
        });
    }
}
