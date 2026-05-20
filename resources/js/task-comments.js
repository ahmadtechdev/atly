function escapeHtml(value) {
    const el = document.createElement('div');
    el.textContent = value ?? '';

    return el.innerHTML;
}

function renderComment(comment) {
    const avatar = comment.author?.avatar_url
        ? `<img src="${comment.author.avatar_url}" alt="${escapeHtml(comment.author.name)}" class="size-9 shrink-0 rounded-full object-cover">`
        : `<span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-atly-muted text-xs font-semibold text-atly-ink">${escapeHtml(comment.author?.initials || '?')}</span>`;

    return `
        <article class="flex gap-3 px-6 py-4" data-comment-id="${comment.id}">
            ${avatar}
            <div class="min-w-0 flex-1">
                <header class="flex flex-wrap items-baseline gap-2">
                    <p class="text-sm font-semibold text-atly-ink">${escapeHtml(comment.author?.name || 'Anonymous')}</p>
                    <p class="text-[11px] text-atly-ink-soft">${escapeHtml(comment.created_at_label || '')}</p>
                    <button type="button" data-delete-comment data-delete-url="${comment.delete_url}" class="ml-auto text-[11px] font-medium text-rose-600 hover:underline">Delete</button>
                </header>
                <p class="mt-1 text-sm leading-relaxed text-atly-ink whitespace-pre-wrap break-words">${escapeHtml(comment.body)}</p>
            </div>
        </article>
    `;
}

export function initTaskComments() {
    document.querySelectorAll('[data-task-comments]').forEach((section) => {
        if (section.dataset.commentsBound === '1') {
            return;
        }

        section.dataset.commentsBound = '1';

        const form = section.querySelector('[data-comment-form]');
        const list = section.querySelector('[data-comments-list]');
        const countEl = section.querySelector('[data-comments-count]');
        const url = section.dataset.commentsUrl;
        const csrf = section.dataset.csrf;

        const setCount = (delta) => {
            if (!countEl) {
                return;
            }

            const match = countEl.textContent.match(/\((\d+)\)/);
            const current = match ? parseInt(match[1], 10) : 0;
            countEl.textContent = `(${Math.max(0, current + delta)})`;
        };

        const removeEmptyState = () => {
            list?.querySelector('[data-comments-empty]')?.remove();
        };

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const textarea = form.querySelector('textarea[name="body"]');
            const errorEl = form.querySelector('[data-comment-error]');
            const submitBtn = form.querySelector('[type="submit"]');
            const body = (textarea?.value || '').trim();

            errorEl?.classList.add('hidden');

            if (!body) {
                if (errorEl) {
                    errorEl.textContent = 'Comment cannot be empty.';
                    errorEl.classList.remove('hidden');
                }

                return;
            }

            submitBtn?.setAttribute('disabled', 'disabled');

            try {
                const formData = new FormData();
                formData.append('body', body);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    body: formData,
                });

                if (response.status === 422) {
                    const data = await response.json();
                    if (errorEl) {
                        errorEl.textContent = Object.values(data.errors || {}).flat().join(' ') || 'Please check your comment.';
                        errorEl.classList.remove('hidden');
                    }

                    return;
                }

                if (response.status === 403) {
                    if (errorEl) {
                        errorEl.textContent = 'You no longer have permission to comment on this task.';
                        errorEl.classList.remove('hidden');
                    }

                    return;
                }

                if (!response.ok) {
                    if (errorEl) {
                        errorEl.textContent = 'Something went wrong. Try again.';
                        errorEl.classList.remove('hidden');
                    }

                    return;
                }

                const { comment } = await response.json();

                removeEmptyState();
                list?.insertAdjacentHTML('beforeend', renderComment(comment));
                setCount(1);
                if (textarea) {
                    textarea.value = '';
                }
            } finally {
                submitBtn?.removeAttribute('disabled');
            }
        });

        section.addEventListener('click', async (event) => {
            const deleteBtn = event.target.closest('[data-delete-comment]');

            if (!deleteBtn) {
                return;
            }

            if (!confirm('Delete this comment?')) {
                return;
            }

            const deleteUrl = deleteBtn.dataset.deleteUrl;
            const response = await fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: new URLSearchParams({ _method: 'DELETE' }),
            });

            if (!response.ok) {
                return;
            }

            const node = deleteBtn.closest('[data-comment-id]');
            node?.remove();
            setCount(-1);
        });
    });
}
