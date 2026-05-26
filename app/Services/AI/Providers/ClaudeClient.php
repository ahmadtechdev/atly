<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIClient;
use App\Services\AI\Exceptions\AIRequestException;
use App\Services\AI\Support\JsonResponseParser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ClaudeClient implements AIClient
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const API_VERSION = '2023-06-01';

    public function __construct(private readonly string $apiKey) {}

    public function generateJson(string $systemPrompt, string $userPrompt, string $model): array
    {
        try {
            $response = Http::acceptJson()
                ->timeout(90)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => self::API_VERSION,
                ])
                ->post(self::ENDPOINT, [
                    'model' => $model,
                    'max_tokens' => 4096,
                    'temperature' => 0.3,
                    'system' => $systemPrompt."\n\nRespond with a single valid JSON object only — no prose, no markdown fences.",
                    'messages' => [
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw new AIRequestException('Could not reach Anthropic Claude. Check your network.', previous: $e);
        }

        if (! $response->ok()) {
            $message = $response->json('error.message') ?? 'Claude request failed.';
            throw new AIRequestException($message);
        }

        $blocks = $response->json('content') ?? [];
        $content = '';

        foreach ($blocks as $block) {
            if (is_array($block) && ($block['type'] ?? null) === 'text' && isset($block['text'])) {
                $content .= $block['text'];
            }
        }

        if ($content === '') {
            throw new AIRequestException('Claude returned an empty response.');
        }

        return JsonResponseParser::parse($content);
    }
}
