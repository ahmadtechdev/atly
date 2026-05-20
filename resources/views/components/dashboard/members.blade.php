@props([
    'owner',
    'members' => [],
    'canInvite' => false,
    'inviteTarget' => null,
])

@php
    $count = ($owner ? 1 : 0) + (is_countable($members) ? count($members) : 0);
@endphp

<section class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-atly-ink-soft">Members ({{ $count }})</h3>
        @if ($canInvite && $inviteTarget)
            <button
                type="button"
                data-open-invite-modal
                data-invitable-type="{{ $inviteTarget['type'] }}"
                data-invitable-id="{{ $inviteTarget['id'] }}"
                data-invitable-label="{{ $inviteTarget['label'] }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-atly-border bg-atly-surface px-3 py-1.5 text-xs font-semibold text-atly-ink hover:bg-atly-muted/50"
            >
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3" /></svg>
                Invite
            </button>
        @endif
    </div>

    <ul class="grid gap-2 sm:grid-cols-2">
        @if ($owner)
            <li class="flex items-center gap-3 rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5">
                @if ($owner->avatar_url)
                    <img src="{{ $owner->avatar_url }}" alt="{{ $owner->name }}" class="size-8 shrink-0 rounded-full object-cover">
                @else
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-atly-contrast-bg text-xs font-semibold text-atly-contrast-fg">{{ $owner->initials() }}</span>
                @endif
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-atly-ink">{{ $owner->name }}</span>
                    <span class="block truncate text-[11px] text-atly-ink-soft">{{ $owner->email }}</span>
                </span>
                <span class="inline-flex shrink-0 rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Owner</span>
            </li>
        @endif

        @foreach ($members as $member)
            @php
                $role = \App\Enums\MembershipRole::tryParse($member->pivot->role ?? null);
            @endphp
            <li class="flex items-center gap-3 rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5">
                @if ($member->avatar_url)
                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="size-8 shrink-0 rounded-full object-cover">
                @else
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-atly-muted text-xs font-semibold text-atly-ink">{{ $member->initials() }}</span>
                @endif
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-atly-ink">{{ $member->name }}</span>
                    <span class="block truncate text-[11px] text-atly-ink-soft">{{ $member->email }}</span>
                </span>
                @if ($role)
                    <span class="inline-flex shrink-0 rounded-md px-2 py-0.5 text-[10px] font-semibold {{ $role->colorClass() }}">{{ $role->label() }}</span>
                @endif
            </li>
        @endforeach
    </ul>

    @if ($count === 0)
        <p class="px-2 py-6 text-center text-sm text-atly-ink-soft">No members yet.</p>
    @endif
</section>
