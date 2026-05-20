@use(App\Enums\MembershipRole)
@use(App\Models\TimeEntry)
@use(Illuminate\Support\Str)

@php
    $viewer = auth()->user();
    $isOwner = $viewer && $viewer->id === $task->user_id;
    $viewerRole = $viewer ? $task->roleFor($viewer) : null;
    $canManage = $payload['viewer']['can_edit'] ?? false;
    $canComment = $payload['viewer']['can_comment'] ?? false;
    $canComplete = $payload['viewer']['can_complete'] ?? false;
    $canTrack = $canComplete && ! ($payload['is_completed'] ?? false);
    $totalSeconds = $task->totalTrackedSeconds();
@endphp

<x-layouts.dashboard :title="$task->title">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-5">
            <div class="rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly">
                <nav class="flex flex-wrap items-center gap-1 text-xs text-atly-ink-soft">
                    <a href="{{ route('tasks.index') }}" class="hover:text-atly-ink">Tasks</a>
                    @if ($task->project)
                        <span>/</span>
                        <a href="{{ route('projects.show', $task->project) }}" class="hover:text-atly-ink">{{ $task->project->name }}</a>
                    @endif
                </nav>

                <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h1 @class([
                            'font-display text-2xl font-bold',
                            'text-atly-ink' => ! ($payload['is_completed'] ?? false),
                            'text-atly-ink-soft line-through' => $payload['is_completed'] ?? false,
                        ])>{{ $task->title }}</h1>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold {{ $payload['status_class'] }}">{{ $payload['status_label'] }}</span>
                            <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold {{ $payload['priority_class'] }}">{{ $payload['priority_label'] }}</span>
                            @if ($viewerRole && ! $isOwner)
                                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold {{ $viewerRole->colorClass() }}">You · {{ $viewerRole->label() }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($canManage)
                            <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-atly-contrast-bg px-3 py-2 text-sm font-semibold text-atly-contrast-fg">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                                Edit
                            </a>
                            <button
                                type="button"
                                data-open-invite-modal
                                data-invitable-type="task"
                                data-invitable-id="{{ $task->id }}"
                                data-invitable-label="{{ $task->title }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-atly-border bg-atly-card px-3 py-2 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                                Invite
                            </button>
                        @endif
                    </div>
                </div>

                <dl class="mt-5 grid grid-cols-2 gap-x-6 gap-y-3 border-t border-atly-border pt-4 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-atly-ink-soft">Start</dt>
                        <dd class="mt-0.5 font-medium text-atly-ink">{{ $task->start_date?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-atly-ink-soft">Due</dt>
                        <dd @class([
                            'mt-0.5 font-medium',
                            'text-rose-600' => $task->isOverdue(),
                            'text-atly-ink' => ! $task->isOverdue(),
                        ])>{{ $task->due_date?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-atly-ink-soft">Time tracked</dt>
                        <dd class="mt-0.5 font-medium text-atly-ink">{{ $totalSeconds > 0 ? TimeEntry::formatSeconds($totalSeconds) : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-atly-ink-soft">Created</dt>
                        <dd class="mt-0.5 font-medium text-atly-ink">{{ $task->created_at?->format('M j, Y') }}</dd>
                    </div>
                </dl>

                <div class="mt-5">
                    <h2 class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-atly-ink-soft">Description</h2>
                    @if ($task->description)
                        <div class="rounded-xl border border-atly-border/60 bg-atly-muted/20 px-4 py-3 text-sm leading-relaxed text-atly-ink whitespace-pre-wrap break-words">{{ $task->description }}</div>
                    @else
                        <p class="text-sm italic text-atly-ink-soft/80">No description.</p>
                    @endif
                </div>

                @if ($task->attachments->isNotEmpty())
                    <div class="mt-5">
                        <h2 class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-atly-ink-soft">Attachments ({{ $task->attachments->count() }})</h2>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($task->attachments as $attachment)
                                <a href="{{ $attachment->url() }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-lg border border-atly-border bg-atly-surface px-3 py-2 text-sm text-atly-ink hover:bg-atly-muted/40">
                                    <svg class="size-4 text-atly-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>
                                    <span class="min-w-0 flex-1 truncate">{{ $attachment->original_name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <section
                id="comments"
                class="rounded-atly-lg border border-atly-border bg-atly-card shadow-atly scroll-mt-24"
                data-task-comments
                data-task-id="{{ $task->id }}"
                data-comments-url="{{ route('tasks.comments.store', $task) }}"
                data-csrf="{{ csrf_token() }}"
                data-can-comment="{{ $canComment ? '1' : '0' }}"
            >
                <header class="flex items-center justify-between gap-3 border-b border-atly-border px-6 py-4">
                    <h2 class="font-display text-base font-bold text-atly-ink">Comments <span class="text-sm font-medium text-atly-ink-soft" data-comments-count>({{ $payload['comments_count'] }})</span></h2>
                </header>

                <div data-comments-list class="divide-y divide-atly-border">
                    @forelse ($payload['comments'] as $comment)
                        @include('tasks.partials.comment', ['comment' => $comment])
                    @empty
                        <div data-comments-empty class="px-6 py-10 text-center text-sm text-atly-ink-soft">No comments yet. {{ $canComment ? 'Be the first to leave one.' : '' }}</div>
                    @endforelse
                </div>

                @if ($canComment)
                    <form data-comment-form class="border-t border-atly-border px-5 py-4">
                        <label for="comment-body" class="sr-only">Add a comment</label>
                        <textarea
                            id="comment-body"
                            name="body"
                            rows="2"
                            maxlength="2000"
                            placeholder="Write a comment..."
                            class="w-full resize-none rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        ></textarea>
                        <div data-comment-error class="hidden mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"></div>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <p class="text-[11px] text-atly-ink-soft">Markdown isn't supported yet · max 2,000 chars</p>
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-atly-contrast-bg px-4 py-2 text-sm font-semibold text-atly-contrast-fg shadow-sm transition hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-60">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                                Post
                            </button>
                        </div>
                    </form>
                @else
                    <div class="border-t border-atly-border px-6 py-4 text-center text-xs text-atly-ink-soft">
                        @if ($viewerRole === MembershipRole::Guest)
                            You are a guest on this task and can only view comments.
                        @else
                            Sign in or be invited as a collaborator to leave comments.
                        @endif
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
            <x-dashboard.members
                :owner="$task->user"
                :members="$task->collaborators"
                :canInvite="$canManage"
                :inviteTarget="['type' => 'task', 'id' => $task->id, 'label' => $task->title]"
            />

            @if ($task->project)
                <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly">
                    <h3 class="text-[11px] font-semibold uppercase tracking-wide text-atly-ink-soft">Project</h3>
                    <a href="{{ route('projects.show', $task->project) }}" class="mt-2 flex items-center gap-2 text-sm font-medium text-atly-ink hover:text-atly-accent-strong">
                        <span @class([
                            'size-2.5 shrink-0 rounded-full',
                            'bg-'.$task->project->color.'-500' => $task->project->color,
                            'bg-atly-ink-soft/40' => ! $task->project->color,
                        ])></span>
                        <span class="truncate">{{ $task->project->name }}</span>
                    </a>
                    @if ($task->project->workspace)
                        <p class="mt-1 text-xs text-atly-ink-soft">in <a href="{{ route('workspaces.show', $task->project->workspace) }}" class="hover:text-atly-ink">{{ $task->project->workspace->name }}</a></p>
                    @endif
                </div>
            @endif

            <a href="{{ route('tasks.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-atly-border bg-atly-card px-4 py-2.5 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                Back to tasks
            </a>
        </aside>
    </div>

    @push('scripts')
        <script>
            window.atlyTaskShow = {
                csrf: @json(csrf_token()),
            };
        </script>
    @endpush
</x-layouts.dashboard>
