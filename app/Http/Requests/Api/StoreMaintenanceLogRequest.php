<?php

namespace App\Http\Requests\Api;

use App\Enums\MaintenanceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceLogRequest extends FormRequest
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
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'maintenance_type' => ['required', Rule::enum(MaintenanceType::class)],
            'performed_by' => ['required', 'string', 'max:255'],
            'performed_on' => ['required', 'date'],
            'result' => ['required', 'string'],
            'has_errors' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'managewp_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
