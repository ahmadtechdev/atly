@php
    use App\Enums\MembershipRole;
@endphp

@props([
    'owner',
    'members' => [],
    'canInvite' => false,
    'inviteTarget' => null,
    'viewerIsOwner' => false,
    'updateUrlPattern' => null,
    'removeUrlPattern' => null,
])

@php
    $count = ($owner ? 1 : 0) + (is_countable($members) ? count($members) : 0);
    $roleOptions = MembershipRole::assignable();
@endphp

<section class="rounded-atly-lg border border-atly-border bg-atly-card p-4 shadow-atly">
    <div class="mb-3 flex items-center justify-between gap-2">
        <h3 class="font-display text-[11px] font-semibold uppercase tracking-wide text-atly-ink-soft">Members <span class="text-atly-ink-soft/60">({{ $count }})</span></h3>
        @if ($canInvite && $inviteTarget)
            <button
                type="button"
                data-open-invite-modal
                data-invitable-type="{{ $inviteTarget['type'] }}"
                data-invitable-id="{{ $inviteTarget['id'] }}"
                data-invitable-label="{{ $inviteTarget['label'] }}"
                class="inline-flex items-center gap-1 rounded-lg border border-atly-border bg-atly-surface px-2.5 py-1 text-[11px] font-semibold text-atly-ink hover:bg-atly-muted/50"
            >
                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Invite
            </button>
        @endif
    </div>

    @if ($count === 0)
        <p class="px-2 py-4 text-center text-xs text-atly-ink-soft">No members yet.</p>
    @else
        <ul class="space-y-1">
            @if ($owner)
                <li class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-atly-muted/30">
                    @if ($owner->avatar_url)
                        <img src="{{ $owner->avatar_url }}" alt="{{ $owner->name }}" class="size-7 shrink-0 rounded-full object-cover">
                    @else
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-atly-contrast-bg text-[10px] font-semibold text-atly-contrast-fg">{{ $owner->initials() }}</span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-xs font-semibold text-atly-ink">{{ $owner->name }}</span>
                        <span class="block truncate text-[10px] text-atly-ink-soft">{{ $owner->email }}</span>
                    </span>
                    <span class="inline-flex shrink-0 rounded-md bg-emerald-100 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Owner</span>
                </li>
            @endif

            @foreach ($members as $member)
                @php
                    $role = MembershipRole::tryParse($member->pivot->role ?? null);
                    $updateUrl = $viewerIsOwner && $updateUrlPattern ? str_replace('__USER_ID__', (string) $member->id, $updateUrlPattern) : null;
                    $removeUrl = $viewerIsOwner && $removeUrlPattern ? str_replace('__USER_ID__', (string) $member->id, $removeUrlPattern) : null;
                @endphp
                <li
                    class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-atly-muted/30"
                    @if ($viewerIsOwner && $updateUrl && $removeUrl)
                        data-member-row
                        data-member-name="{{ $member->name }}"
                        data-current-role="{{ $role?->value }}"
                        data-update-url="{{ $updateUrl }}"
                        data-remove-url="{{ $removeUrl }}"
                    @endif
                >
                    @if ($member->avatar_url)
                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="size-7 shrink-0 rounded-full object-cover">
                    @else
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-atly-muted text-[10px] font-semibold text-atly-ink">{{ $member->initials() }}</span>
                    @endif
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-xs font-semibold text-atly-ink">{{ $member->name }}</span>
                        <span class="block truncate text-[10px] text-atly-ink-soft">{{ $member->email }}</span>
                    </span>
                    @if ($viewerIsOwner && $updateUrl)
                        <span class="relative inline-flex">
                            <button type="button" data-member-menu-trigger class="inline-flex items-center gap-1 rounded-md border border-transparent px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide transition {{ $role?->colorClass() ?? 'bg-atly-muted text-atly-ink-soft' }} hover:border-atly-border">
                                <span data-member-role-label>{{ $role?->label() ?? 'Set role' }}</span>
                                <svg class="size-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div data-member-menu class="absolute right-0 top-full z-30 mt-1 hidden w-52 rounded-xl border border-atly-border bg-atly-card p-1.5 shadow-atly-lg">
                                <p class="px-2 py-1 text-[9px] font-semibold uppercase tracking-wide text-atly-ink-soft">Change role</p>
                                @foreach ($roleOptions as $opt)
                                    <button type="button" data-member-set-role="{{ $opt->value }}" class="flex w-full items-start gap-2 rounded-lg px-2 py-1.5 text-left hover:bg-atly-muted/50">
                                        <span class="inline-flex shrink-0 rounded-md px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide {{ $opt->colorClass() }}">{{ $opt->label() }}</span>
                                        <span class="min-w-0 flex-1 text-[10px] leading-snug text-atly-ink-soft">{{ $opt->description() }}</span>
                                    </button>
                                @endforeach
                                <div class="my-1 border-t border-atly-border"></div>
                                <button type="button" data-member-remove class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left text-[11px] font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m11.456 0a48.11 48.11 0 0 0-3.478-.397m-9.978.397a48.11 48.11 0 0 1 3.478-.397M4.772 5.79V4.5a2.25 2.25 0 0 1 2.25-2.25h9.956a2.25 2.25 0 0 1 2.25 2.25v1.29" /></svg>
                                    Remove from {{ $inviteTarget['type'] ?? 'item' }}
                                </button>
                            </div>
                        </span>
                    @elseif ($role)
                        <span class="inline-flex shrink-0 rounded-md px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide {{ $role->colorClass() }}">{{ $role->label() }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
