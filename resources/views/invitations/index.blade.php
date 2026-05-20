<x-layouts.dashboard title="Invitations">
    @php
        $tabs = [
            'incoming' => ['label' => 'Received', 'count' => $incoming->count(), 'items' => $incoming],
            'sent' => ['label' => 'Sent', 'count' => $sent->count(), 'items' => $sent],
        ];
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="font-display text-2xl font-bold text-atly-ink">Invitations</h2>
                <p class="mt-1 text-sm text-atly-ink-soft">Manage requests you've sent and ones you've received.</p>
            </div>
        </div>

        <div class="inline-flex rounded-xl border border-atly-border bg-atly-card p-1 shadow-atly">
            @foreach ($tabs as $key => $info)
                <a
                    href="{{ route('invitations.index', ['tab' => $key]) }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition',
                        'bg-atly-contrast-bg text-atly-contrast-fg shadow-sm' => $tab === $key,
                        'text-atly-ink-soft hover:text-atly-ink' => $tab !== $key,
                    ])
                >
                    {{ $info['label'] }}
                    <span @class([
                        'inline-flex min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[10px] font-bold leading-tight',
                        'bg-atly-contrast-fg/15 text-atly-contrast-fg' => $tab === $key,
                        'bg-atly-muted text-atly-ink-soft' => $tab !== $key,
                    ])>{{ $info['count'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card shadow-atly">
            @php
                $items = $tabs[$tab]['items'];
            @endphp

            @if ($items->isEmpty())
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-2xl bg-atly-muted/40 text-atly-ink-soft">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                    </div>
                    <p class="text-sm text-atly-ink-soft">
                        @if ($tab === 'incoming')
                            You don't have any invitations right now.
                        @else
                            You haven't sent any invitations yet.
                        @endif
                    </p>
                </div>
            @else
                <ul class="divide-y divide-atly-border">
                    @foreach ($items as $invitation)
                        @php
                            $isIncoming = $tab === 'incoming';
                            $counterpart = $isIncoming ? $invitation->inviter : $invitation->invitee;
                            $counterpartName = $counterpart?->name ?? $invitation->invitee_email;
                        @endphp
                        <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-atly-muted text-sm font-semibold text-atly-ink">
                                    {{ strtoupper(mb_substr($counterpartName, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-atly-ink">
                                        @if ($isIncoming)
                                            {{ $counterpartName }} invited you to a {{ $invitation->invitableKind() }}
                                        @else
                                            Invitation to {{ $invitation->invitee_email }}
                                        @endif
                                    </p>
                                    <p class="mt-0.5 truncate text-xs text-atly-ink-soft">
                                        <span class="font-medium text-atly-ink">{{ ucfirst($invitation->invitableKind()) }}:</span>
                                        {{ $invitation->invitableLabel() }}
                                        @if ($invitation->message)
                                            <span class="ml-1 italic">— "{{ \Illuminate\Support\Str::limit($invitation->message, 80) }}"</span>
                                        @endif
                                    </p>
                                    <p class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-atly-ink-soft">
                                        <span>{{ $invitation->created_at->diffForHumans() }}</span>
                                        @if ($invitation->expires_at && $invitation->isPending())
                                            <span>•</span>
                                            <span>Expires {{ $invitation->expires_at->diffForHumans() }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                @php
                                    $role = \App\Enums\MembershipRole::tryParse($invitation->role);
                                @endphp
                                @if ($role)
                                    <span class="inline-flex rounded-md px-2 py-0.5 text-[10px] font-semibold {{ $role->colorClass() }}">{{ $role->label() }}</span>
                                @endif
                                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold {{ $invitation->status->colorClass() }}">{{ $invitation->status->label() }}</span>

                                @if (! $isIncoming && $invitation->invitee_id === null && $invitation->isPending())
                                    <span class="inline-flex rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-200" title="Recipient hasn't signed up yet — they'll appear after creating an account.">Awaiting sign-up</span>
                                @endif

                                @if ($invitation->isPending())
                                    @if ($isIncoming)
                                        <form method="POST" action="{{ route('invitations.accept', $invitation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5" /></svg>
                                                Accept
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('invitations.decline', $invitation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-atly-border bg-atly-surface px-3 py-1.5 text-xs font-semibold text-atly-ink hover:bg-atly-muted/40">
                                                Decline
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('invitations.destroy', $invitation) }}" onsubmit="return confirm('Cancel this invitation?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
