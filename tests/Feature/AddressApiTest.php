<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Store\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/v1/addresses')->assertUnauthorized();
    }

    public function test_lists_only_the_authenticated_users_addresses(): void
    {
        $user = User::factory()->create();
        Address::factory()->for($user)->count(2)->create();
        Address::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/addresses')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_creates_an_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/addresses', $this->addressPayload())
            ->assertCreated()
            ->assertJsonPath('data.recipient_name', 'Juan Perez')
            ->assertJsonPath('data.line1', 'Av. Reforma 100')
            ->assertJsonPath('data.is_default', false);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'city' => 'Ciudad de México',
        ]);
    }

    public function test_creating_a_default_address_unsets_previous_defaults(): void
    {
        $user = User::factory()->create();
        $first = Address::factory()->for($user)->default()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/addresses', $this->addressPayload(['is_default' => true]))
            ->assertCreated();

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($user->addresses()->where('is_default', true)->count() === 1);
    }

    public function test_address_validation_requires_complete_payload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/addresses', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'recipient_name', 'phone', 'line1', 'city', 'state', 'postal_code', 'country',
            ]);
    }

    public function test_updates_and_deletes_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create(['city' => 'Monterrey']);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/addresses/{$address->id}", $this->addressPayload())
            ->assertOk()
            ->assertJsonPath('data.city', 'Ciudad de México');

        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'city' => 'Ciudad de México']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/addresses/{$address->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_cannot_access_another_users_address(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $address = Address::factory()->for($owner)->create();

        $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/v1/addresses/{$address->id}")
            ->assertNotFound();

        $this->actingAs($intruder, 'sanctum')
            ->putJson("/api/v1/addresses/{$address->id}", $this->addressPayload())
            ->assertNotFound();

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/addresses/{$address->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
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
            'line2' => 'Dept. 5',
            'city' => 'Ciudad de México',
            'state' => 'CDMX',
            'postal_code' => '06600',
            'country' => 'MX',
        ], $overrides);
    }
}
