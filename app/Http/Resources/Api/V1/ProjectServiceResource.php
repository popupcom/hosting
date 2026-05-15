<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProjectService
 */
class ProjectServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'service_catalog_item_id' => $this->service_catalog_item_id,
            'name' => $this->effective_name,
            'cost_price' => $this->effective_cost_price,
            'sales_price' => $this->effective_sales_price,
            'quantity' => $this->effective_quantity,
            'billing_interval' => $this->effective_billing_interval?->value,
            'status' => $this->status?->value,
            'moco_sync_status' => $this->moco_sync_status?->value,
            'moco_invoice_reference' => $this->moco_invoice_reference,
            'billing_group_id' => $this->billing_group_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'project' => $this->whenLoaded('project', fn () => new ProjectResource($this->project)),
            'service_catalog_item' => $this->whenLoaded('serviceCatalogItem'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
