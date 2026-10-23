<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Store\Store;
use App\Infrastructure\Persistence\Eloquent\Store\StoreConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_stores(): void
    {
        Store::factory()->create(['name' => 'Mega Tienda Centro', 'slug' => 'mega-tienda-centro']);
        Store::factory()->create(['name' => 'Mega Tienda Norte', 'slug' => 'mega-tienda-norte']);
        Store::factory()->inactive()->create(['name' => 'Tienda Oculta', 'slug' => 'oculta']);

        $response = $this->getJson('/api/v1/stores')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertEqualsCanonicalizing(
            ['mega-tienda-centro', 'mega-tienda-norte'],
            collect($response->json('data'))->pluck('slug')->all()
        );
    }

    public function test_shows_a_store_with_its_configuration(): void
    {
        $store = Store::factory()->create([
            'name' => 'Mega Tienda Centro',
            'slug' => 'mega-tienda-centro',
        ]);

        StoreConfiguration::factory()->for($store, 'store')->create([
            'main_color' => '#0F766E',
            'second_color' => '#14B8A6',
            'third_color' => '#FEF3C7',
            'facebook_url' => 'https://facebook.com/megatienda',
            'address' => 'Av. Madero 15',
            'city' => 'Ciudad de México',
            'latitude' => 19.4326,
            'longitude' => -99.1332,
        ]);

        $this->getJson('/api/v1/stores/mega-tienda-centro')
            ->assertOk()
            ->assertJsonPath('data.configuration.main_color', '#0F766E')
            ->assertJsonPath('data.configuration.second_color', '#14B8A6')
            ->assertJsonPath('data.configuration.third_color', '#FEF3C7')
            ->assertJsonPath('data.configuration.social_links.facebook', 'https://facebook.com/megatienda')
            ->assertJsonPath('data.configuration.address', 'Av. Madero 15')
            ->assertJsonPath('data.configuration.city', 'Ciudad de México')
            ->assertJsonPath('data.configuration.latitude', 19.4326)
            ->assertJsonPath('data.configuration.longitude', -99.1332)
            ->assertJsonPath('data.configuration.logo_url', null);
    }

    public function test_store_without_configuration_omits_the_section(): void
    {
        Store::factory()->create(['name' => 'Tienda Sola', 'slug' => 'tienda-sola']);

        $this->getJson('/api/v1/stores/tienda-sola')
            ->assertOk()
            ->assertJsonMissing(['configuration']);
    }

    public function test_shows_a_store_by_slug(): void
    {
        Store::factory()->create([
            'name' => 'Mega Tienda Centro',
            'slug' => 'mega-tienda-centro',
            'email' => 'centro@megatienda.test',
        ]);

        $this->getJson('/api/v1/stores/mega-tienda-centro')
            ->assertOk()
            ->assertJsonPath('data.slug', 'mega-tienda-centro')
            ->assertJsonPath('data.email', 'centro@megatienda.test');
    }

    public function test_hides_inactive_stores(): void
    {
        Store::factory()->inactive()->create(['name' => 'Tienda Oculta', 'slug' => 'oculta']);

        $this->getJson('/api/v1/stores/oculta')->assertNotFound();
    }

    public function test_returns_404_for_unknown_store(): void
    {
        $this->getJson('/api/v1/stores/does-not-exist')->assertNotFound();
    }
}
