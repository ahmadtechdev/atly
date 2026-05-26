<?php

namespace App\Services\AI\Contracts;

interface AIClient
{
    /**
     * Send a structured prompt expecting a JSON response.
     *
     * @return array<string, mixed>
     */
    public function generateJson(string $systemPrompt, string $userPrompt, string $model): array;
}
