<?php

namespace App\Infrastructure\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Resources\Catalog\ProductResource;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:170'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'in:newest,price_asc,price_desc,name'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        [$sortColumn, $sortDirection] = match ($validated['sort'] ?? 'newest') {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name' => ['name', 'asc'],
            default => ['created_at', 'desc'],
        };

        $products = Product::active()
            ->with('category')
            ->when($validated['category'] ?? null, fn ($query) => $query->whereHas(
                'category',
                fn ($category) => $category->where('slug', $validated['category'])
            ))
            ->when($validated['q'] ?? null, fn ($query) => $query->where(fn ($where) => $where
                ->where('name', 'like', '%'.$validated['q'].'%')
                ->orWhere('sku', 'like', '%'.$validated['q'].'%')))
            ->when($validated['min_price'] ?? null, fn ($query) => $query->where('price', '>=', $validated['min_price']))
            ->when($validated['max_price'] ?? null, fn ($query) => $query->where('price', '<=', $validated['max_price']))
            ->when($request->boolean('in_stock'), fn ($query) => $query->inStock())
            ->orderBy($sortColumn, $sortDirection)
            ->orderBy('id', $sortDirection)
            ->paginate($validated['per_page'] ?? 15);

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        return new ProductResource(
            $product->load([
                'category',
                'images' => fn ($query) => $query->orderByDesc('is_main')->orderBy('id'),
            ])
        );
    }
}
