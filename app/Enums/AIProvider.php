<?php

namespace App\Enums;

enum AIProvider: string
{
    case OpenAI = 'openai';
    case Gemini = 'gemini';
    case Claude = 'claude';

    public function label(): string
    {
        return match ($this) {
            self::OpenAI => 'OpenAI (ChatGPT)',
            self::Gemini => 'Google Gemini',
            self::Claude => 'Anthropic Claude',
        };
    }

    public function defaultModel(): string
    {
        return match ($this) {
            self::OpenAI => 'gpt-4o-mini',
            self::Gemini => 'gemini-3.5-flash',
            self::Claude => 'claude-3-5-sonnet-latest',
        };
    }

    public function keyHelpUrl(): string
    {
        return match ($this) {
            self::OpenAI => 'https://platform.openai.com/api-keys',
            self::Gemini => 'https://aistudio.google.com/app/apikey',
            self::Claude => 'https://console.anthropic.com/settings/keys',
        };
    }
}
