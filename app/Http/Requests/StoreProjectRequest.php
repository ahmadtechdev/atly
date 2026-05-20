<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('workspace_id') === '') {
            $this->merge(['workspace_id' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'color' => ['nullable', 'string', 'max:32'],
            'workspace_id' => [
                'nullable',
                Rule::exists('workspaces', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
        ];
    }
}
