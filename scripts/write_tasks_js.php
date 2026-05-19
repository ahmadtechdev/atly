<?php

$content = <<<'JS'
function debounce(fn, delay = 300) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function escapeHtml(value) {
    const el = document.createElement('TAGNAME');
    el.textContent = value ?? '';

    return el.innerHTML;
}

function renderTaskDetail(task) {
    const attachments = (task.attachments ?? [])
        .map((file) => {
            if (file.is_image) {
                return `<a href="${file.url}" target="_blank" rel="noopener" class="block overflow-hidden rounded-lg border border-atly-border"><img src="${file.url}" alt="${escapeHtml(file.name)}" class="max-h-40 w-full object-cover"></a>`;
            }

            return `<a href="${file.url}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-lg border border-atly-border px-3 py-2 text-sm text-atly-ink hover:bg-atly-muted/40"><span class="truncate">${escapeHtml(file.name)}</span><span class="text-xs text-atly-ink-soft">${file.size}</span></a>`;
        })
        .join('');

    return `
        WRAPPER_OPEN class="space-y-5">
            INNER_OPEN class="flex flex-wrap items-start justify-between gap-3">
                <h3 class="font-display text-xl font-bold text-atly-ink">${escapeHtml(task.title)}</h3>
                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${task.priority_class}">${escapeHtml(task.priority_label)}</span>
            INNER_CLOSE
            INNER_OPEN class="flex flex-wrap gap-2">
                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${task.status_class}">${escapeHtml(task.status_label)}</span>
            INNER_CLOSE
            ${task.description ? `<p class="text-sm leading-relaxed text-atly-ink-soft">${escapeHtml(task.description)}</p>` : '<p class="text-sm text-atly-ink-soft">No description.</p>'}
            <dl class="grid grid-cols-2 gap-3 text-sm">
                INNER_OPEN class="rounded-lg bg-atly-muted/30 p-3"><dt class="text-atly-ink-soft">Start</dt><dd class="mt-1 font-medium text-atly-ink">${task.start_date ?? '—'}</dd>INNER_CLOSE
                INNER_OPEN class="rounded-lg bg-atly-muted/30 p-3"><dt class="text-atly-ink-soft">Due</dt><dd class="mt-1 font-medium ${task.is_overdue ? 'text-rose-600' : 'text-atly-ink'}">${task.due_date ?? '—'}</dd>INNER_CLOSE
            </dl>
            ${attachments ? `INNER_OPEN class="space-y-2"><p class="text-xs font-semibold uppercase tracking-wide text-atly-ink-soft">Attachments</p>${attachments}INNER_CLOSE` : ''}
            INNER_OPEN class="flex flex-wrap gap-2 border-t border-atly-border pt-4">
                <a href="${task.edit_url}" class="inline-flex rounded-xl bg-atly-contrast-bg px-4 py-2 text-sm font-semibold text-atly-contrast-fg">Edit</a>
                <button type="button" data-delete-task="${task.id}" data-delete-url="${task.delete_url}" class="inline-flex rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">Delete</button>
            INNER_CLOSE
        WRAPPER_CLOSE
    `;
}

function getFilterParams() {
    const search = document.getElementById('task-search');
    const status = document.getElementById('task-filter-status');
    const priority = document.getElementById('task-filter-priority');

    const params = new URLSearchParams();

    if (search?.value.trim()) {
        params.set('search', search.value.trim());
    }

    if (status?.value) {
        params.set('status', status.value);
    }

    if (priority?.value) {
        params.set('priority', priority.value);
    }

    return params;
}

export function initTasks() {
    const config = window.atlyTasks;

    if (!config) {
        return;
    }

    const modal = document.getElementById('task-quick-modal');
    const quickForm = document.getElementById('task-quick-form');
    const listWrapper = document.getElementById('tasks-list-wrapper');
    const searchInput = document.getElementById('task-search');
    const statusFilter = document.getElementById('task-filter-status');
    const priorityFilter = document.getElementById('task-filter-priority');

    let activeTaskId = null;

    const openModal = () => {
        modal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        modal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        quickForm?.reset();
        document.getElementById('task-modal-errors')?.classList.add('hidden');
    };

    document.querySelectorAll('[data-open-task-modal]').forEach((button) => {
        button.addEventListener('click', openModal);
    });

    document.querySelectorAll('[data-close-task-modal]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    const refreshList = async (url = null) => {
        if (!listWrapper) {
            return;
        }

        const params = getFilterParams();
        const fetchUrl = url ?? `${config.indexUrl}?${params.toString()}`;

        listWrapper.classList.add('opacity-60', 'pointer-events-none');

        try {
            const response = await fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            listWrapper.innerHTML = data.html;
            bindListEvents();

            if (activeTaskId) {
                highlightActiveTask(activeTaskId);
            }
        } finally {
            listWrapper.classList.remove('opacity-60', 'pointer-events-none');
        }
    };

    const debouncedRefresh = debounce(() => refreshList(), 300);

    searchInput?.addEventListener('input', debouncedRefresh);
    statusFilter?.addEventListener('change', () => refreshList());
    priorityFilter?.addEventListener('change', () => refreshList());

    const showTaskDetail = async (taskId) => {
        activeTaskId = taskId;
        highlightActiveTask(taskId);

        const response = await fetch(`${config.indexUrl}/${taskId}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            return;
        }

        const { task } = await response.json();
        const html = renderTaskDetail(task);

        const panelEmpty = document.getElementById('task-detail-empty');
        const panelContent = document.getElementById('task-detail-content');
        const drawer = document.getElementById('task-detail-drawer');
        const drawerContent = document.getElementById('task-detail-drawer-content');

        if (panelEmpty && panelContent) {
            panelEmpty.classList.add('hidden');
            panelContent.classList.remove('hidden');
            panelContent.innerHTML = html;
        }

        if (drawer && drawerContent) {
            drawerContent.innerHTML = html;
            drawer.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    };

    function highlightActiveTask(taskId) {
        document.querySelectorAll('.task-item').forEach((item) => {
            const isActive = item.dataset.taskId === String(taskId);
            item.classList.toggle('bg-atly-muted/50', isActive);
            item.classList.toggle('ring-1', isActive);
            item.classList.toggle('ring-inset', isActive);
            item.classList.toggle('ring-atly-accent/40', isActive);
        });
    }

    const closeDetailDrawer = () => {
        document.getElementById('task-detail-drawer')?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('[data-close-task-detail]').forEach((el) => {
        el.addEventListener('click', closeDetailDrawer);
    });

    async function deleteTask(button) {
        if (!confirm('Delete this task?')) {
            return;
        }

        const url = button.dataset.deleteUrl;

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': config.csrf,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body: new URLSearchParams({ _method: 'DELETE' }),
        });

        if (!response.ok) {
            return;
        }

        activeTaskId = null;
        document.getElementById('task-detail-empty')?.classList.remove('hidden');
        document.getElementById('task-detail-content')?.classList.add('hidden');
        closeDetailDrawer();
        await refreshList();
    }

    function bindListEvents() {
        document.querySelectorAll('.task-item[data-task-id]').forEach((item) => {
            item.addEventListener('click', () => showTaskDetail(item.dataset.taskId));
        });

        listWrapper?.querySelectorAll('#tasks-pagination a').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                refreshList(link.href);
            });
        });
    }

    document.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('[data-delete-task]');

        if (deleteButton) {
            event.preventDefault();
            deleteTask(deleteButton);
        }
    });

    quickForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const errorsEl = document.getElementById('task-modal-errors');
        const submitButton = quickForm.querySelector('[type="submit"]');

        submitButton?.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch(config.storeUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: new FormData(quickForm),
            });

            if (response.status === 422) {
                const data = await response.json();
                const messages = Object.values(data.errors ?? {}).flat().join(' ');

                if (errorsEl) {
                    errorsEl.textContent = messages || 'Please check the form.';
                    errorsEl.classList.remove('hidden');
                }

                return;
            }

            if (!response.ok) {
                return;
            }

            closeModal();

            if (listWrapper) {
                await refreshList();
            } else if (config.tasksUrl) {
                window.location.href = config.tasksUrl;
            }
        } finally {
            submitButton?.removeAttribute('disabled');
        }
    });

    bindListEvents();
}
JS;

$content = str_replace(
    ['TAGNAME', 'WRAPPER_OPEN', 'WRAPPER_CLOSE', 'INNER_OPEN', 'INNER_CLOSE'],
    ['div', '<div', '</motion-div>', '<div', '</motion-div>'],
    $content
);

$content = str_replace('motion-div', 'div', $content);

file_put_contents(__DIR__.'/../resources/js/tasks.js', $content);

echo "Written tasks.js\n";
