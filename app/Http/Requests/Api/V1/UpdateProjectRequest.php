<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'client_id' => ['sometimes', 'integer', 'exists:clients,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'string', 'max:2048'],
            'wordpress_version' => ['sometimes', 'nullable', 'string', 'max:64'],
            'php_version' => ['sometimes', 'nullable', 'string', 'max:64'],
            'managewp_site_id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'lastpass_reference' => ['sometimes', 'nullable', 'string', 'max:512'],
            'moco_project_id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'status' => ['sometimes', 'string', 'max:32'],
            'maintenance_contract' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
