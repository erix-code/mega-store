<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Catalog\ProductImage;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_products_paginated(): void
    {
        Product::factory()->count(3)->create();
        Product::factory()->inactive()->create();

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'sku', 'name', 'slug', 'price', 'stock', 'is_active', 'category',
                ]],
                'meta' => ['current_page', 'total', 'per_page', 'last_page'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);
    }

    public function test_filters_products_by_store(): void
    {
        $store = Store::factory()->create(['name' => 'Mega Tienda Norte', 'slug' => 'mega-tienda-norte']);
        Product::factory()->for($store, 'store')->create(['name' => 'Producto Norte']);
        Product::factory()->create(['name' => 'Producto Otra Tienda']);

        $this->getJson('/api/v1/products?store=mega-tienda-norte')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Producto Norte')
            ->assertJsonPath('data.0.store.slug', 'mega-tienda-norte');
    }

    public function test_filters_products_by_category(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics', 'slug' => 'electronics']);
        Product::factory()->for($category, 'category')->create(['name' => 'Smartphone X1']);
        Product::factory()->create(['name' => 'Wooden Table']);

        $this->getJson('/api/v1/products?category=electronics')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Smartphone X1')
            ->assertJsonPath('data.0.category.slug', 'electronics');
    }

    public function test_filters_products_by_search(): void
    {
        Product::factory()->create(['name' => 'Smartphone X1', 'sku' => 'SKU-11111']);
        Product::factory()->create(['name' => 'Wooden Table', 'sku' => 'SKU-22222']);

        $this->getJson('/api/v1/products?q=smartphone')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Smartphone X1');

        $this->getJson('/api/v1/products?q=SKU-22222')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Wooden Table');
    }

    public function test_filters_products_by_price_range(): void
    {
        Product::factory()->create(['name' => 'Cheap Product', 'price' => 50]);
        Product::factory()->create(['name' => 'Expensive Product', 'price' => 500]);

        $this->getJson('/api/v1/products?min_price=100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Expensive Product');

        $this->getJson('/api/v1/products?max_price=100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Cheap Product');
    }

    public function test_sorts_products_by_price_ascending(): void
    {
        Product::factory()->create(['name' => 'Expensive', 'price' => 500]);
        Product::factory()->create(['name' => 'Cheap', 'price' => 50]);

        $this->getJson('/api/v1/products?sort=price_asc')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Cheap')
            ->assertJsonPath('data.1.name', 'Expensive');
    }

    public function test_shows_a_product_by_slug_with_images(): void
    {
        $product = Product::factory()->create(['name' => 'Smartphone X1', 'slug' => 'smartphone-x1']);
        ProductImage::factory()->count(2)->for($product)->create();
        ProductImage::factory()->main()->for($product)->create();

        $this->getJson('/api/v1/products/smartphone-x1')
            ->assertOk()
            ->assertJsonPath('data.slug', 'smartphone-x1')
            ->assertJsonCount(3, 'data.images')
            ->assertJsonPath('data.images.0.is_main', true);
    }

    public function test_hides_inactive_products(): void
    {
        Product::factory()->inactive()->create(['name' => 'Hidden', 'slug' => 'hidden']);

        $this->getJson('/api/v1/products/hidden')->assertNotFound();
    }
}
