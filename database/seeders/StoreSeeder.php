<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Store\Address;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Infrastructure\Persistence\Eloquent\Store\ClientAddress;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use App\Infrastructure\Persistence\Eloquent\Store\StoreConfiguration;
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
        $centro = Store::factory()->create([
            'name' => 'Mega Tienda Centro',
            'slug' => 'mega-tienda-centro',
        ]);

        $norte = Store::factory()->create([
            'name' => 'Mega Tienda Norte',
            'slug' => 'mega-tienda-norte',
        ]);

        $sur = Store::factory()->create([
            'name' => 'Mega Tienda Sur',
            'slug' => 'mega-tienda-sur',
        ]);

        User::factory()->admin()->forStore($centro)->create([
            'name' => 'Admin Centro',
            'email' => 'centro@megatienda.test',
        ]);

        StoreConfiguration::factory()->for($centro, 'store')->create([
            'main_color' => '#0F766E',
            'second_color' => '#14B8A6',
            'third_color' => '#FEF3C7',
            'address' => 'Av. Madero 15',
            'city' => 'Ciudad de México',
            'state' => 'CDMX',
            'postal_code' => '06000',
            'latitude' => 19.4326,
            'longitude' => -99.1332,
        ]);

        User::factory()->admin()->forStore($norte)->create([
            'name' => 'Admin Norte',
            'email' => 'norte@megatienda.test',
        ]);

        StoreConfiguration::factory()->for($norte, 'store')->create([
            'main_color' => '#1D4ED8',
            'second_color' => '#60A5FA',
            'third_color' => '#DBEAFE',
            'address' => 'Av. Insurgentes Norte 500',
            'city' => 'Ciudad de México',
            'state' => 'CDMX',
            'postal_code' => '11450',
            'latitude' => 19.4908,
            'longitude' => -99.1547,
        ]);

        User::factory()->admin()->forStore($sur)->create([
            'name' => 'Admin Sur',
            'email' => 'sur@megatienda.test',
        ]);

        StoreConfiguration::factory()->for($sur, 'store')->create([
            'main_color' => '#B91C1C',
            'second_color' => '#F87171',
            'third_color' => '#FEE2E2',
            'address' => 'Av. Insurgentes Sur 1457',
            'city' => 'Ciudad de México',
            'state' => 'CDMX',
            'postal_code' => '03210',
            'latitude' => 19.3615,
            'longitude' => -99.1728,
        ]);

        $stores = Store::all();

        User::where('is_admin', false)->get()->each(function (User $user) use ($stores): void {
            Address::factory()->default()->for($user)->create();

            $client = Client::factory()
                ->forUser($user)
                ->forStore($stores->random())
                ->create();

            ClientAddress::factory()->default()->for($client)->create();
            ClientAddress::factory()->for($client)->create();
        });
    }
}
