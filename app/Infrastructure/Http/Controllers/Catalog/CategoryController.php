<?php

namespace App\Infrastructure\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Resources\Catalog\CategoryResource;
use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'store' => ['nullable', 'string', 'max:170'],
        ]);

        $categories = Category::active()
            ->whereNull('parent_id')
            ->with([
                'store',
                'children' => fn ($query) => $query->active()->orderBy('name'),
            ])
            ->when($validated['store'] ?? null, fn ($query) => $query->where(
                fn ($group) => $group
                    ->whereHas('store', fn ($store) => $store->where('slug', $validated['store']))
                    ->orWhereNull('store_id')
            ))
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category): CategoryResource
    {
        abort_unless($category->is_active, 404);

        $category->load([
            'store',
            'children' => fn ($query) => $query->active()->orderBy('name'),
        ]);

        return new CategoryResource($category);
    }
}
