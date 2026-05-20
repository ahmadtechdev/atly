@props(['title' => 'Account'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    @include('partials.theme-script')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ config('atly.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|plus-jakarta-sans:500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-atly-gradient-hero">
    <div class="pointer-events-none fixed inset-0 bg-atly-glow" aria-hidden="true"></div>

    <div class="absolute right-4 top-4 z-10 sm:right-6">
        <x-theme.toggle />
    </div>

    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6">
        <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2.5 transition hover:opacity-80">
            <span class="flex size-11 items-center justify-center rounded-xl bg-atly-contrast-bg text-sm font-bold text-atly-contrast-fg shadow-atly">
                {{ strtoupper(substr(config('atly.name'), 0, 1)) }}
            </span>
            <span class="font-display text-2xl font-bold text-atly-ink">{{ config('atly.name') }}</span>
        </a>

        <div class="w-full max-w-md">
            {{ $slot }}
        </div>

        <p class="mt-8 text-center text-sm text-atly-ink-soft">
            &copy; {{ date('Y') }} {{ config('atly.name') }}. {{ config('atly.tagline') }}
        </p>
    </div>

    @include('partials.flash-data')
</body>
</html>
