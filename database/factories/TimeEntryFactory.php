<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        $started = fake()->dateTimeBetween('-5 days', '-1 hour');
        $duration = fake()->numberBetween(60, 7200);

        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'description' => fake()->optional(0.3)->sentence(),
            'started_at' => $started,
            'ended_at' => (clone $started)->modify("+{$duration} seconds"),
            'duration_seconds' => $duration,
        ];
    }

    public function running(): static
    {
        return $this->state(fn () => [
            'started_at' => fake()->dateTimeBetween('-1 hour', 'now'),
            'ended_at' => null,
            'duration_seconds' => null,
        ]);
    }
}
