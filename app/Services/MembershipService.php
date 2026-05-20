<?php

namespace App\Services;

use App\Enums\MembershipRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Centralised owner-only operations for membership pivots (task collaborators,
 * project members, workspace members). Authorization is performed by the
 * caller (policy / form-request). This service deals only with the data
 * change itself.
 */
class MembershipService
{
    /** @var array<class-string<Model>, array{relation:string,foreign:string,table:string}> */
    private const TARGETS = [
        Task::class => ['relation' => 'collaborators', 'foreign' => 'task_id', 'table' => 'task_collaborators'],
        Project::class => ['relation' => 'members', 'foreign' => 'project_id', 'table' => 'project_members'],
        Workspace::class => ['relation' => 'members', 'foreign' => 'workspace_id', 'table' => 'workspace_members'],
    ];

    public function updateRole(Model $entity, User $member, MembershipRole $role): void
    {
        $target = $this->resolveTarget($entity);

        if ($entity->user_id === $member->id) {
            throw ValidationException::withMessages([
                'role' => 'The owner role cannot be changed.',
            ]);
        }

        $exists = $entity->{$target['relation']}()->whereKey($member->id)->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'role' => 'That user is not a member of this item.',
            ]);
        }

        $entity->{$target['relation']}()->updateExistingPivot($member->id, [
            'role' => $role->value,
            'updated_at' => now(),
        ]);
    }

    public function remove(Model $entity, User $member): void
    {
        $target = $this->resolveTarget($entity);

        if ($entity->user_id === $member->id) {
            throw ValidationException::withMessages([
                'member' => 'The owner cannot be removed.',
            ]);
        }

        $entity->{$target['relation']}()->detach($member->id);
    }

    /**
     * @return array{relation:string,foreign:string,table:string}
     */
    private function resolveTarget(Model $entity): array
    {
        $type = $entity::class;

        if (! array_key_exists($type, self::TARGETS)) {
            throw ValidationException::withMessages([
                'entity' => 'This entity does not support membership operations.',
            ]);
        }

        return self::TARGETS[$type];
    }
}
