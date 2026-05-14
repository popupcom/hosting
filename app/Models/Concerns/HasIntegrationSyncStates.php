<?php

namespace App\Models\Concerns;

use App\Enums\IntegrationProvider;
use App\Models\IntegrationSyncState;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasIntegrationSyncStates
{
    public function integrationSyncStates(): MorphMany
    {
        return $this->morphMany(IntegrationSyncState::class, 'syncable');
    }

    public function integrationSyncStateFor(IntegrationProvider $provider): ?IntegrationSyncState
    {
        return $this->integrationSyncStates()
            ->where('provider', $provider)
            ->first();
    }
}
