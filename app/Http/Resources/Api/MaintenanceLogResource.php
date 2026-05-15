<?php

namespace App\Http\Resources\Api;

use App\Models\MaintenanceHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MaintenanceHistory
 */
class MaintenanceLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'maintenance_type' => $this->maintenance_type?->value,
            'performed_by' => $this->performed_by,
            'performed_on' => $this->performed_on?->toDateString(),
            'result' => $this->result,
            'has_errors' => $this->has_errors,
            'notes' => $this->notes,
            'managewp_reference' => $this->managewp_reference,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
