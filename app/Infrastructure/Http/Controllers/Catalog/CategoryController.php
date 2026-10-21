<?php

namespace App\Infrastructure\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Resources\Catalog\CategoryResource;
use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::active()
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->active()->orderBy('name')])
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category): CategoryResource
    {
        abort_unless($category->is_active, 404);

        $category->load(['children' => fn ($query) => $query->active()->orderBy('name')]);

        return new CategoryResource($category);
    }
}
