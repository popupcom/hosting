<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Enums\ServiceCatalogBillingInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectServiceRequest extends FormRequest
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
            'custom_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'custom_cost_price' => ['sometimes', 'nullable', 'numeric'],
            'custom_sales_price' => ['sometimes', 'nullable', 'numeric'],
            'custom_quantity' => ['sometimes', 'nullable', 'numeric'],
            'custom_billing_interval' => ['sometimes', 'nullable', Rule::enum(ServiceCatalogBillingInterval::class)],
            'quantity' => ['sometimes', 'nullable', 'numeric'],
            'status' => ['sometimes', Rule::enum(ProjectServiceStatus::class)],
            'moco_sync_status' => ['sometimes', Rule::enum(ProjectServiceMocoSyncStatus::class)],
            'moco_invoice_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'billing_group_id' => ['sometimes', 'nullable', 'integer', 'exists:billing_groups,id'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
