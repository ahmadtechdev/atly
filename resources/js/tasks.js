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

function renderTaskTimeChip(task, size = 'sm') {
    const tt = task.time_tracking || {};
    const sizeClasses = size === 'lg' ? 'px-3 py-1.5 text-xs' : 'px-2 py-0.5 text-[10px]';
    const iconSize = size === 'lg' ? 'size-3.5' : 'size-3';

    if (tt.is_running && tt.stop_url) {
        return `<button type="button" data-track-toggle data-track-action="stop" data-track-url="${tt.stop_url}" data-task-time-running data-started-at-ms="${tt.running_started_at_unix_ms}" data-base-seconds="${tt.base_seconds}" class="group/track inline-flex items-center gap-1 rounded-full bg-emerald-100 ring-1 ring-emerald-300 font-medium text-emerald-800 transition hover:bg-emerald-200 hover:ring-emerald-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-800 ${sizeClasses}" title="Stop tracking" aria-label="Stop tracking"><span class="relative inline-flex size-1.5"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-75"></span><span class="relative inline-flex size-1.5 rounded-full bg-emerald-500"></span></span><span data-task-time-label class="tabular-nums">${escapeHtml(tt.total_label || '0m')}</span><svg class="${iconSize} opacity-70 group-hover/track:opacity-100" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="6" width="12" height="12" rx="1.5"/></svg></button>`;
    }

    if (tt.can_track && tt.start_url) {
        const label = tt.has_entries ? tt.total_label : 'Track';
        const titleText = tt.has_entries ? 'Resume tracking' : 'Start tracking';

        return `<button type="button" data-track-toggle data-track-action="start" data-track-url="${tt.start_url}" data-task-id="${task.id}" class="group/track inline-flex items-center gap-1 rounded-full border border-dashed border-atly-border bg-transparent font-medium text-atly-ink-soft transition hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:hover:bg-emerald-950/30 dark:hover:text-emerald-200 ${sizeClasses}" title="${titleText}" aria-label="${titleText}"><svg class="${iconSize}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.14v13.72c0 .79.87 1.27 1.54.84l10.79-6.86c.62-.39.62-1.29 0-1.68L9.54 4.3C8.87 3.87 8 4.35 8 5.14z"/></svg><span class="tabular-nums">${escapeHtml(label)}</span></button>`;
    }

    if (tt.has_entries) {
        return `<span class="inline-flex items-center gap-1 rounded-full bg-atly-muted/60 font-medium text-atly-ink-soft ${sizeClasses}" title="Time tracked"><svg class="${iconSize}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><span class="tabular-nums">${escapeHtml(tt.total_label)}</span></span>`;
    }

    return `<span class="inline-flex items-center gap-1 italic text-atly-ink-soft/70 ${sizeClasses}" title="No time tracked">Not tracked</span>`;
}

function renderTimeTrackingDetail(task) {
    const tt = task.time_tracking || {};

    if (!tt.can_track && !tt.has_entries) {
        return '';
    }

    return `
        <div class="space-y-1.5">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-atly-ink-soft">Time tracking</p>
            <div data-task-time-chip data-task-id="${task.id}">${renderTaskTimeChip(task, 'lg')}</div>
        </div>
    `;
}

function renderProjectAttacher(task) {
    const searchUrl = window.atlyProjects?.searchUrl || '';
    const updateUrl = task.update_project_url || '';

    if (!searchUrl || !updateUrl) {
        return '';
    }

    const project = task.project || null;
    const currentAttrs = project
        ? `data-current-id="${project.id}" data-current-label="${escapeHtml(project.name)}" data-current-color="${escapeHtml(project.color || '')}"`
        : '';

    return `
        <div class="space-y-1.5">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-atly-ink-soft">Project</p>
            <div data-inline-attacher
                 data-update-url="${updateUrl}"
                 data-search-url="${searchUrl}"
                 data-field-name="project_id"
                 data-entity-label="project"
                 ${currentAttrs}></div>
        </div>
    `;
}

function renderPersonChip(person, roleLabel = null, roleClass = null) {
    const avatar = person.avatar_url
        ? `<img src="${person.avatar_url}" alt="${escapeHtml(person.name)}" class="size-7 rounded-full object-cover">`
        : `<span class="flex size-7 items-center justify-center rounded-full bg-atly-contrast-bg text-[10px] font-semibold text-atly-contrast-fg">${escapeHtml(person.initials || '?')}</span>`;
    const role = roleLabel
        ? `<span class="inline-flex rounded-md px-1.5 py-0.5 text-[10px] font-semibold ${roleClass || 'bg-atly-muted text-atly-ink-soft'}">${escapeHtml(roleLabel)}</span>`
        : '';

    return `
        <div class="flex items-center gap-2 rounded-lg border border-atly-border bg-atly-surface px-2.5 py-1.5" title="${escapeHtml(person.name)}">
            ${avatar}
            <span class="min-w-0 flex-1 truncate text-xs font-medium text-atly-ink">${escapeHtml(person.name)}</span>
            ${role}
        </div>
    `;
}

function renderCollaborators(task) {
    const owner = task.owner || task.assignee || null;
    const collaborators = Array.isArray(task.collaborators) ? task.collaborators : [];

    if (!owner && collaborators.length === 0) {
        return '';
    }

    const ownerHtml = owner ? renderPersonChip(owner, 'Owner', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200') : '';
    const collabHtml = collaborators.map((c) => renderPersonChip(c, c.role_label, c.role_class)).join('');

    return `
        <div class="space-y-1.5">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-atly-ink-soft">Collaborators (${collaborators.length + (owner ? 1 : 0)})</p>
            <div class="flex flex-wrap gap-2">
                ${ownerHtml}
                ${collabHtml}
            </div>
        </div>
    `;
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
    const viewer = task.viewer || {};

    const editBtn = viewer.can_edit
        ? `<a href="${task.edit_url}" class="inline-flex rounded-xl bg-atly-contrast-bg px-4 py-2 text-sm font-semibold text-atly-contrast-fg">Edit</a>`
        : '';
    const inviteBtn = viewer.can_invite
        ? `<button type="button" data-open-invite-modal data-invitable-type="task" data-invitable-id="${task.id}" data-invitable-label="${escapeHtml(task.title)}" class="inline-flex items-center gap-1.5 rounded-xl border border-atly-border bg-atly-card px-4 py-2 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                Invite
            </button>`
        : '';
    const deleteBtn = viewer.can_delete
        ? `<button type="button" data-delete-task="${task.id}" data-delete-url="${task.delete_url}" class="inline-flex rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">Delete</button>`
        : '';
    const projectAttacher = viewer.can_edit ? renderProjectAttacher(task) : '';
    const primaryAction = viewer.can_complete ? renderTaskPrimaryAction(task) : '';

    const viewerBadge = viewer.role_label && !viewer.is_owner
        ? `<span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${viewer.role_class || ''}">You · ${escapeHtml(viewer.role_label)}</span>`
        : '';

    return `
        <div class="min-w-0 space-y-4">
            ${primaryAction}
            <div class="min-w-0">
                <h3 class="font-display text-lg font-bold ${titleClass}">${escapeHtml(task.title)}</h3>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${task.status_class}">${escapeHtml(task.status_label)}</span>
                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${task.priority_class}">${escapeHtml(task.priority_label)}</span>
                    ${viewerBadge}
                </div>
            </div>
            ${renderCollaborators(task)}
            ${projectAttacher}
            ${renderTimeTrackingDetail(task)}
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
                ${editBtn}${inviteBtn}${deleteBtn}
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

    const urlParams = new URLSearchParams(window.location.search);
    const projectId = urlParams.get('project_id');
    if (projectId) {
        params.set('project_id', projectId);
    }
    const workspaceId = urlParams.get('workspace_id');
    if (workspaceId) {
        params.set('workspace_id', workspaceId);
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

    const projectPicker = () => modal?.querySelector('[data-searchable-picker][data-name="project_id"]');

    const openModal = (prefill = {}) => {
        modal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        if (prefill.projectId) {
            window.atlySetSearchablePicker?.(projectPicker(), {
                id: prefill.projectId,
                label: prefill.projectLabel || '',
                color: prefill.projectColor || '',
            });
        }
    };

    const closeModal = () => {
        modal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        quickForm?.reset();
        window.atlySetSearchablePicker?.(projectPicker(), { id: '', label: '', color: '' });
        document.getElementById('task-modal-errors')?.classList.add('hidden');
    };

    document.querySelectorAll('[data-open-task-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            openModal({
                projectId: button.dataset.prefillProjectId,
                projectLabel: button.dataset.prefillProjectLabel,
                projectColor: button.dataset.prefillProjectColor,
            });
        });
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
            window.atlyInitAttachers?.(listWrapper);
            window.atlyTickTaskTimers?.();

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
            window.atlyInitAttachers?.(panelContent);
        }

        if (drawer && drawerContent) {
            drawerContent.innerHTML = html;

            const isMobile = window.matchMedia('(max-width: 1279px)').matches;

            if (isMobile) {
                drawer.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                drawer.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            window.atlyInitAttachers?.(drawerContent);
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

        const timeCell = row.querySelector('[data-task-time-cell]');

        if (timeCell) {
            timeCell.innerHTML = renderTaskTimeChip(task, 'sm');
            window.atlyTickTaskTimers?.();
        }
    }

    function applyTaskToDetail(task) {
        const html = renderTaskDetail(task);
        const panelContent = document.getElementById('task-detail-content');
        const drawerContent = document.getElementById('task-detail-drawer-content');

        if (panelContent && !panelContent.classList.contains('hidden')) {
            panelContent.innerHTML = html;
            window.atlyInitAttachers?.(panelContent);
        }

        if (drawerContent) {
            drawerContent.innerHTML = html;
            window.atlyInitAttachers?.(drawerContent);
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

    async function toggleTracking(button) {
        const url = button.dataset.trackUrl;
        const action = button.dataset.trackAction;

        if (!url) {
            return;
        }

        button.setAttribute('disabled', 'disabled');
        button.classList.add('opacity-60');

        try {
            let response;

            if (action === 'start') {
                const formData = new FormData();
                formData.append('task_id', button.dataset.taskId || '');
                response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': config.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    body: formData,
                });
            } else {
                response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': config.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                });
            }

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (data.task && String(activeTaskId) === String(data.task.id)) {
                applyTaskToDetail(data.task);
            }

            await refreshList();
        } finally {
            button.removeAttribute?.('disabled');
            button.classList?.remove('opacity-60');
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
            if (event.target.closest('[data-inline-attacher]')) {
                return;
            }

            const trackButton = event.target.closest('[data-track-toggle]');

            if (trackButton) {
                event.stopPropagation();
                event.preventDefault();
                toggleTracking(trackButton);

                return;
            }

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

        listWrapper.addEventListener('keydown', (event) => {
            if (event.target.closest('[data-inline-attacher]') || event.target.closest('[data-track-toggle]')) {
                return;
            }

            const selectTarget = event.target.closest('[data-task-select]');

            if (!selectTarget || (event.key !== 'Enter' && event.key !== ' ')) {
                return;
            }

            event.preventDefault();
            const row = selectTarget.closest('.task-row');

            if (row?.dataset.taskId) {
                showTaskDetail(row.dataset.taskId);
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

            return;
        }

        const trackButton = event.target.closest('[data-track-toggle]');

        if (trackButton && !trackButton.closest('#tasks-list')) {
            event.preventDefault();
            toggleTracking(trackButton);
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