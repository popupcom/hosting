<?php

namespace App\Http\Resources\Api\V1;

use App\Models\CostLineItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CostLineItem
 */
class CostLineItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'project_id' => $this->project_id,
            'line_type' => $this->line_type?->value,
            'billable_type' => $this->billable_type,
            'billable_id' => $this->billable_id,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'billing_interval' => $this->billing_interval,
            'moco_sync_status' => $this->moco_sync_status?->value,
            'is_active' => $this->is_active,
            'client' => $this->whenLoaded('client', fn () => new ClientResource($this->client)),
            'project' => $this->whenLoaded('project', fn () => new ProjectResource($this->project)),
            'integration_sync_states' => IntegrationSyncStateResource::collection($this->whenLoaded('integrationSyncStates')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
