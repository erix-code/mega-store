<?php

namespace App\Infrastructure\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Store\SaveClientRequest;
use App\Infrastructure\Http\Resources\Store\ClientResource;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'store' => ['nullable', 'string', 'max:170'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $clients = Client::query()
            ->with('store')
            ->withCount('orders')
            ->when($validated['q'] ?? null, fn ($query) => $query->where(fn ($where) => $where
                ->where('name', 'like', '%'.$validated['q'].'%')
                ->orWhere('email', 'like', '%'.$validated['q'].'%')))
            ->when($validated['store'] ?? null, fn ($query) => $query->whereHas(
                'store',
                fn ($store) => $store->where('slug', $validated['store'])
            ))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($validated['per_page'] ?? 15);

        return ClientResource::collection($clients);
    }

    public function store(SaveClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return (new ClientResource($client->fresh()))->response()->setStatusCode(201);
    }

    public function show(Client $client): ClientResource
    {
        return new ClientResource(
            $client->load(['store', 'addresses' => fn ($query) => $query->orderByDesc('is_default')->orderBy('id')])
                ->loadCount('orders')
        );
    }

    public function update(SaveClientRequest $request, Client $client): ClientResource
    {
        $client->update($request->validated());

        return new ClientResource($client->fresh());
    }

    public function destroy(Client $client): JsonResponse
    {
        if ($client->orders()->exists()) {
            throw ValidationException::withMessages([
                'client' => ['The client has orders and cannot be deleted.'],
            ]);
        }

        $client->delete();

        return response()->json(null, 204);
    }
}
