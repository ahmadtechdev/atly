<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Donation / support link
    |--------------------------------------------------------------------------
    | Shown to users when no AI models are available (no keys configured or
    | every model has hit its monthly limit). Leave blank to hide the prompt.
    */

    'donation_url' => env('AI_DONATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Provider API keys (developer-managed)
    |--------------------------------------------------------------------------
    | Keys live in the .env file. A model is only offered to users if its
    | provider has a non-empty key here AND it is marked enabled below.
    */

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
        ],
        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available AI models
    |--------------------------------------------------------------------------
    | Each entry is a model the user may select inside AI Blueprint. Toggle
    | `enabled` to expose/hide a model. `monthly_limit` caps how many times
    | this app may call the model per calendar month (null = unlimited). The
    | counter resets at the start of each month.
    */

    'models' => [

        'openai-gpt-4o-mini' => [
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'label' => 'GPT-4o Mini',
            'tagline' => 'Fast & affordable — great default.',
            'enabled' => true,
            'monthly_limit' => 100,
        ],

        'openai-gpt-4o' => [
            'provider' => 'openai',
            'model' => 'gpt-4o',
            'label' => 'GPT-4o',
            'tagline' => 'Best reasoning, higher cost.',
            'enabled' => false,
            'monthly_limit' => 25,
        ],

        'gemini-flash' => [
            'provider' => 'gemini',
            'model' => 'gemini-3.5-flash',
            'label' => 'Gemini 3.5 Flash',
            'tagline' => 'Generous free tier — fast.',
            'enabled' => true,
            'monthly_limit' => null,
        ],

        'gemini-pro' => [
            'provider' => 'gemini',
            'model' => 'gemini-3.5-pro',
            'label' => 'Gemini 3.5 Pro',
            'tagline' => 'Stronger reasoning.',
            'enabled' => false,
            'monthly_limit' => 50,
        ],

        'claude-haiku' => [
            'provider' => 'claude',
            'model' => 'claude-3-5-haiku-latest',
            'label' => 'Claude 3.5 Haiku',
            'tagline' => 'Quick & cost-efficient.',
            'enabled' => true,
            'monthly_limit' => 50,
        ],

        'claude-sonnet' => [
            'provider' => 'claude',
            'model' => 'claude-3-5-sonnet-latest',
            'label' => 'Claude 3.5 Sonnet',
            'tagline' => 'Best for complex plans.',
            'enabled' => false,
            'monthly_limit' => 20,
        ],

    ],
];
