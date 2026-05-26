<?php

namespace App\Http\Requests;

use App\Services\AI\AIModelRegistry;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateBlueprintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('assignment_type') === null) {
            $this->merge(['assignment_type' => 'individual']);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'model' => ['required', 'string', $this->availableModelRule()],
            'description' => ['nullable', 'string', 'max:8000', 'required_without:document'],
            'document' => [
                'nullable',
                'file',
                'max:8192',
                'mimes:txt,md,pdf,docx',
                'required_without:description',
            ],
            'assignment_type' => ['required', Rule::in(['individual', 'team'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'members' => ['nullable', 'array', 'max:20', 'required_if:assignment_type,team'],
            'members.*.name' => ['required_with:members', 'string', 'max:120'],
            'members.*.skill' => ['nullable', 'string', 'max:120'],
            'members.*.split' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required_without' => 'Provide either a project description or upload a document.',
            'document.required_without' => 'Provide either a project description or upload a document.',
            'model.required' => 'Select an AI model to generate the plan.',
        ];
    }

    private function availableModelRule(): ValidationRule
    {
        return new class implements ValidationRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                if (! is_string($value) || trim($value) === '') {
                    $fail('Select an AI model.');

                    return;
                }

                if (! app(AIModelRegistry::class)->isAvailable($value)) {
                    $fail('That AI model is not available right now.');
                }
            }
        };
    }
}
