<?php

namespace App\Services\Integrations;

use App\Enums\IntegrationProvider;
use App\Enums\MocoSyncStatus;
use App\Models\Client;
use App\Models\IntegrationSyncState;
use App\Models\IntegrationWebhookEvent;
use App\Models\Project;
use App\Models\ProjectDomain;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class IntegrationWebhookService
{
    public function recordEvent(
        IntegrationProvider $provider,
        array $payload,
        ?string $dedupeKey,
        ?string $ipAddress,
    ): array {
        if ($dedupeKey === null || $dedupeKey === '') {
            $event = IntegrationWebhookEvent::query()->create([
                'provider' => $provider,
                'dedupe_key' => null,
                'payload' => $payload,
                'ip_address' => $ipAddress,
            ]);

            return ['event' => $event, 'is_duplicate' => false];
        }

        try {
            $event = IntegrationWebhookEvent::query()->create([
                'provider' => $provider,
                'dedupe_key' => $dedupeKey,
                'payload' => $payload,
                'ip_address' => $ipAddress,
            ]);

            return ['event' => $event, 'is_duplicate' => false];
        } catch (QueryException $e) {
            if (! str_contains(strtolower($e->getMessage()), 'unique')) {
                throw $e;
            }
            $event = IntegrationWebhookEvent::query()->where('dedupe_key', $dedupeKey)->firstOrFail();

            return ['event' => $event, 'is_duplicate' => true];
        }
    }

    public function handleInbound(IntegrationProvider $provider, array $payload): void
    {
        match ($provider) {
            IntegrationProvider::Moco => $this->handleMoco($payload),
            IntegrationProvider::ManageWp => $this->handleManageWp($payload),
            IntegrationProvider::AutoDns => $this->handleAutoDns($payload),
        };
    }

    private function handleMoco(array $payload): void
    {
        $externalId = Arr::get($payload, 'external_id')
            ?? Arr::get($payload, 'customer_id')
            ?? Arr::get($payload, 'id');

        if (is_string($externalId) || is_numeric($externalId)) {
            $externalId = (string) $externalId;
            $this->touchClientsByMocoId($externalId);
            $this->touchProjectsByMocoProjectId($externalId);
        }
    }

    private function handleManageWp(array $payload): void
    {
        $siteId = Arr::get($payload, 'site_id') ?? Arr::get($payload, 'external_id') ?? Arr::get($payload, 'id');

        if (is_string($siteId) || is_numeric($siteId)) {
            $this->touchProjectsByManageWpId((string) $siteId);
        }
    }

    private function handleAutoDns(array $payload): void
    {
        $zoneId = Arr::get($payload, 'zone_id') ?? Arr::get($payload, 'external_id') ?? Arr::get($payload, 'id');

        if (is_string($zoneId) || is_numeric($zoneId)) {
            $this->touchDomainsByAutoDnsId((string) $zoneId);
        }
    }

    private function touchClientsByMocoId(string $externalId): void
    {
        Client::query()->where('moco_customer_id', $externalId)->each(function (Client $client) use ($externalId): void {
            $this->markPending($client, IntegrationProvider::Moco, $externalId);
        });
    }

    private function touchProjectsByMocoProjectId(string $externalId): void
    {
        Project::query()->where('moco_project_id', $externalId)->each(function (Project $project) use ($externalId): void {
            $this->markPending($project, IntegrationProvider::Moco, $externalId);
        });
    }

    private function touchProjectsByManageWpId(string $externalId): void
    {
        Project::query()->where('managewp_site_id', $externalId)->each(function (Project $project) use ($externalId): void {
            $this->markPending($project, IntegrationProvider::ManageWp, $externalId);
        });
    }

    private function touchDomainsByAutoDnsId(string $externalId): void
    {
        ProjectDomain::query()->where('autodns_id', $externalId)->each(function (ProjectDomain $domain) use ($externalId): void {
            $this->markPending($domain, IntegrationProvider::AutoDns, $externalId);
        });
    }

    private function markPending(object $syncable, IntegrationProvider $provider, ?string $externalId = null): void
    {
        DB::transaction(function () use ($syncable, $provider, $externalId): void {
            IntegrationSyncState::query()->updateOrCreate(
                [
                    'syncable_type' => $syncable::class,
                    'syncable_id' => $syncable->getKey(),
                    'provider' => $provider,
                ],
                [
                    'status' => MocoSyncStatus::Pending,
                    'external_id' => $externalId,
                    'last_error' => null,
                    'meta' => ['source' => 'webhook', 'received_at' => now()->toIso8601String()],
                ],
            );
        });
    }
}
