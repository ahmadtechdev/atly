<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('start_date')) {
            $this->merge(['start_date' => now()->toDateString()]);
        }

        if ($this->input('project_id') === '') {
            $this->merge(['project_id' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('create', Task::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'start_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'project_id' => [
                'nullable',
                Rule::exists('projects', 'id')->where(fn ($q) => $q->where('user_id', $this->user()->id)),
            ],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip'],
        ];
    }
}
