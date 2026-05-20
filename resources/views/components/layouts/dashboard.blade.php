@props(['title' => 'Dashboard'])

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
<body class="min-h-screen bg-atly-surface" data-sidebar-collapsed="false">
    <script>
        try {
            if (localStorage.getItem('atly-sidebar-collapsed') === 'true') {
                document.currentScript.parentElement.dataset.sidebarCollapsed = 'true';
            }
        } catch (e) {}
    </script>
    <div class="flex min-h-screen">
        <x-dashboard.sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-dashboard.topbar :title="$title">
                @isset($actions)
                    <x-slot:actions>{{ $actions }}</x-slot:actions>
                @endisset
            </x-dashboard.topbar>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @include('tasks.partials.quick-modal')
    @include('projects.partials.quick-modal')
    @include('workspaces.partials.quick-modal')
    @include('invitations.partials.invite-modal')

    @auth
        <script>
            window.atlyTasks = {
                indexUrl: @json(route('tasks.index')),
                storeUrl: @json(route('tasks.store')),
                tasksUrl: @json(route('tasks.index')),
                csrf: @json(csrf_token()),
            };
            window.atlyProjects = {
                indexUrl: @json(route('projects.index')),
                storeUrl: @json(route('projects.store')),
                searchUrl: @json(route('projects.search')),
                csrf: @json(csrf_token()),
            };
            window.atlyWorkspaces = {
                indexUrl: @json(route('workspaces.index')),
                storeUrl: @json(route('workspaces.store')),
                searchUrl: @json(route('workspaces.search')),
                csrf: @json(csrf_token()),
            };
            window.atlyInvitations = {
                indexUrl: @json(route('invitations.index')),
                storeUrl: @json(route('invitations.store')),
                csrf: @json(csrf_token()),
            };
        </script>
    @endauth

    @include('partials.flash-data')

    @stack('scripts')
</body>
</html>
