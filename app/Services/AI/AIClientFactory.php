<?php

namespace App\Services\AI;

use App\Enums\AIProvider;
use App\Services\AI\Contracts\AIClient;
use App\Services\AI\Exceptions\AIRequestException;
use App\Services\AI\Providers\ClaudeClient;
use App\Services\AI\Providers\GeminiClient;
use App\Services\AI\Providers\OpenAIClient;

class AIClientFactory
{
    /**
     * Build a configured client for the given user-facing model identifier.
     */
    public function forModel(string $modelId): AIClient
    {
        $models = config('ai.models', []);
        $providers = config('ai.providers', []);

        $config = $models[$modelId] ?? null;

        if (! is_array($config)) {
            throw new AIRequestException('Unknown AI model selected.');
        }

        if (! ($config['enabled'] ?? false)) {
            throw new AIRequestException('That AI model is currently disabled.');
        }

        $providerKey = (string) ($config['provider'] ?? '');
        $apiKey = $providers[$providerKey]['api_key'] ?? null;

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new AIRequestException('No API key configured for this model. Please contact the administrator.');
        }

        $provider = AIProvider::tryFrom($providerKey)
            ?? throw new AIRequestException('Unknown AI provider for this model.');

        return $this->make($provider, $apiKey);
    }

    public function make(AIProvider $provider, string $apiKey): AIClient
    {
        return match ($provider) {
            AIProvider::OpenAI => new OpenAIClient($apiKey),
            AIProvider::Gemini => new GeminiClient($apiKey),
            AIProvider::Claude => new ClaudeClient($apiKey),
        };
    }
}
