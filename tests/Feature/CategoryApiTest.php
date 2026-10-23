<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_root_categories_with_active_children(): void
    {
        $electronics = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);
        Category::factory()->create(['parent_id' => $electronics->id, 'name' => 'Phones', 'slug' => 'phones']);
        Category::factory()->inactive()->create(['parent_id' => $electronics->id, 'name' => 'Hidden', 'slug' => 'hidden']);
        Category::factory()->create(['name' => 'Toys', 'slug' => 'toys']);
        Category::factory()->inactive()->create(['name' => 'Hidden Root', 'slug' => 'hidden-root']);

        $response = $this->getJson('/api/v1/categories')->assertOk();

        $this->assertEqualsCanonicalizing(
            ['electronics', 'toys'],
            collect($response->json('data'))->pluck('slug')->all()
        );

        $electronicsData = collect($response->json('data'))->firstWhere('slug', 'electronics');

        $this->assertSame(['phones'], collect($electronicsData['children'])->pluck('slug')->all());
    }

    public function test_filters_categories_by_store_including_global_ones(): void
    {
        $centro = Store::factory()->create(['name' => 'Mega Tienda Centro', 'slug' => 'mega-tienda-centro']);
        $norte = Store::factory()->create(['name' => 'Mega Tienda Norte', 'slug' => 'mega-tienda-norte']);

        Category::factory()->forStore($centro)->create(['name' => 'Centro Cat', 'slug' => 'centro-cat']);
        Category::factory()->forStore($norte)->create(['name' => 'Norte Cat', 'slug' => 'norte-cat']);
        Category::factory()->create(['name' => 'Global Cat', 'slug' => 'global-cat']);

        $response = $this->getJson('/api/v1/categories?store=mega-tienda-centro')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertEqualsCanonicalizing(
            ['centro-cat', 'global-cat'],
            collect($response->json('data'))->pluck('slug')->all()
        );
    }

    public function test_shows_a_category_by_slug(): void
    {
        $root = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);
        Category::factory()->create(['parent_id' => $root->id, 'name' => 'Phones', 'slug' => 'phones']);

        $this->getJson('/api/v1/categories/electronics')
            ->assertOk()
            ->assertJsonPath('data.slug', 'electronics')
            ->assertJsonPath('data.children.0.slug', 'phones');
    }

    public function test_hides_inactive_categories(): void
    {
        Category::factory()->inactive()->create(['name' => 'Hidden', 'slug' => 'hidden']);

        $this->getJson('/api/v1/categories/hidden')->assertNotFound();
    }

    public function test_returns_404_for_unknown_category(): void
    {
        $this->getJson('/api/v1/categories/does-not-exist')->assertNotFound();
    }
}
