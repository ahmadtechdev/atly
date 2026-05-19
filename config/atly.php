<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

    'name' => env('ATLY_NAME', 'ATLY'),

    'tagline' => env('ATLY_TAGLINE', 'Smart Task Management'),

    'slogan' => env('ATLY_SLOGAN', 'Organize smarter. Achieve more.'),

    'description' => env(
        'ATLY_DESCRIPTION',
        'ATLY helps teams and individuals plan, prioritize, and finish work with clarity — without the clutter of traditional tools.'
    ),

    /*
    |--------------------------------------------------------------------------
    | Navigation & actions (update when auth routes exist)
    |--------------------------------------------------------------------------
    */

    'links' => [
        'login' => env('ATLY_LOGIN_URL', '/login'),
        'register' => env('ATLY_REGISTER_URL', '/register'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Landing page content
    |--------------------------------------------------------------------------
    */

    'nav' => [
        ['label' => 'Features', 'href' => '#features'],
        ['label' => 'How it works', 'href' => '#how-it-works'],
        ['label' => 'Why ATLY', 'href' => '#why-atly'],
    ],

    'features' => [
        [
            'title' => 'Smart prioritization',
            'description' => 'Surface what matters today with intelligent ordering that adapts as your day unfolds.',
            'icon' => 'priority',
        ],
        [
            'title' => 'Unified workspace',
            'description' => 'Projects, tasks, and deadlines in one calm view — no tab-hopping or context switching.',
            'icon' => 'workspace',
        ],
        [
            'title' => 'Progress at a glance',
            'description' => 'Track momentum with clear status, timelines, and completion insights your team can trust.',
            'icon' => 'progress',
        ],
        [
            'title' => 'Collaboration built in',
            'description' => 'Assign, comment, and sync with your team in real time without noisy notifications.',
            'icon' => 'team',
        ],
        [
            'title' => 'Focus mode',
            'description' => 'Strip away distractions and work through your list one intentional step at a time.',
            'icon' => 'focus',
        ],
        [
            'title' => 'Secure & reliable',
            'description' => 'Your data stays protected with enterprise-ready practices from day one.',
            'icon' => 'secure',
        ],
    ],

    'steps' => [
        [
            'number' => '01',
            'title' => 'Capture everything',
            'description' => 'Add tasks, ideas, and deadlines in seconds — from anywhere you work.',
        ],
        [
            'number' => '02',
            'title' => 'Let ATLY organize',
            'description' => 'Smart grouping and priority suggestions keep your list actionable.',
        ],
        [
            'number' => '03',
            'title' => 'Ship with confidence',
            'description' => 'Execute with focus, track progress, and celebrate wins as you go.',
        ],
    ],

    'stats' => [
        ['value' => '10k+', 'label' => 'Tasks organized'],
        ['value' => '98%', 'label' => 'User satisfaction'],
        ['value' => '2x', 'label' => 'Faster completion'],
    ],

];
