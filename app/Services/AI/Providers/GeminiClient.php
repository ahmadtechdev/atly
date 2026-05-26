<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIClient;
use App\Services\AI\Exceptions\AIRequestException;
use App\Services\AI\Support\JsonResponseParser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GeminiClient implements AIClient
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function __construct(private readonly string $apiKey) {}

    public function generateJson(string $systemPrompt, string $userPrompt, string $model): array
    {
        $url = sprintf(self::ENDPOINT, urlencode($model));

        try {
            $response = Http::acceptJson()
                ->timeout(90)
                ->withHeaders(['x-goog-api-key' => $this->apiKey])
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [['text' => $userPrompt]],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.3,
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw new AIRequestException('Could not reach Google Gemini. Check your network.', previous: $e);
        }

        if (! $response->ok()) {
            $message = $response->json('error.message') ?? 'Gemini request failed.';
            throw new AIRequestException($message);
        }

        $parts = $response->json('candidates.0.content.parts') ?? [];
        $content = '';

        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $content .= $part['text'];
            }
        }

        if ($content === '') {
            throw new AIRequestException('Gemini returned an empty response.');
        }

        return JsonResponseParser::parse($content);
    }
}
