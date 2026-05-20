<article class="flex gap-3 px-6 py-4" data-comment-id="{{ $comment['id'] }}">
    @if (! empty($comment['author']['avatar_url']))
        <img src="{{ $comment['author']['avatar_url'] }}" alt="{{ $comment['author']['name'] }}" class="size-9 shrink-0 rounded-full object-cover">
    @else
        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-atly-muted text-xs font-semibold text-atly-ink">{{ $comment['author']['initials'] ?? '?' }}</span>
    @endif

    <div class="min-w-0 flex-1">
        <header class="flex flex-wrap items-baseline gap-2">
            <p class="text-sm font-semibold text-atly-ink">{{ $comment['author']['name'] }}</p>
            <p class="text-[11px] text-atly-ink-soft">{{ $comment['created_at_label'] }}</p>
            @if (! empty($comment['can_delete']))
                <button
                    type="button"
                    data-delete-comment
                    data-delete-url="{{ $comment['delete_url'] }}"
                    class="ml-auto text-[11px] font-medium text-rose-600 hover:underline"
                >Delete</button>
            @endif
        </header>
        <p class="mt-1 text-sm leading-relaxed text-atly-ink whitespace-pre-wrap break-words">{{ $comment['body'] }}</p>
    </div>
</article>
