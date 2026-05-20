<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMembershipUpdates;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    use HandlesMembershipUpdates;

    public function update(Request $request, Project $project, User $member): JsonResponse|RedirectResponse
    {
        $role = $this->applyRoleUpdate($request, $project, $member);

        return $this->jsonOrBack(
            $request,
            $member->name." is now a {$role->label()}.",
            'projects.show',
            $project,
        );
    }

    public function destroy(Request $request, Project $project, User $member): JsonResponse|RedirectResponse
    {
        $this->applyRemove($request, $project, $member);

        return $this->jsonOrBack(
            $request,
            $member->name.' was removed from this project.',
            'projects.show',
            $project,
        );
    }
}
