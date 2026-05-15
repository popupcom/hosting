<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\IntegrationProvider;
use App\Enums\MocoSyncStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectDomain;
use App\Models\ProjectService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIntegrationSyncStateRequest extends FormRequest
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
            'syncable_type' => ['required', 'string', Rule::in([
                Client::class,
                Project::class,
                ProjectDomain::class,
                ProjectService::class,
            ])],
            'syncable_id' => ['required', 'integer', 'min:1'],
            'provider' => ['required', Rule::enum(IntegrationProvider::class)],
            'status' => ['required', Rule::enum(MocoSyncStatus::class)],
            'external_id' => ['sometimes', 'nullable', 'string', 'max:128'],
            'last_synced_at' => ['sometimes', 'nullable', 'date'],
            'last_error' => ['sometimes', 'nullable', 'string'],
            'meta' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
