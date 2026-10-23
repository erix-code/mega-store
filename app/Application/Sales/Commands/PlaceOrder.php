<?php

namespace App\Application\Sales\Commands;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Store\Address;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): Order
    {
        $client = Client::firstOrCreate(
            ['email' => $data['customer_email'] ?? $user->email],
            [
                'name' => $data['customer_name'] ?? $user->name,
                'phone' => $data['customer_phone'] ?? null,
            ],
        );

        $shippingAddress = isset($data['address_id'])
            ? $this->resolveShippingAddress($user, (int) $data['address_id'])
            : $this->normalizeShippingAddress($data['shipping_address']);

        return $this->place($client, $data['items'], $shippingAddress);
    }

    /**
     * Place an order for a client, reserving stock inside a transaction.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @param  array<string, mixed>  $shippingAddress
     */
    public function place(Client $client, array $items, array $shippingAddress): Order
    {
        return DB::transaction(function () use ($client, $items, $shippingAddress): Order {
            $order = Order::create([
                'client_id' => $client->id,
                'status' => OrderStatus::Pending,
                'total' => 0,
                'shipping_address' => $shippingAddress,
            ]);

            $total = 0.0;

            foreach ($items as $item) {
                $product = Product::query()
                    ->whereKey($item['product_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Not enough stock for {$product->name}."],
                    ]);
                }

                $subtotal = round((float) $product->price * $item['quantity'], 2);
                $total = round($total + $subtotal, 2);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            $order->update(['total' => $total]);

            $this->rememberClientAddress($client, $order->shipping_address);

            return $order->load(['items', 'client']);
        });
    }

    /**
     * Keep track of where the client lives by storing the shipping address
     * used at checkout, skipping duplicates.
     *
     * @param  array<string, mixed>  $shippingAddress
     */
    private function rememberClientAddress(Client $client, array $shippingAddress): void
    {
        $alreadyKnown = $client->addresses()
            ->where('line1', $shippingAddress['line1'] ?? '')
            ->where('postal_code', $shippingAddress['postal_code'] ?? '')
            ->exists();

        if ($alreadyKnown) {
            return;
        }

        $client->addresses()->create([
            'recipient_name' => $shippingAddress['recipient_name'] ?? $client->name,
            'phone' => $shippingAddress['phone'] ?? $client->phone,
            'line1' => $shippingAddress['line1'],
            'line2' => $shippingAddress['line2'] ?? null,
            'city' => $shippingAddress['city'] ?? null,
            'state' => $shippingAddress['state'] ?? null,
            'postal_code' => $shippingAddress['postal_code'] ?? null,
            'country' => $shippingAddress['country'] ?? 'MX',
            'is_default' => $client->addresses()->count() === 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveShippingAddress(User $user, int $addressId): array
    {
        $address = Address::query()
            ->where('user_id', $user->id)
            ->findOrFail($addressId);

        return $this->normalizeShippingAddress($address->toArray());
    }

    /**
     * @param  array<string, mixed>  $address
     * @return array<string, mixed>
     */
    private function normalizeShippingAddress(array $address): array
    {
        return [
            'recipient_name' => $address['recipient_name'] ?? null,
            'phone' => $address['phone'] ?? null,
            'line1' => $address['line1'],
            'line2' => $address['line2'] ?? null,
            'city' => $address['city'],
            'state' => $address['state'],
            'postal_code' => $address['postal_code'],
            'country' => $address['country'] ?? 'MX',
        ];
    }
}
