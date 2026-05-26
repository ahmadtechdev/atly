<?php

namespace App\Services\AI;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Services\AI\Exceptions\AIRequestException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Throwable;

class BlueprintGenerator
{
    public function __construct(
        private readonly AIClientFactory $factory,
        private readonly AIModelRegistry $registry,
        private readonly AIUsageTracker $usage,
        private readonly BlueprintPromptBuilder $prompts,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generate(string $modelId, array $context): array
    {
        $model = $this->registry->find($modelId);

        if ($model === null) {
            throw new AIRequestException('Unknown AI model selected.');
        }

        if (! $model['available']) {
            throw new AIRequestException(match ($model['unavailable_reason']) {
                'disabled' => 'That AI model is currently disabled.',
                'no_key' => 'No API key is configured for this model.',
                'limit_reached' => 'This model has reached its monthly usage limit. Try another model or support the project to extend access.',
                default => 'This model is not available right now.',
            });
        }

        $client = $this->factory->forModel($modelId);

        try {
            $raw = $client->generateJson(
                $this->prompts->systemPrompt(),
                $this->prompts->userPrompt($context),
                (string) $model['model'],
            );
        } catch (AIRequestException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AIRequestException('AI generation failed: '.$e->getMessage(), previous: $e);
        }

        $blueprint = $this->normalize($raw, $context);

        $this->usage->increment($modelId);

        return $blueprint;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function normalize(array $payload, array $context): array
    {
        $start = CarbonImmutable::parse((string) $context['start_date']);
        $end = CarbonImmutable::parse((string) $context['end_date']);

        $projectName = (string) ($payload['project']['name'] ?? Str::limit((string) ($context['description'] ?? 'Untitled project'), 60, ''));
        $projectDescription = (string) ($payload['project']['description'] ?? '');

        $tasks = [];

        foreach ($payload['tasks'] ?? [] as $index => $task) {
            if (! is_array($task)) {
                continue;
            }

            $title = trim((string) ($task['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $taskStart = $this->clampDate($task['start_date'] ?? null, $start, $end, fallback: $start);
            $taskDue = $this->clampDate($task['due_date'] ?? null, $start, $end, fallback: $end);

            if ($taskDue->lessThan($taskStart)) {
                $taskDue = $taskStart;
            }

            $tasks[] = [
                'id' => 'draft-'.($index + 1),
                'title' => Str::limit($title, 200, ''),
                'description' => trim((string) ($task['description'] ?? '')),
                'milestone' => trim((string) ($task['milestone'] ?? 'General')),
                'skill_required' => trim((string) ($task['skill_required'] ?? '')),
                'estimated_hours' => $this->intInRange($task['estimated_hours'] ?? 4, 1, 200, 4),
                'priority' => $this->resolvePriority($task['priority'] ?? null)->value,
                'status' => TaskStatus::Pending->value,
                'start_date' => $taskStart->toDateString(),
                'due_date' => $taskDue->toDateString(),
                'assigned_to' => $this->resolveAssignment($task['assigned_to'] ?? null),
                'depends_on' => $this->resolveDependsOn($task['depends_on'] ?? []),
            ];
        }

        if ($tasks === []) {
            throw new AIRequestException('The AI did not produce any tasks. Try refining your description.');
        }

        return [
            'project' => [
                'name' => Str::limit($projectName, 200, ''),
                'description' => $projectDescription,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            'tasks' => $tasks,
        ];
    }

    private function clampDate(mixed $value, CarbonImmutable $min, CarbonImmutable $max, CarbonImmutable $fallback): CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return $fallback;
        }

        try {
            $date = CarbonImmutable::parse($value);
        } catch (Throwable) {
            return $fallback;
        }

        if ($date->lessThan($min)) {
            return $min;
        }

        if ($date->greaterThan($max)) {
            return $max;
        }

        return $date;
    }

    private function intInRange(mixed $value, int $min, int $max, int $default): int
    {
        if (! is_numeric($value)) {
            return $default;
        }

        $int = (int) $value;

        return max($min, min($max, $int));
    }

    private function resolvePriority(mixed $value): TaskPriority
    {
        if ($value instanceof TaskPriority) {
            return $value;
        }

        if (is_string($value)) {
            return TaskPriority::tryFrom(strtolower(trim($value))) ?? TaskPriority::Medium;
        }

        return TaskPriority::Medium;
    }

    private function resolveAssignment(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : Str::limit($trimmed, 120, '');
    }

    /**
     * @return array<int, string>
     */
    private function resolveDependsOn(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item): string => is_string($item) ? trim($item) : '',
            $value,
        ), fn (string $item): bool => $item !== ''));
    }
}
