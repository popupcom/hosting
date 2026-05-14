<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProjectDomain;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProjectDomain
 */
class ProjectDomainResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'domain_name' => $this->domain_name,
            'registrar' => $this->registrar,
            'hosting_provider' => $this->hosting_provider,
            'autodns_id' => $this->autodns_id,
            'expires_at' => $this->expires_at?->toDateString(),
            'status' => $this->status?->value,
            'project' => $this->whenLoaded('project', fn () => new ProjectResource($this->project)),
            'integration_sync_states' => IntegrationSyncStateResource::collection($this->whenLoaded('integrationSyncStates')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
