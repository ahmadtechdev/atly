<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMembershipUpdates;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    use HandlesMembershipUpdates;

    public function update(Request $request, Workspace $workspace, User $member): JsonResponse|RedirectResponse
    {
        $role = $this->applyRoleUpdate($request, $workspace, $member);

        return $this->jsonOrBack(
            $request,
            $member->name." is now a {$role->label()}.",
            'workspaces.show',
            $workspace,
        );
    }

    public function destroy(Request $request, Workspace $workspace, User $member): JsonResponse|RedirectResponse
    {
        $this->applyRemove($request, $workspace, $member);

        return $this->jsonOrBack(
            $request,
            $member->name.' was removed from this workspace.',
            'workspaces.show',
            $workspace,
        );
    }
}
