<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class N8nIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_api_accepts_valid_sanctum_token_with_integrations_ability(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['integrations']);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_api_rejects_token_without_required_ability(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['other']);

        $this->getJson('/api/v1/me')->assertForbidden();
    }

    public function test_webhook_rejects_missing_authentication(): void
    {
        config([
            'integrations.webhook_bearer_tokens.moco' => 'secret-bearer',
            'integrations.webhook_hmac_secrets.moco' => 'hmac-secret',
        ]);

        $this->postJson('/api/v1/webhooks/moco', ['id' => '123'])
            ->assertUnauthorized();
    }

    public function test_webhook_accepts_valid_bearer_token(): void
    {
        config([
            'integrations.webhook_bearer_tokens.moco' => 'secret-bearer',
            'integrations.webhook_hmac_secrets.moco' => null,
        ]);

        $this->postJson('/api/v1/webhooks/moco', ['id' => '999'], [
            'Authorization' => 'Bearer secret-bearer',
        ])->assertOk()->assertJsonPath('duplicate', false);
    }

    public function test_webhook_moco_marks_matching_client_sync_pending(): void
    {
        config(['integrations.webhook_bearer_tokens.moco' => 'secret-bearer']);

        $client = Client::forceCreate([
            'name' => 'Test Client',
            'slug' => 'test-client-'.uniqid(),
            'company' => 'Test GmbH',
            'moco_customer_id' => 'moco-42',
        ]);

        $this->postJson('/api/v1/webhooks/moco', ['customer_id' => 'moco-42'], [
            'Authorization' => 'Bearer secret-bearer',
        ])->assertOk();

        $this->assertDatabaseHas('integration_sync_states', [
            'syncable_type' => Client::class,
            'syncable_id' => (string) $client->id,
            'provider' => 'moco',
            'status' => 'pending',
        ]);
    }
}
