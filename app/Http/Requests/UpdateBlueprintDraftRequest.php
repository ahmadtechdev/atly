<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlueprintDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('draft')) === true;
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
            'project.name' => ['nullable', 'string', 'max:200'],
            'project.description' => ['nullable', 'string', 'max:5000'],
            'project.color' => ['nullable', 'string', 'max:32'],
            'workspace_id' => [
                'nullable',
                Rule::exists('workspaces', 'id')->where(fn ($q) => $q->where('user_id', $this->user()->id)),
            ],

            'tasks' => ['nullable', 'array', 'max:100'],
            'tasks.*.title' => ['required_with:tasks', 'string', 'max:200'],
            'tasks.*.description' => ['nullable', 'string', 'max:5000'],
            'tasks.*.priority' => ['required_with:tasks', Rule::enum(TaskPriority::class)],
            'tasks.*.start_date' => ['required_with:tasks', 'date'],
            'tasks.*.due_date' => ['required_with:tasks', 'date', 'after_or_equal:tasks.*.start_date'],
            'tasks.*.assigned_to' => ['nullable', 'string', 'max:120'],
            'tasks.*.milestone' => ['nullable', 'string', 'max:120'],
            'tasks.*.skill_required' => ['nullable', 'string', 'max:120'],
            'tasks.*.estimated_hours' => ['nullable', 'integer', 'between:1,200'],

            'members' => ['nullable', 'array', 'max:20'],
            'members.*.id' => ['nullable', 'integer'],
            'members.*.name' => ['required_with:members', 'string', 'max:120'],
            'members.*.email' => ['nullable', 'email:rfc', 'max:191'],
            'members.*.skills' => ['nullable', 'string', 'max:500'],
            'members.*.split' => ['nullable', 'integer', 'between:0,100'],
        ];
    }
}
