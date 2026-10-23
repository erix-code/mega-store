<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Sales\OrderItem;
use App\Infrastructure\Persistence\Eloquent\Store\Address;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/orders')->assertUnauthorized();
        $this->postJson('/api/v1/orders', [])->assertUnauthorized();
    }

    public function test_places_an_order_with_inline_shipping_address(): void
    {
        $user = User::factory()->create(['name' => 'Juan Perez', 'email' => 'juan@example.com']);
        $product = Product::factory()->create(['name' => 'Smartphone X1', 'price' => 100.50, 'stock' => 10]);
        $other = Product::factory()->create(['name' => 'Case', 'price' => 25, 'stock' => 5]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                    ['product_id' => $other->id, 'quantity' => 1],
                ],
                'shipping_address' => [
                    'line1' => 'Av. Reforma 100',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'postal_code' => '06600',
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', 226)
            ->assertJsonPath('data.client.name', 'Juan Perez')
            ->assertJsonPath('data.client.email', 'juan@example.com')
            ->assertJsonPath('data.shipping_address.line1', 'Av. Reforma 100')
            ->assertJsonCount(2, 'data.items');

        $client = Client::where('email', 'juan@example.com')->first();

        $this->assertNotNull($client);

        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'status' => 'pending',
            'total' => 226.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('data.id'),
            'product_id' => $product->id,
            'quantity' => 2,
            'subtotal' => 201.00,
        ]);

        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame(4, $other->fresh()->stock);

        $this->assertDatabaseHas('client_addresses', [
            'client_id' => $client->id,
            'line1' => 'Av. Reforma 100',
            'is_default' => true,
        ]);
    }

    public function test_places_an_order_using_a_saved_address(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10, 'stock' => 5]);
        $address = Address::factory()->for($user)->create([
            'recipient_name' => 'Maria Lopez',
            'line1' => 'Calle 5 #20',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'address_id' => $address->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.total', 10)
            ->assertJsonPath('data.shipping_address.recipient_name', 'Maria Lopez')
            ->assertJsonPath('data.shipping_address.line1', 'Calle 5 #20');
    }

    public function test_rejects_an_order_without_shipping_information(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['shipping_address']);
    }

    public function test_rejects_an_order_with_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Smartphone X1', 'stock' => 1]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => $product->id, 'quantity' => 5]],
                'shipping_address' => $this->shippingAddress(),
            ]);

        $response->assertUnprocessable();
        $this->assertSame(
            ['Not enough stock for Smartphone X1.'],
            $response->json('errors')['items.0.quantity']
        );

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_rejects_an_order_with_unknown_product(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [
                'items' => [['product_id' => 999999, 'quantity' => 1]],
                'shipping_address' => $this->shippingAddress(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_rejects_an_empty_payload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items', 'shipping_address']);
    }

    public function test_reuses_the_same_client_for_repeated_emails(): void
    {
        $user = User::factory()->create(['name' => 'Juan Perez', 'email' => 'juan@example.com']);
        $product = Product::factory()->create(['price' => 10, 'stock' => 20]);

        foreach ([1, 2] as $i) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/orders', [
                    'items' => [['product_id' => $product->id, 'quantity' => 1]],
                    'shipping_address' => $this->shippingAddress(),
                ])
                ->assertCreated();
        }

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseCount('client_addresses', 1);
    }

    public function test_lists_only_the_authenticated_users_orders(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $userClient = Client::factory()->forUser($user)->create();
        $otherClient = Client::factory()->forUser($other)->create();

        $order = Order::factory()->forClient($userClient)->create();
        OrderItem::factory()->forProduct($this->makeProduct())->for($order)->create();
        Order::factory()->forClient($userClient)->create();
        Order::factory()->forClient($otherClient)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $firstOrder = collect($response->json('data'))->firstWhere('id', $order->id);
        $this->assertNotNull($firstOrder);
        $this->assertNotEmpty($firstOrder['items']);
    }

    public function test_shows_own_order(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->forUser($user)->create();
        $order = Order::factory()->forClient($client)->create();
        OrderItem::factory()->count(2)->sequence(
            ['product_id' => Product::factory(), 'product_name' => 'Item A', 'unit_price' => 10, 'quantity' => 1, 'subtotal' => 10],
            ['product_id' => Product::factory(), 'product_name' => 'Item B', 'unit_price' => 20, 'quantity' => 2, 'subtotal' => 40],
        )->for($order)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.client.email', $client->email)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $ownerClient = Client::factory()->forUser($owner)->create();
        $order = Order::factory()->forClient($ownerClient)->create();

        $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/v1/orders/{$order->id}")
            ->assertNotFound();
    }

    private function makeProduct(): Product
    {
        return Product::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function shippingAddress(): array
    {
        return [
            'line1' => 'Av. Reforma 100',
            'city' => 'Ciudad de México',
            'state' => 'CDMX',
            'postal_code' => '06600',
        ];
    }
}
