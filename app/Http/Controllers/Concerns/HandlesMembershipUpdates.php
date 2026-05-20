<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\MembershipRole;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Shared helpers used by the per-resource membership controllers.
 *
 * Permission rule (matches product spec): only the owner of the entity can
 * change roles or remove members. Admins can invite but cannot change roles.
 */
trait HandlesMembershipUpdates
{
    private function authorizeOwner(Model $entity, Request $request): void
    {
        $user = $request->user();

        if ($user === null || $entity->user_id !== $user->id) {
            abort(403, 'Only the owner can manage members.');
        }
    }

    private function applyRoleUpdate(Request $request, Model $entity, User $member): MembershipRole
    {
        $this->authorizeOwner($entity, $request);

        $data = $request->validate([
            'role' => ['required', Rule::in(array_map(
                fn (MembershipRole $r) => $r->value,
                MembershipRole::assignable(),
            ))],
        ]);

        $role = MembershipRole::tryFrom($data['role']) ?? throw ValidationException::withMessages([
            'role' => 'Unknown role.',
        ]);

        app(MembershipService::class)->updateRole($entity, $member, $role);

        return $role;
    }

    private function applyRemove(Request $request, Model $entity, User $member): void
    {
        $this->authorizeOwner($entity, $request);

        app(MembershipService::class)->remove($entity, $member);
    }

    private function jsonOrBack(Request $request, string $message, ?string $redirectRoute = null, mixed $redirectParam = null): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return $redirectRoute
            ? redirect()->route($redirectRoute, $redirectParam)->with('status', $message)
            : back()->with('status', $message);
    }
}
