<?php

namespace App\Infrastructure\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Resources\Store\StoreResource;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return StoreResource::collection(
            Store::active()->with('configuration')->orderBy('name')->get()
        );
    }

    public function show(Store $store): StoreResource
    {
        abort_unless($store->is_active, 404);

        return new StoreResource($store->load('configuration'));
    }
}
