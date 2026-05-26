<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlueprintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('workspace_id') === '') {
            $this->merge(['workspace_id' => null]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'project.name' => ['required', 'string', 'max:200'],
            'project.description' => ['nullable', 'string', 'max:5000'],
            'project.color' => ['nullable', 'string', 'max:32'],
            'workspace_id' => [
                'nullable',
                Rule::exists('workspaces', 'id')->where(fn ($q) => $q->where('user_id', $this->user()->id)),
            ],
            'tasks' => ['required', 'array', 'min:1', 'max:100'],
            'tasks.*.title' => ['required', 'string', 'max:200'],
            'tasks.*.description' => ['nullable', 'string', 'max:5000'],
            'tasks.*.priority' => ['required', Rule::enum(TaskPriority::class)],
            'tasks.*.start_date' => ['required', 'date'],
            'tasks.*.due_date' => ['required', 'date', 'after_or_equal:tasks.*.start_date'],
        ];
    }
}
