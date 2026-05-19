function debounce(fn, delay = 300) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function escapeHtml(value) {
    const el = document.createElement('div');
    el.textContent = value ?? '';

    return el.innerHTML;
}

function assigneeAvatarHtml(assignee) {
    if (!assignee) {
        return '';
    }

    if (assignee.avatar_url) {
        return `<img src="${assignee.avatar_url}" alt="${escapeHtml(assignee.name)}" class="size-9 rounded-full object-cover ring-2 ring-atly-card">`;
    }

    return `<span class="flex size-9 items-center justify-center rounded-full bg-atly-contrast-bg text-xs font-semibold text-atly-contrast-fg ring-2 ring-atly-card">${escapeHtml(assignee.initials)}</span>`;
}

function renderTaskAction(task, size = 'sm') {
    const buttonSize = size === 'sm' ? 'size-8' : 'size-9';
    const iconSize = size === 'sm' ? 'size-4' : 'size-5';
    const indicatorSize = size === 'sm' ? 'size-[1.125rem]' : 'size-5';

    if (task.status === 'pending') {
        return `<button type="button" data-start-task data-start-url="${task.start_url}" class="group/start relative flex ${buttonSize} shrink-0 items-center justify-center rounded-full text-atly-ink-soft transition hover:bg-sky-500/10 hover:text-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-atly-card" aria-label="Start task" title="Start task"><span class="flex ${iconSize} items-center justify-center rounded-full border-2 border-dashed border-atly-ink-soft/50 bg-atly-card transition-all duration-200 group-hover/start:border-sky-500 group-hover/start:bg-sky-500/10 group-hover/start:text-sky-600"><svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 0 1 9 0v3.75M8.25 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m0 0v4.875c0 .621.504 1.125 1.125 1.125h15.75c.621 0 1.125-.504 1.125-1.125V10.5m-16.5 0h16.5"/></svg></span></button>`;
    }

    const isCompleted = task.is_completed;

    return `<button type="button" data-toggle-complete data-complete-url="${task.complete_url}" data-is-completed="${isCompleted ? '1' : '0'}" class="group/check relative flex ${buttonSize} shrink-0 items-center justify-center rounded-full transition hover:bg-atly-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-atly-accent/50 focus-visible:ring-offset-2 focus-visible:ring-offset-atly-card" aria-label="${isCompleted ? 'Mark as incomplete' : 'Mark as complete'}" title="${isCompleted ? 'Mark as incomplete' : 'Mark as complete'}"><span data-complete-indicator class="${indicatorSize} flex items-center justify-center rounded-full border-2 transition-all duration-200 ${isCompleted ? 'border-emerald-500 bg-emerald-500 text-white shadow-sm shadow-emerald-500/30' : 'border-atly-border bg-atly-card group-hover/check:border-atly-accent group-hover/check:bg-atly-muted/30'}">${isCompleted ? '<svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5"/></svg>' : ''}</span></button>`;
}

function renderTaskPrimaryAction(task) {
    if (task.status === 'pending') {
        return `<button type="button" data-start-task data-start-url="${task.start_url}" class="flex w-full items-center gap-3 rounded-xl border border-sky-200 bg-sky-500/5 px-3 py-2.5 text-left text-sm transition hover:bg-sky-500/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/40"><span class="flex size-8 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-sky-400/60 bg-atly-card text-sky-600"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 0 1 9 0v3.75M8.25 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m0 0v4.875c0 .621.504 1.125 1.125 1.125h15.75c.621 0 1.125-.504 1.125-1.125V10.5m-16.5 0h16.5"/></svg></span><span class="font-medium text-atly-ink">Start task</span></button>`;
    }

    const isCompleted = task.is_completed;

    return `<button type="button" data-toggle-complete data-complete-url="${task.complete_url}" data-is-completed="${isCompleted ? '1' : '0'}" class="flex w-full items-center gap-3 rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5 text-left text-sm transition hover:bg-atly-muted/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-atly-accent/40"><span data-complete-indicator class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 transition-all ${isCompleted ? 'border-emerald-500 bg-emerald-500 text-white shadow-sm shadow-emerald-500/30' : 'border-atly-border bg-atly-card'}">${isCompleted ? '<svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5"/></svg>' : ''}</span><span class="font-medium text-atly-ink">${isCompleted ? 'Mark as incomplete' : 'Mark as complete'}</span></button>`;
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

    const isCompleted = task.is_completed;
    const titleClass = isCompleted ? 'text-atly-ink-soft line-through' : 'text-atly-ink';

    return `
        <div class="min-w-0 space-y-4">
            ${renderTaskPrimaryAction(task)}
            <div class="min-w-0">
                <h3 class="font-display text-lg font-bold ${titleClass}">${escapeHtml(task.title)}</h3>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${task.status_class}">${escapeHtml(task.status_label)}</span>
                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${task.priority_class}">${escapeHtml(task.priority_label)}</span>
                </div>
            </div>
            ${task.assignee ? `<div class="flex items-center gap-2" title="${escapeHtml(task.assignee.name)}">${assigneeAvatarHtml(task.assignee)}<span class="min-w-0 truncate text-sm font-medium text-atly-ink">${escapeHtml(task.assignee.name)}</span></div>` : ''}
            <div>
                <h4 class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-atly-ink-soft">Description</h4>
                ${task.description
        ? `<div class="task-detail-description max-h-36 w-full min-w-0 overflow-x-hidden overflow-y-auto break-words whitespace-pre-wrap rounded-lg border border-atly-border/60 bg-atly-muted/20 px-3 py-2.5 text-sm leading-relaxed text-atly-ink-soft">${escapeHtml(task.description)}</div>`
        : '<p class="text-sm text-atly-ink-soft/80">No description.</p>'}
            </div>
            <dl class="flex flex-wrap gap-x-6 gap-y-1 border-y border-atly-border py-2.5 text-xs">
                <div class="flex items-baseline gap-2"><dt class="text-atly-ink-soft">Start</dt><dd class="font-medium text-atly-ink">${task.start_date ?? '—'}</dd></div>
                <div class="flex items-baseline gap-2"><dt class="text-atly-ink-soft">Due</dt><dd class="font-medium ${task.is_overdue ? 'text-rose-600' : 'text-atly-ink'}">${task.due_date ?? '—'}</dd></div>
            </dl>
            ${attachments ? `<div class="space-y-2"><p class="text-xs font-semibold uppercase tracking-wide text-atly-ink-soft">Attachments</p>${attachments}</div>` : ''}
            <div class="flex flex-wrap gap-2 pt-1">
                <a href="${task.edit_url}" class="inline-flex rounded-xl bg-atly-contrast-bg px-4 py-2 text-sm font-semibold text-atly-contrast-fg">Edit</a>
                <button type="button" data-delete-task="${task.id}" data-delete-url="${task.delete_url}" class="inline-flex rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">Delete</button>
            </div>
        </div>
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


    function updateTaskRowUI(task) {
        const row = document.querySelector(`.task-row[data-task-id="${task.id}"]`);

        if (!row) {
            return;
        }

        const actionSlot = row.querySelector('[data-task-action]');

        if (actionSlot) {
            actionSlot.innerHTML = renderTaskAction(task, 'sm');
        }

        const title = row.querySelector('[data-task-title]');

        if (title) {
            const completed = task.is_completed;
            title.classList.toggle('line-through', completed);
            title.classList.toggle('text-atly-ink-soft', completed);
            title.classList.toggle('text-atly-ink', !completed);
            title.classList.toggle('group-hover:text-atly-accent-strong', !completed);
        }

        const statusBadge = row.querySelector('[data-task-status-badge]');

        if (statusBadge) {
            statusBadge.textContent = task.status_label;
            statusBadge.className = `inline-flex whitespace-nowrap rounded-md px-2 py-0.5 text-xs font-semibold sm:flex sm:w-full sm:justify-center ${task.status_class}`;
            statusBadge.setAttribute('data-task-status-badge', '');
        }
    }

    function applyTaskToDetail(task) {
        const html = renderTaskDetail(task);
        const panelContent = document.getElementById('task-detail-content');
        const drawerContent = document.getElementById('task-detail-drawer-content');

        if (panelContent && !panelContent.classList.contains('hidden')) {
            panelContent.innerHTML = html;
        }

        if (drawerContent) {
            drawerContent.innerHTML = html;
        }
    }

    async function patchTask(url) {
        return fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': config.csrf,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });
    }

    async function startTask(button) {
        const url = button.dataset.startUrl;

        if (!url) {
            return;
        }

        const response = await patchTask(url);

        if (!response.ok) {
            return;
        }

        const { task } = await response.json();
        updateTaskRowUI(task);

        if (String(activeTaskId) === String(task.id)) {
            applyTaskToDetail(task);
        }
    }

    async function toggleTaskComplete(button) {
        const url = button.dataset.completeUrl;

        if (!url) {
            return;
        }

        const response = await patchTask(url);

        if (!response.ok) {
            return;
        }

        const { task } = await response.json();
        updateTaskRowUI(task);

        if (String(activeTaskId) === String(task.id)) {
            applyTaskToDetail(task);
        }
    }
    function highlightActiveTask(taskId) {
        document.querySelectorAll('.task-row').forEach((item) => {
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

    if (listWrapper && !listWrapper.dataset.eventsBound) {
        listWrapper.dataset.eventsBound = '1';
        listWrapper.addEventListener('click', (event) => {
            const startButton = event.target.closest('[data-start-task]');

            if (startButton) {
                event.stopPropagation();
                startTask(startButton);

                return;
            }

            const completeButton = event.target.closest('[data-toggle-complete]');

            if (completeButton) {
                event.stopPropagation();
                toggleTaskComplete(completeButton);

                return;
            }

            const selectButton = event.target.closest('[data-task-select]');

            if (selectButton) {
                const row = selectButton.closest('.task-row');

                if (row?.dataset.taskId) {
                    showTaskDetail(row.dataset.taskId);
                }
            }
        });
    }

    function bindListEvents() {
        listWrapper?.querySelectorAll('#tasks-pagination a[href]').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                refreshList(link.href).then(() => {
                    listWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
        });
    }

    document.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('[data-delete-task]');

        if (deleteButton) {
            event.preventDefault();
            deleteTask(deleteButton);
            return;
        }

        const startButton = event.target.closest('[data-start-task]');

        if (startButton && !startButton.closest('#tasks-list')) {
            event.preventDefault();
            startTask(startButton);

            return;
        }

        const completeButton = event.target.closest('[data-toggle-complete]');

        if (completeButton && !completeButton.closest('#tasks-list')) {
            event.preventDefault();
            toggleTaskComplete(completeButton);
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