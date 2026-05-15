<?php

namespace Tests\Feature\Api;

use App\Enums\MaintenanceType;
use App\Models\Client;
use App\Models\Project;
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

    public function test_get_api_projects_requires_authentication(): void
    {
        $this->getJson('/api/projects')->assertUnauthorized();
    }

    public function test_get_api_projects_returns_ok_with_integrations_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['integrations']);

        $client = Client::forceCreate([
            'name' => 'API Client',
            'slug' => 'api-client-'.uniqid(),
            'company' => 'Co',
        ]);

        Project::forceCreate([
            'client_id' => $client->id,
            'name' => 'Example Site',
            'url' => 'https://example.test',
        ]);

        $this->getJson('/api/projects')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_post_api_maintenance_logs_creates_record(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['integrations']);

        $client = Client::forceCreate([
            'name' => 'Log Client',
            'slug' => 'log-client-'.uniqid(),
            'company' => 'Co',
        ]);

        $project = Project::forceCreate([
            'client_id' => $client->id,
            'name' => 'Log Project',
            'url' => 'https://log.example.test',
        ]);

        $payload = [
            'project_id' => $project->id,
            'maintenance_type' => MaintenanceType::WordPressCore->value,
            'performed_by' => 'n8n-test',
            'performed_on' => '2026-05-14',
            'result' => 'OK',
            'has_errors' => false,
        ];

        $this->postJson('/api/maintenance-logs', $payload)
            ->assertCreated()
            ->assertJsonPath('data.result', 'OK');

        $this->assertDatabaseHas('maintenance_histories', [
            'project_id' => $project->id,
            'performed_by' => 'n8n-test',
        ]);
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
