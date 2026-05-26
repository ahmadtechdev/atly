<?php

namespace App\Services\AI\Support;

use App\Services\AI\Exceptions\AIRequestException;

class JsonResponseParser
{
    /**
     * Extract and decode a JSON object from a model's textual response.
     *
     * @return array<string, mixed>
     */
    public static function parse(string $raw): array
    {
        $clean = self::stripFences(trim($raw));

        $decoded = json_decode($clean, true);

        if (! is_array($decoded)) {
            $extracted = self::extractJsonBlock($clean);
            $decoded = $extracted === null ? null : json_decode($extracted, true);
        }

        if (! is_array($decoded)) {
            throw new AIRequestException('The AI response did not contain valid JSON.');
        }

        return $decoded;
    }

    private static function stripFences(string $text): string
    {
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*\n?/i', '', $text) ?? $text;
            $text = preg_replace('/\n?```\s*$/', '', $text) ?? $text;
        }

        return trim($text);
    }

    private static function extractJsonBlock(string $text): ?string
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return substr($text, $start, $end - $start + 1);
    }
}
