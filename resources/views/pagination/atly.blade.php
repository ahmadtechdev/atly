@if ($paginator->hasPages() || $paginator->total() > 0)
    <nav id="tasks-pagination" role="navigation" aria-label="Tasks pagination" class="flex flex-col gap-3 border-t border-atly-border px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-center text-xs text-atly-ink-soft sm:text-left">
            Showing
            <span class="font-medium text-atly-ink">{{ $paginator->firstItem() ?? 0 }}</span>
            –
            <span class="font-medium text-atly-ink">{{ $paginator->lastItem() ?? 0 }}</span>
            of
            <span class="font-medium text-atly-ink">{{ $paginator->total() }}</span>
        </p>

        @if ($paginator->hasPages())
            <div class="flex items-center justify-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg text-atly-ink-soft/40" aria-disabled="true">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex size-9 items-center justify-center rounded-lg border border-atly-border bg-atly-surface text-atly-ink transition hover:bg-atly-muted/50" aria-label="Previous page">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-1 text-atly-ink-soft">…</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex min-w-9 items-center justify-center rounded-lg bg-atly-contrast-bg px-2.5 py-2 text-sm font-semibold text-atly-contrast-fg" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex min-w-9 items-center justify-center rounded-lg border border-transparent px-2.5 py-2 text-sm font-medium text-atly-ink-soft transition hover:border-atly-border hover:bg-atly-muted/40 hover:text-atly-ink">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex size-9 items-center justify-center rounded-lg border border-atly-border bg-atly-surface text-atly-ink transition hover:bg-atly-muted/50" aria-label="Next page">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @else
                    <span class="inline-flex size-9 cursor-not-allowed items-center justify-center rounded-lg text-atly-ink-soft/40" aria-disabled="true">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </span>
                @endif
            </div>
        @endif
    </nav>
@endif
