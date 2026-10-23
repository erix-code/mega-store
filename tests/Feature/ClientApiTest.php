<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Sales\Order;
use App\Infrastructure\Persistence\Eloquent\Store\Client;
use App\Infrastructure\Persistence\Eloquent\Store\ClientAddress;
use App\Infrastructure\Persistence\Eloquent\Store\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/clients')->assertUnauthorized();
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/clients')
            ->assertForbidden();
    }

    public function test_admin_users_can_list_and_search_clients(): void
    {
        $admin = User::factory()->admin()->create();
        Client::factory()->create(['name' => 'Juan Perez', 'email' => 'juan@example.com']);
        Client::factory()->create(['name' => 'Maria Lopez', 'email' => 'maria@example.com']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/clients')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'name', 'email', 'phone', 'orders_count']],
                'meta',
            ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/clients?q=juan')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Juan Perez');
    }

    public function test_admin_users_can_filter_clients_by_store(): void
    {
        $admin = User::factory()->admin()->create();
        $centro = Store::factory()->create(['name' => 'Mega Tienda Centro', 'slug' => 'mega-tienda-centro']);
        Store::factory()->create(['name' => 'Mega Tienda Norte', 'slug' => 'mega-tienda-norte']);

        Client::factory()->forStore($centro)->create(['name' => 'Cliente Centro']);
        Client::factory()->create(['name' => 'Cliente Global']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/clients?store=mega-tienda-centro')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Cliente Centro')
            ->assertJsonPath('data.0.store.slug', 'mega-tienda-centro');
    }

    public function test_admin_users_can_create_a_client(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/clients', [
                'name' => 'Cliente Nuevo',
                'email' => 'nuevo@example.com',
                'phone' => '5512345678',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Cliente Nuevo');

        $this->assertDatabaseHas('clients', ['email' => 'nuevo@example.com']);
    }

    public function test_client_validation_rejects_duplicated_email(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = Client::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/clients', [
                'name' => 'Otro Cliente',
                'email' => $existing->email,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_users_can_show_and_update_a_client(): void
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->has(ClientAddress::factory()->count(2), 'addresses')->create();

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/clients/{$client->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $client->id)
            ->assertJsonCount(2, 'data.addresses')
            ->assertJsonPath('data.orders_count', 0);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/clients/{$client->id}", [
                'name' => 'Nombre Actualizado',
                'email' => $client->email,
                'phone' => '5587654321',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nombre Actualizado')
            ->assertJsonPath('data.phone', '5587654321');
    }

    public function test_cannot_delete_a_client_with_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();
        Order::factory()->forClient($client)->create();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/clients/{$client->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['client']);

        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    public function test_admin_users_can_delete_a_client_without_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();
        ClientAddress::factory()->for($client)->create();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/clients/{$client->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
        $this->assertDatabaseCount('client_addresses', 0);
    }

    public function test_admin_users_can_manage_client_addresses(): void
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/clients/{$client->id}/addresses", $this->addressPayload(['is_default' => true]))
            ->assertCreated()
            ->assertJsonPath('data.city', 'Ciudad de México');

        $addressId = $client->addresses()->first()->id;

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/clients/{$client->id}/addresses", $this->addressPayload())
            ->assertCreated();

        $this->assertSame(1, $client->addresses()->where('is_default', true)->count());

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/clients/{$client->id}/addresses/{$addressId}", $this->addressPayload(['city' => 'Monterrey']))
            ->assertOk()
            ->assertJsonPath('data.city', 'Monterrey');

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/clients/{$client->id}/addresses/{$addressId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('client_addresses', ['id' => $addressId]);
    }

    public function test_cannot_access_an_address_from_another_client(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = Client::factory()->create();
        $other = Client::factory()->create();
        $address = ClientAddress::factory()->for($owner)->create();

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/clients/{$other->id}/addresses/{$address->id}")
            ->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'recipient_name' => 'Juan Perez',
            'phone' => '5512345678',
            'line1' => 'Av. Reforma 100',
            'city' => 'Ciudad de México',
            'state' => 'CDMX',
            'postal_code' => '06600',
            'country' => 'MX',
        ], $overrides);
    }
}
