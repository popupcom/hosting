<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MocoSyncStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIntegrationSyncStateRequest extends FormRequest
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
            'status' => ['sometimes', Rule::enum(MocoSyncStatus::class)],
            'external_id' => ['sometimes', 'nullable', 'string', 'max:128'],
            'last_synced_at' => ['sometimes', 'nullable', 'date'],
            'last_error' => ['sometimes', 'nullable', 'string'],
            'meta' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
