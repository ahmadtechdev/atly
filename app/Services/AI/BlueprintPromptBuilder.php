<?php

namespace App\Services\AI;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

class BlueprintPromptBuilder
{
    public function systemPrompt(): string
    {
        $statuses = collect(TaskStatus::cases())->map(fn (TaskStatus $s) => $s->value)->implode(', ');
        $priorities = collect(TaskPriority::cases())->map(fn (TaskPriority $p) => $p->value)->implode(', ');

        return <<<PROMPT
You are ATLY's senior project planner. Convert the user's project brief into a realistic, executable task plan.

STRICT RULES:
- You MUST distribute task due dates logically between the user-provided Start Date and End Date.
- Calculate sequential tasks based on logical dependencies (e.g. database design must finish before API development starts).
- Ensure NO task due date exceeds the project's final End Date.
- Ensure NO task start date is before the project's Start Date.
- Each task must have an `estimated_hours` value (integer between 1 and 80) reflecting realistic effort.
- Group related work into milestones; the `milestone` field is a short phrase shared by tasks in the same phase.
- Each task must have a single primary `skill_required` (e.g. "Frontend", "Backend", "QA", "Design", "DevOps", "Research", "Documentation").
- When the assignment is for a team, balance work across members using the provided splits/skills via the `assigned_to` field (use the member name exactly as provided).
- Output ONLY a JSON object. No prose. No markdown fences. No commentary.

OUTPUT JSON SHAPE:
{
  "project": {
    "name": "string (concise project name)",
    "description": "string (1-3 sentence project summary)"
  },
  "tasks": [
    {
      "title": "string (max 120 chars, action verb)",
      "description": "string (1-3 sentences, what & how)",
      "milestone": "string (phase name, e.g. 'Discovery', 'Build', 'Launch')",
      "skill_required": "string",
      "estimated_hours": integer,
      "priority": "one of: {$priorities}",
      "status": "one of: {$statuses} (use 'pending' for all generated tasks)",
      "start_date": "YYYY-MM-DD",
      "due_date": "YYYY-MM-DD",
      "assigned_to": "string or null (member name or null for individual)",
      "depends_on": ["task title", "..."] or []
    }
  ]
}

QUALITY BAR:
- Produce 6 to 18 tasks depending on project scope.
- Order tasks logically; earlier tasks have earlier due_dates.
- Use ISO dates (YYYY-MM-DD).
- Prefer realistic, granular tasks over vague ones.
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function userPrompt(array $context): string
    {
        $lines = [];

        $lines[] = '### Project Brief';
        $lines[] = $context['description'] ?? '(no description provided)';
        $lines[] = '';
        $lines[] = '### Timeline';
        $lines[] = 'Start Date: '.($context['start_date'] ?? 'unknown');
        $lines[] = 'End Date:   '.($context['end_date'] ?? 'unknown');
        $lines[] = '';
        $lines[] = '### Assignment';

        if (($context['assignment_type'] ?? 'individual') === 'team') {
            $lines[] = 'Type: Team';
            $members = $context['members'] ?? [];
            $count = is_array($members) ? count($members) : 0;
            $lines[] = 'Team Size: '.$count;

            if (is_array($members) && $members !== []) {
                $lines[] = 'Members:';
                foreach ($members as $i => $member) {
                    $name = trim((string) ($member['name'] ?? ('Member '.($i + 1))));
                    $skill = trim((string) ($member['skill'] ?? ''));
                    $split = $member['split'] ?? null;

                    $parts = ["- {$name}"];
                    if ($skill !== '') {
                        $parts[] = "skill: {$skill}";
                    }
                    if ($split !== null && $split !== '') {
                        $parts[] = "workload: {$split}%";
                    }
                    $lines[] = implode(' | ', $parts);
                }
            }
        } else {
            $lines[] = 'Type: Individual (single contributor)';
        }

        if (! empty($context['document_text'])) {
            $lines[] = '';
            $lines[] = '### Project Source Document';
            $lines[] = (string) $context['document_text'];
        }

        $lines[] = '';
        $lines[] = 'Now generate the task plan as strict JSON following the system rules.';

        return implode("\n", $lines);
    }
}
