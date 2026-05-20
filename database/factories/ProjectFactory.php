<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workspace_id' => null,
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional(0.5)->sentence(),
            'color' => fake()->randomElement(['sky', 'emerald', 'amber', 'rose', 'violet', 'fuchsia']),
        ];
    }

    public function inWorkspace(Workspace $workspace): static
    {
        return $this->state(fn () => [
            'user_id' => $workspace->user_id,
            'workspace_id' => $workspace->id,
        ]);
    }
}
