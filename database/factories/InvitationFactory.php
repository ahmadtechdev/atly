<?php

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inviter_id' => User::factory(),
            'invitee_id' => null,
            'invitee_email' => fake()->safeEmail(),
            'invitable_type' => Project::class,
            'invitable_id' => Project::factory(),
            'role' => 'member',
            'status' => InvitationStatus::Pending,
            'message' => null,
            'token' => (string) Str::ulid().bin2hex(random_bytes(8)),
            'expires_at' => now()->addDays(14),
            'responded_at' => null,
        ];
    }

    public function forWorkspace(?Workspace $workspace = null): self
    {
        return $this->state(fn () => [
            'invitable_type' => Workspace::class,
            'invitable_id' => $workspace?->id ?? Workspace::factory(),
        ]);
    }

    public function accepted(): self
    {
        return $this->state(fn () => [
            'status' => InvitationStatus::Accepted,
            'responded_at' => now(),
        ]);
    }
}
