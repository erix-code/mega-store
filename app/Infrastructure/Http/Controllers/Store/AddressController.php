<?php

namespace App\Infrastructure\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Store\SaveAddressRequest;
use App\Infrastructure\Http\Resources\Store\AddressResource;
use App\Infrastructure\Persistence\Eloquent\Store\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return AddressResource::collection($addresses);
    }

    public function store(SaveAddressRequest $request): JsonResponse
    {
        $address = $request->user()->addresses()->create($request->validated());

        $this->unsetOtherDefaults($request, $address->id);

        return (new AddressResource($address->fresh()))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $address): AddressResource
    {
        return new AddressResource($this->findOwnedAddress($request, $address));
    }

    public function update(SaveAddressRequest $request, int $address): AddressResource
    {
        $address = $this->findOwnedAddress($request, $address);

        $address->update($request->validated());

        $this->unsetOtherDefaults($request, $address->id);

        return new AddressResource($address->fresh());
    }

    public function destroy(Request $request, int $address): JsonResponse
    {
        $this->findOwnedAddress($request, $address)->delete();

        return response()->json(null, 204);
    }

    private function findOwnedAddress(Request $request, int $addressId): Address
    {
        return $request->user()->addresses()->findOrFail($addressId);
    }

    private function unsetOtherDefaults(Request $request, int $addressId): void
    {
        if (! $request->boolean('is_default')) {
            return;
        }

        $request->user()
            ->addresses()
            ->whereKeyNot($addressId)
            ->update(['is_default' => false]);
    }
}
