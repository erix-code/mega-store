<?php

namespace App\Infrastructure\Http\Controllers\Sales;

use App\Application\Sales\Commands\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Sales\StoreOrderRequest;
use App\Infrastructure\Http\Resources\Sales\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private PlaceOrder $placeOrder) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->placeOrder->handle($request->user(), $request->validated());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $order): OrderResource
    {
        return new OrderResource(
            $request->user()->orders()->with('items')->findOrFail($order)
        );
    }
}
