<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectDomainRequest extends FormRequest
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
            'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
            'domain_name' => ['sometimes', 'string', 'max:255'],
            'registrar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'hosting_provider' => ['sometimes', 'nullable', 'string', 'max:255'],
            'autodns_id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'dns_zone' => ['sometimes', 'nullable', 'string'],
            'nameservers' => ['sometimes', 'nullable', 'string'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
            'cancellation_notice_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'cost_price' => ['sometimes', 'nullable', 'numeric'],
            'selling_price' => ['sometimes', 'nullable', 'numeric'],
            'billing_interval' => ['sometimes', 'nullable', 'string', 'max:32'],
            'status' => ['sometimes', 'string', 'max:32'],
            'reminder_at' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
