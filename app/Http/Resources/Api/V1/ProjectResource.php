<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'name' => $this->name,
            'url' => $this->url,
            'wordpress_version' => $this->wordpress_version,
            'php_version' => $this->php_version,
            'managewp_site_id' => $this->managewp_site_id,
            'moco_project_id' => $this->moco_project_id,
            'status' => $this->status?->value,
            'maintenance_contract' => $this->maintenance_contract,
            'client' => $this->whenLoaded('client', fn () => new ClientResource($this->client)),
            'integration_sync_states' => IntegrationSyncStateResource::collection($this->whenLoaded('integrationSyncStates')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
