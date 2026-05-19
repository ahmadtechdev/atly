<?php

$path = __DIR__.'/../resources/js/tasks.js';
$content = file_get_contents($path);

$start = strpos($content, 'function renderTaskDetail(task)');
$end = strpos($content, 'function getFilterParams()', $start);

if ($start === false || $end === false) {
    fwrite(STDERR, "Could not find markers\n");
    exit(1);
}

$newFunction = <<<'JS'
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
        <div class="space-y-4">
            <button type="button" data-toggle-complete data-complete-url="${task.complete_url}" data-is-completed="${isCompleted ? '1' : '0'}" class="flex w-full items-center gap-3 rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5 text-left text-sm transition hover:bg-atly-muted/40">
                <span class="flex size-5 shrink-0 items-center justify-center rounded border-2 ${isCompleted ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-atly-ink-soft/40'}">${isCompleted ? '<svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5"/></svg>' : ''}</span>
                <span class="font-medium text-atly-ink">${isCompleted ? 'Mark as incomplete' : 'Mark as complete'}</span>
            </button>
            <div class="min-w-0">
                <h3 class="font-display text-lg font-bold ${titleClass}">${escapeHtml(task.title)}</h3>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${task.status_class}">${escapeHtml(task.status_label)}</span>
                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ${task.priority_class}">${escapeHtml(task.priority_label)}</span>
                </div>
            </div>
            ${task.assignee ? `<motion-div class="flex items-center gap-2.5"><span class="text-xs font-medium uppercase tracking-wide text-atly-ink-soft">Assignee</span>${assigneeAvatarHtml(task.assignee)}<span class="text-sm font-medium text-atly-ink">${escapeHtml(task.assignee.name)}</span></motion-div>` : ''}
            ${task.description ? `<p class="text-sm leading-relaxed text-atly-ink-soft">${escapeHtml(task.description)}</p>` : '<p class="text-sm text-atly-ink-soft">No description.</p>'}
            <dl class="flex flex-wrap gap-x-6 gap-y-1 border-y border-atly-border py-2.5 text-xs">
                <motion-div class="flex items-baseline gap-2"><dt class="text-atly-ink-soft">Start</dt><dd class="font-medium text-atly-ink">${task.start_date ?? '—'}</dd></motion-div>
                <motion-div class="flex items-baseline gap-2"><dt class="text-atly-ink-soft">Due</dt><dd class="font-medium ${task.is_overdue ? 'text-rose-600' : 'text-atly-ink'}">${task.due_date ?? '—'}</dd></motion-div>
            </dl>
            ${attachments ? `<motion-div class="space-y-2"><p class="text-xs font-semibold uppercase tracking-wide text-atly-ink-soft">Attachments</p>${attachments}</motion-div>` : ''}
            <motion-div class="flex flex-wrap gap-2 pt-1">
                <a href="${task.edit_url}" class="inline-flex rounded-xl bg-atly-contrast-bg px-4 py-2 text-sm font-semibold text-atly-contrast-fg">Edit</a>
                <button type="button" data-delete-task="${task.id}" data-delete-url="${task.delete_url}" class="inline-flex rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">Delete</button>
            </motion-div>
        </motion-div>
    `.replaceAll('motion-div', 'div');
}

JS;

$newFunction = str_replace('motion-div', 'div', $newFunction);

$content = substr($content, 0, $start).$newFunction.substr($content, $end);

$handlers = <<<'JS'

    function updateCompleteButton(button, isCompleted) {
        const box = button.querySelector('span');
        if (!box) {
            return;
        }

        button.dataset.isCompleted = isCompleted ? '1' : '0';
        button.setAttribute('aria-label', isCompleted ? 'Mark as incomplete' : 'Mark as complete');
        button.title = isCompleted ? 'Mark as incomplete' : 'Mark as complete';

        if (isCompleted) {
            box.className = 'flex size-4 items-center justify-center rounded border-2 border-emerald-500 bg-emerald-500 text-white';
            box.innerHTML = '<svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5"/></svg>';
        } else {
            box.className = 'flex size-4 items-center justify-center rounded border-2 border-atly-ink-soft/40 bg-transparent';
            box.innerHTML = '';
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

    async function toggleTaskComplete(button) {
        const url = button.dataset.completeUrl;

        if (!url) {
            return;
        }

        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': config.csrf,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            return;
        }

        const { task } = await response.json();
        const row = document.querySelector(`.task-row[data-task-id="${task.id}"]`);

        if (row) {
            const rowButton = row.querySelector('[data-toggle-complete]');
            if (rowButton) {
                updateCompleteButton(rowButton, task.is_completed);
            }

            const title = row.querySelector('[data-task-select] span.truncate, [data-task-select] .truncate');
            if (title) {
                title.classList.toggle('line-through', task.is_completed);
                title.classList.toggle('text-atly-ink-soft', task.is_completed);
                title.classList.toggle('text-atly-ink', !task.is_completed);
            }
        }

        if (String(activeTaskId) === String(task.id)) {
            applyTaskToDetail(task);
        }

        await refreshList();
    }

JS;

if (! str_contains($content, 'function updateCompleteButton')) {
    $insertAt = strpos($content, '    function highlightActiveTask(taskId)');
    if ($insertAt !== false) {
        $content = substr($content, 0, $insertAt).$handlers.substr($content, $insertAt);
    }
}

$content = str_replace(
    "    function highlightActiveTask(taskId) {\n        document.querySelectorAll('.task-item').forEach((item) => {",
    "    function highlightActiveTask(taskId) {\n        document.querySelectorAll('.task-row').forEach((item) => {",
    $content
);

$content = str_replace(
    <<<'OLD'
    function bindListEvents() {
        document.querySelectorAll('.task-item[data-task-id]').forEach((item) => {
            item.addEventListener('click', () => showTaskDetail(item.dataset.taskId));
        });
OLD
    ,
    <<<'NEW'
    function bindListEvents() {
        document.querySelectorAll('[data-task-select]').forEach((item) => {
            item.addEventListener('click', () => {
                const row = item.closest('.task-row');
                if (row?.dataset.taskId) {
                    showTaskDetail(row.dataset.taskId);
                }
            });
        });

        document.querySelectorAll('[data-toggle-complete]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                toggleTaskComplete(button);
            });
        });
NEW
    ,
    $content
);

$content = str_replace(
    <<<'OLD'
    document.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('[data-delete-task]');

        if (deleteButton) {
            event.preventDefault();
            deleteTask(deleteButton);
        }
    });
OLD
    ,
    <<<'NEW'
    document.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('[data-delete-task]');

        if (deleteButton) {
            event.preventDefault();
            deleteTask(deleteButton);
            return;
        }

        const completeButton = event.target.closest('[data-toggle-complete]');

        if (completeButton && !completeButton.closest('#tasks-list')) {
            event.preventDefault();
            toggleTaskComplete(completeButton);
        }
    });
NEW
    ,
    $content
);

file_put_contents($path, $content);
echo "Patched tasks.js\n";
