<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMembershipUpdates;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskCollaboratorController extends Controller
{
    use HandlesMembershipUpdates;

    public function update(Request $request, Task $task, User $member): JsonResponse|RedirectResponse
    {
        $role = $this->applyRoleUpdate($request, $task, $member);

        return $this->jsonOrBack(
            $request,
            $member->name." is now a {$role->label()}.",
            'tasks.show',
            $task,
        );
    }

    public function destroy(Request $request, Task $task, User $member): JsonResponse|RedirectResponse
    {
        $this->applyRemove($request, $task, $member);

        return $this->jsonOrBack(
            $request,
            $member->name.' was removed from this task.',
            'tasks.show',
            $task,
        );
    }
}
