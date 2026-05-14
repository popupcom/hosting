<?php

namespace App\Http\Resources\Api\V1;

use App\Models\IntegrationSyncState;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IntegrationSyncState
 */
class IntegrationSyncStateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'syncable_type' => $this->syncable_type,
            'syncable_id' => $this->syncable_id,
            'provider' => $this->provider?->value,
            'status' => $this->status?->value,
            'external_id' => $this->external_id,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'last_error' => $this->last_error,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
