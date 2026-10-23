<?php

namespace Tests\Feature;

use App\Domain\Sales\ValueObjects\OrderStatus;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Stores\Pages\EditStore;
use App\Infrastructure\Persistence\Eloquent\Catalog\Category;
use App\Infrastructure\Persistence\Eloquent\Catalog\Product;
use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use App\Infrastructure\Persistence\Eloquent\Store\StoreConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_can_update_store_configuration(): void
    {
        $admin = User::factory()->admin()->create();
        $store = Store::factory()->create();

        Livewire::actingAs($admin)
            ->test(EditStore::class, ['record' => $store->getRouteKey()])
            ->fillForm([
                'configuration.main_color' => '#FF0000',
                'configuration.second_color' => '#00FF00',
                'configuration.third_color' => '#0000FF',
                'configuration.address' => 'Calle Test 123',
                'configuration.latitude' => '19.4326',
                'configuration.longitude' => '-99.1332',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('store_configurations', [
            'store_id' => $store->id,
            'main_color' => '#FF0000',
            'second_color' => '#00FF00',
            'third_color' => '#0000FF',
            'address' => 'Calle Test 123',
            'latitude' => 19.4326000,
            'longitude' => -99.1332000,
        ]);
    }

    public function test_store_edit_page_shows_the_map_preview(): void
    {
        $admin = User::factory()->admin()->create();
        $store = Store::factory()->create();
        StoreConfiguration::factory()
            ->for($store, 'store')
            ->create(['latitude' => 19.4326, 'longitude' => -99.1332]);

        Livewire::actingAs($admin)
            ->test(EditStore::class, ['record' => $store->getRouteKey()])
            ->assertOk()
            ->assertSee('maps.google.com');
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_non_admin_users_cannot_access_the_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_users_can_view_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_users_can_list_products(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get('/admin/products')
            ->assertOk();
    }

    public function test_orders_can_be_created_from_the_panel(): void
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();
        $product = Product::factory()->create(['name' => 'Producto Panel', 'price' => 50, 'stock' => 10]);

        Livewire::actingAs($admin)
            ->test(CreateOrder::class)
            ->fillForm([
                'client_id' => $client->id,
                'status' => 'pending',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
                'line1' => 'Calle Panel 123',
                'city' => 'La Paz',
                'state' => 'La Paz',
                'postal_code' => '0000',
                'country' => 'BO',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'status' => 'pending',
            'total' => 100.00,
        ]);

        $this->assertSame(8, $product->fresh()->stock);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'subtotal' => 100.00,
        ]);
    }

    public function test_admin_users_can_create_products(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $store = Store::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreateProduct::class)
            ->fillForm([
                'sku' => 'SKU-99999',
                'name' => 'Producto de prueba',
                'price' => 199.99,
                'stock' => 10,
                'store_id' => $store->id,
                'category_id' => $category->id,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-99999',
            'name' => 'Producto de prueba',
            'slug' => 'producto-de-prueba',
            'price' => 199.99,
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_users_can_change_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        Livewire::actingAs($admin)
            ->test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm(['status' => OrderStatus::Paid->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Paid->value,
        ]);
    }
}
