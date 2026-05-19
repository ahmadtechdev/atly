<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('atly.description') }}">

    <title>{{ config('atly.name') }} — {{ config('atly.tagline') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|plus-jakarta-sans:500,600,700,800" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    {{ $slot }}

    <script>
        document.getElementById('nav-toggle')?.addEventListener('click', () => {
            const menu = document.getElementById('nav-menu');
            const open = menu?.classList.toggle('hidden') === false;
            document.getElementById('nav-toggle')?.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    </script>
</body>
</html>
