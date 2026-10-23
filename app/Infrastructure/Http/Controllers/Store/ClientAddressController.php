<?php

namespace App\Infrastructure\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Store\SaveClientAddressRequest;
use App\Infrastructure\Http\Resources\Store\ClientAddressResource;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Infrastructure\Persistence\Eloquent\Store\ClientAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientAddressController extends Controller
{
    public function index(Request $request, Client $client): AnonymousResourceCollection
    {
        $addresses = $client->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return ClientAddressResource::collection($addresses);
    }

    public function store(SaveClientAddressRequest $request, Client $client): JsonResponse
    {
        $address = $client->addresses()->create($request->validated());

        $this->unsetOtherDefaults($client, $address->id, $request->boolean('is_default'));

        return (new ClientAddressResource($address->fresh()))->response()->setStatusCode(201);
    }

    public function show(Client $client, ClientAddress $address): ClientAddressResource
    {
        abort_unless($address->client_id === $client->id, 404);

        return new ClientAddressResource($address);
    }

    public function update(SaveClientAddressRequest $request, Client $client, ClientAddress $address): ClientAddressResource
    {
        abort_unless($address->client_id === $client->id, 404);

        $address->update($request->validated());

        $this->unsetOtherDefaults($client, $address->id, $request->boolean('is_default'));

        return new ClientAddressResource($address->fresh());
    }

    public function destroy(Client $client, ClientAddress $address): JsonResponse
    {
        abort_unless($address->client_id === $client->id, 404);

        $address->delete();

        return response()->json(null, 204);
    }

    private function unsetOtherDefaults(Client $client, int $addressId, bool $isDefault): void
    {
        if (! $isDefault) {
            return;
        }

        $client->addresses()
            ->whereKeyNot($addressId)
            ->update(['is_default' => false]);
    }
}
