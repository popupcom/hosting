<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CostLineItemType;
use App\Enums\MocoSyncStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCostLineItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'line_type' => ['sometimes', Rule::enum(CostLineItemType::class)],
            'billable_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'billable_id' => ['sometimes', 'nullable', 'integer'],
            'cost_price' => ['sometimes', 'nullable', 'numeric'],
            'selling_price' => ['sometimes', 'nullable', 'numeric'],
            'billing_interval' => ['sometimes', 'nullable', 'string', 'max:32'],
            'moco_sync_status' => ['sometimes', Rule::enum(MocoSyncStatus::class)],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
