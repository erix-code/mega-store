<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Catalog\ProductImage;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rootCategories = Category::factory()->count(5)->create();

        $rootCategories->each(fn (Category $root) => Category::factory()->count(2)->create([
            'parent_id' => $root->id,
        ]));

        $subcategories = Category::whereNotNull('parent_id')->get();

        Product::factory()
            ->count(30)
            ->recycle($subcategories)
            ->create();

        Product::all()->each(function (Product $product) {
            ProductImage::factory()->main()->for($product)->create();
            ProductImage::factory()->count(2)->for($product)->create();
        });
    }
}
