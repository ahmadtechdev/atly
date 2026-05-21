<div id="invite-modal" class="fixed inset-0 z-50 hidden overflow-y-auto overscroll-contain" aria-hidden="true">
    <div data-close-invite-modal class="fixed inset-0 bg-atly-ink/50 backdrop-blur-sm"></div>
    <div class="pointer-events-none relative flex min-h-full items-start justify-center p-4 sm:items-center sm:p-6">
        <div class="pointer-events-auto w-full max-w-md rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly-lg sm:p-7" role="dialog" aria-modal="true" aria-labelledby="invite-modal-title">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="invite-modal-title" class="font-display text-xl font-bold text-atly-ink">Invite someone</h2>
                    <p class="mt-1 truncate text-xs text-atly-ink-soft">
                        <span data-invite-kind class="font-semibold text-atly-ink">Project</span>
                        ·
                        <span data-invite-target class="truncate">—</span>
                    </p>
                </div>
                <button type="button" data-close-invite-modal class="rounded-lg p-2 text-atly-ink-soft hover:bg-atly-muted hover:text-atly-ink" aria-label="Close">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            @php
                $roleOptions = \App\Enums\MembershipRole::assignable();
            @endphp

            <form id="invite-form" class="space-y-4">
                @csrf
                <input type="hidden" name="invitable_type" value="">
                <input type="hidden" name="invitable_id" value="">

                <div>
                    <label for="invite-email" class="mb-1.5 block text-sm font-medium text-atly-ink">Email address</label>
                    <input
                        type="email"
                        name="email"
                        id="invite-email"
                        required
                        placeholder="teammate@example.com"
                        class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-atly-ink">Role</label>
                    <div class="space-y-2">
                        @foreach ($roleOptions as $idx => $role)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5 has-[input:checked]:border-atly-accent has-[input:checked]:bg-atly-muted/30">
                                <input
                                    type="radio"
                                    name="role"
                                    value="{{ $role->value }}"
                                    @checked($role === \App\Enums\MembershipRole::Assignee)
                                    class="mt-0.5"
                                >
                                <span class="flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-atly-ink">{{ $role->label() }}</span>
                                        <span class="inline-flex rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $role->colorClass() }}">{{ strtoupper($role->value) }}</span>
                                    </span>
                                    <span class="mt-0.5 block text-xs text-atly-ink-soft">{{ $role->description() }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="invite-message" class="mb-1.5 block text-sm font-medium text-atly-ink">Message <span class="text-atly-ink-soft">(optional)</span></label>
                    <textarea
                        name="message"
                        id="invite-message"
                        rows="3"
                        maxlength="500"
                        placeholder="Add a short note (optional)"
                        class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                    ></textarea>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" data-invite-submit class="inline-flex items-center gap-2 rounded-xl bg-atly-contrast-bg px-4 py-2.5 text-sm font-semibold text-atly-contrast-fg shadow-sm transition hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-60">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                        Send invitation
                    </button>
                    <button type="button" data-close-invite-modal class="rounded-xl border border-atly-border bg-atly-surface px-4 py-2.5 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
