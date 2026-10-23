<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Catalog\ProductImage;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::all();

        $rootCategories = Category::factory()->count(5)->create();

        $rootCategories->each(function (Category $root) use ($stores): void {
            if ($stores->isNotEmpty() && fake()->boolean(80)) {
                $root->update(['store_id' => $stores->random()->id]);
            }

            Category::factory()->count(2)->create([
                'parent_id' => $root->id,
                'store_id' => $root->store_id,
            ]);
        });

        $subcategories = Category::whereNotNull('parent_id')->get();

        Product::factory()
            ->count(30)
            ->recycle($subcategories)
            ->recycle($stores)
            ->create();

        Product::all()->each(function (Product $product) {
            ProductImage::factory()->main()->for($product)->create();
            ProductImage::factory()->count(2)->for($product)->create();
        });
    }
}
