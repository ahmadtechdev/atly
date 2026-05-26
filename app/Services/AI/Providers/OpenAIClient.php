<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIClient;
use App\Services\AI\Exceptions\AIRequestException;
use App\Services\AI\Support\JsonResponseParser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenAIClient implements AIClient
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function __construct(private readonly string $apiKey) {}

    public function generateJson(string $systemPrompt, string $userPrompt, string $model): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->timeout(90)
                ->post(self::ENDPOINT, [
                    'model' => $model,
                    'temperature' => 0.3,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw new AIRequestException('Could not reach OpenAI. Check your network.', previous: $e);
        }

        if (! $response->ok()) {
            $message = $response->json('error.message') ?? 'OpenAI request failed.';
            throw new AIRequestException($message);
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new AIRequestException('OpenAI returned an empty response.');
        }

        return JsonResponseParser::parse($content);
    }
}
