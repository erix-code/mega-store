<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Store\Store;
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
