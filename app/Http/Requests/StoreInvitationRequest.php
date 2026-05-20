<?php

namespace App\Http\Requests;

use App\Enums\MembershipRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Rules\HasEmailDomainMx;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public const TYPES = [
        'task' => Task::class,
        'project' => Project::class,
        'workspace' => Workspace::class,
    ];

    public function authorize(): bool
    {
        $invitable = $this->resolveInvitable();
        $user = $this->user();

        if ($invitable === null || $user === null) {
            return false;
        }

        return method_exists($invitable, 'canManage')
            ? $invitable->canManage($user)
            : $user->id === $invitable->user_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invitable_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'invitable_id' => ['required', 'integer'],
            'email' => ['required', 'email:rfc', 'max:255', new HasEmailDomainMx],
            'message' => ['nullable', 'string', 'max:500'],
            'role' => ['nullable', Rule::in(array_map(fn (MembershipRole $r) => $r->value, MembershipRole::assignable()))],
        ];
    }

    public function resolveInvitable(): ?Model
    {
        $key = (string) $this->input('invitable_type');
        $class = self::TYPES[$key] ?? null;

        if ($class === null) {
            return null;
        }

        $id = (int) $this->input('invitable_id');

        return $class::query()->find($id);
    }
}
