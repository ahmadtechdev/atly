<x-layouts.admin title="Dashboard">
    <div class="space-y-8">
        <div>
            <h2 class="font-display text-xl font-semibold text-atly-ink">User overview</h2>
            <p class="mt-1 text-sm text-atly-ink-soft">Registered accounts on the platform (excluding super admin).</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
            <x-dashboard.stat-card label="Total registered" :value="$userStats['total_registered']" :accent="true" />
            <x-dashboard.stat-card label="Verified" :value="$userStats['verified']" :hint="$userStats['verification_rate'].'% verified'" />
            <x-dashboard.stat-card
                label="Verification pending"
                :value="$userStats['verification_pending']"
                :hint="$userStats['verification_pending'] > 0 ? 'Awaiting email verification' : 'All caught up'"
            />
            <x-dashboard.stat-card label="Registered today" :value="$userStats['registered_today']" />
            <x-dashboard.stat-card label="This week" :value="$userStats['registered_this_week']" hint="New signups since Monday" />
        </div>

        <div>
            <h2 class="font-display text-xl font-semibold text-atly-ink">Platform activity</h2>
            <p class="mt-1 text-sm text-atly-ink-soft">Aggregate counts across all users.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-dashboard.stat-card label="Workspaces" :value="$platformStats['workspaces']" />
            <x-dashboard.stat-card label="Projects" :value="$platformStats['projects']" />
            <x-dashboard.stat-card label="Tasks" :value="$platformStats['tasks']" />
            <x-dashboard.stat-card label="Invitations" :value="$platformStats['invitations']" />
        </div>

        @if ($userStats['verification_pending'] > 0)
            <div class="rounded-atly-lg border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/30">
                <p class="text-sm font-medium text-amber-900 dark:text-amber-100">
                    {{ $userStats['verification_pending'] }} {{ str('user')->plural($userStats['verification_pending']) }} still need to verify their email.
                </p>
            </div>
        @endif
    </div>
</x-layouts.admin>
