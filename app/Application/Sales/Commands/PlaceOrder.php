<?php

namespace App\Application\Sales\Commands;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Store\Address;
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
        return DB::transaction(function () use ($user, $data): Order {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'total' => 0,
                'customer_name' => $data['customer_name'] ?? $user->name,
                'customer_email' => $data['customer_email'] ?? $user->email,
                'shipping_address' => isset($data['address_id'])
                    ? $this->resolveShippingAddress($user, (int) $data['address_id'])
                    : $this->normalizeShippingAddress($data['shipping_address']),
            ]);

            $total = 0.0;

            foreach ($data['items'] as $item) {
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

            return $order->load('items');
        });
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
