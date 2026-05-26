import { messageFromResponse, notifyError, notifySuccess } from './notify';

const state = {
    project: null,
    tasks: [],
};

let priorities = [];

function el(id) {
    return document.getElementById(id);
}

function showErrors(target, messages) {
    if (!target) return;

    if (!messages || (Array.isArray(messages) && messages.length === 0)) {
        target.classList.add('hidden');
        target.innerHTML = '';
        return;
    }

    const list = Array.isArray(messages) ? messages : [messages];
    target.innerHTML = list.map((m) => `<div>${escapeHtml(m)}</div>`).join('');
    target.classList.remove('hidden');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function flattenValidationErrors(payload) {
    if (!payload) return [];
    if (typeof payload === 'string') return [payload];
    if (payload.errors && typeof payload.errors === 'object') {
        return Object.values(payload.errors).flat();
    }
    if (payload.message) return [payload.message];
    return [];
}

function renderTeamMembers() {
    const container = el('bp-members');
    const block = el('bp-team-block');
    const isTeam = document.querySelector('input[name="assignment_type"]:checked')?.value === 'team';

    if (!block || !container) return;

    block.classList.toggle('hidden', !isTeam);

    if (isTeam && container.children.length === 0) {
        addMemberRow();
        addMemberRow();
    }
}

function addMemberRow() {
    const template = el('bp-member-template');
    const container = el('bp-members');
    if (!template || !container) return;

    const clone = template.content.firstElementChild.cloneNode(true);
    clone.classList.add('mb-2');
    container.appendChild(clone);

    clone.querySelector('[data-remove-member]')?.addEventListener('click', () => {
        clone.remove();
    });
}

function renderTasks() {
    const list = el('bp-tasks-list');
    const count = el('bp-tasks-count');
    if (!list) return;

    if (count) {
        count.textContent = `(${state.tasks.length})`;
    }

    if (state.tasks.length === 0) {
        list.innerHTML = `
            <div class="rounded-xl border border-dashed border-atly-border bg-atly-surface px-6 py-10 text-center text-sm text-atly-ink-soft">
                No tasks left. Add one or regenerate the plan.
            </div>
        `;
        return;
    }

    const priorityOptions = priorities
        .map((p) => `<option value="${escapeHtml(p.value)}">${escapeHtml(p.name)}</option>`)
        .join('');

    list.innerHTML = state.tasks
        .map((task, index) => taskCardHtml(task, index, priorityOptions))
        .join('');

    list.querySelectorAll('[data-task-row]').forEach((row) => {
        const index = Number(row.dataset.index);

        row.querySelectorAll('[data-field]').forEach((input) => {
            input.addEventListener('input', () => {
                const field = input.dataset.field;
                state.tasks[index][field] = input.value;
            });
        });

        row.querySelector('[data-remove-task]')?.addEventListener('click', () => {
            state.tasks.splice(index, 1);
            renderTasks();
        });
    });
}

function taskCardHtml(task, index, priorityOptions) {
    return `
        <div class="rounded-xl border border-atly-border bg-atly-surface p-4" data-task-row data-index="${index}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <input
                        type="text"
                        data-field="title"
                        value="${escapeHtml(task.title)}"
                        placeholder="Task title"
                        class="w-full rounded-lg border border-atly-border bg-atly-card px-3 py-2 text-sm font-semibold text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                    />
                </div>
                <button type="button" data-remove-task class="rounded-lg p-2 text-atly-ink-soft hover:bg-red-50 hover:text-red-600" aria-label="Remove task">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <textarea
                data-field="description"
                rows="2"
                placeholder="Description"
                class="mt-2 w-full rounded-lg border border-atly-border bg-atly-card px-3 py-2 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
            >${escapeHtml(task.description ?? '')}</textarea>

            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-atly-ink-soft">Milestone</span>
                    <input type="text" data-field="milestone" value="${escapeHtml(task.milestone ?? '')}" class="rounded-lg border border-atly-border bg-atly-card px-2.5 py-1.5 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-atly-ink-soft">Skill</span>
                    <input type="text" data-field="skill_required" value="${escapeHtml(task.skill_required ?? '')}" class="rounded-lg border border-atly-border bg-atly-card px-2.5 py-1.5 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-atly-ink-soft">Priority</span>
                    <select data-field="priority" class="rounded-lg border border-atly-border bg-atly-card px-2.5 py-1.5 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                        ${priorities
                            .map(
                                (p) =>
                                    `<option value="${escapeHtml(p.value)}" ${task.priority === p.value ? 'selected' : ''}>${escapeHtml(p.name)}</option>`,
                            )
                            .join('')}
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-atly-ink-soft">Start</span>
                    <input type="date" data-field="start_date" value="${escapeHtml(task.start_date)}" class="rounded-lg border border-atly-border bg-atly-card px-2.5 py-1.5 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-atly-ink-soft">Due</span>
                    <input type="date" data-field="due_date" value="${escapeHtml(task.due_date)}" class="rounded-lg border border-atly-border bg-atly-card px-2.5 py-1.5 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                </label>
            </div>

            ${task.estimated_hours || task.assigned_to ? `
                <div class="mt-2 flex flex-wrap gap-2 text-xs text-atly-ink-soft">
                    ${task.estimated_hours ? `<span class="inline-flex items-center gap-1 rounded-full bg-atly-muted px-2 py-1">≈ ${escapeHtml(task.estimated_hours)}h</span>` : ''}
                    ${task.assigned_to ? `<span class="inline-flex items-center gap-1 rounded-full bg-atly-muted px-2 py-1">@${escapeHtml(task.assigned_to)}</span>` : ''}
                </div>
            ` : ''}
        </div>
    `;
}

function setLoading(loading) {
    const btn = el('bp-generate-btn');
    const label = el('bp-generate-label');
    if (!btn) return;

    if (loading) {
        btn.setAttribute('disabled', 'disabled');
        if (label) label.textContent = 'Generating…';
    } else {
        btn.removeAttribute('disabled');
        if (label) label.textContent = 'Generate plan';
    }
}

function showDraft() {
    el('blueprint-form-section')?.classList.add('hidden');
    el('blueprint-draft-section')?.classList.remove('hidden');

    el('bp-project-name').value = state.project?.name ?? '';
    el('bp-project-description').value = state.project?.description ?? '';

    renderTasks();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function backToForm() {
    el('blueprint-draft-section')?.classList.add('hidden');
    el('blueprint-form-section')?.classList.remove('hidden');
}

async function handleGenerate(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const errors = el('bp-form-errors');
    showErrors(errors, null);

    const formData = new FormData(form);

    document.querySelectorAll('[data-member-row]').forEach((row, index) => {
        ['name', 'skill', 'split'].forEach((field) => {
            const input = row.querySelector(`[data-field="${field}"]`);
            if (input && input.value !== '') {
                formData.append(`members[${index}][${field}]`, input.value);
            }
        });
    });

    setLoading(true);

    try {
        const response = await fetch(window.atlyBlueprint.generateUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.atlyBlueprint.csrf,
                Accept: 'application/json',
            },
            body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const list = flattenValidationErrors(data);
            showErrors(errors, list.length ? list : [data.message || 'Generation failed.']);
            return;
        }

        state.project = data.blueprint.project;
        state.tasks = data.blueprint.tasks;
        notifySuccess(`AI drafted ${state.tasks.length} tasks. Review and edit before saving.`);
        showDraft();
    } catch (err) {
        notifyError('Network error. Please try again.');
    } finally {
        setLoading(false);
    }
}

async function handleFinalize() {
    const errors = el('bp-finalize-errors');
    showErrors(errors, null);

    const projectName = el('bp-project-name').value.trim();
    const projectDescription = el('bp-project-description').value.trim();
    const workspaceId = el('bp-project-workspace').value;
    const color = document.querySelector('#bp-project-color input[name="bp-color"]:checked')?.value ?? 'sky';

    if (!projectName) {
        showErrors(errors, ['Project name is required.']);
        return;
    }

    if (state.tasks.length === 0) {
        showErrors(errors, ['Add at least one task before saving.']);
        return;
    }

    const payload = {
        project: {
            name: projectName,
            description: projectDescription || null,
            color,
        },
        workspace_id: workspaceId || null,
        tasks: state.tasks.map((task) => ({
            title: task.title,
            description: task.description || null,
            priority: task.priority,
            start_date: task.start_date,
            due_date: task.due_date,
        })),
    };

    const btn = el('bp-finalize-btn');
    btn?.setAttribute('disabled', 'disabled');

    try {
        const response = await fetch(window.atlyBlueprint.storeUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.atlyBlueprint.csrf,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const list = flattenValidationErrors(data);
            showErrors(errors, list.length ? list : [data.message || 'Could not save project.']);
            return;
        }

        notifySuccess(data.message || 'Project created.');
        window.location.href = data.redirect;
    } catch (err) {
        showErrors(errors, ['Network error. Please try again.']);
    } finally {
        btn?.removeAttribute('disabled');
    }
}

export function initBlueprint() {
    const config = window.atlyBlueprint;
    if (!config) return;

    priorities = Array.isArray(config.priorities) ? config.priorities : [];

    if (!config.hasAvailableModels) {
        return;
    }

    const form = el('blueprint-form');
    form?.addEventListener('submit', handleGenerate);

    document.querySelectorAll('input[name="assignment_type"]').forEach((radio) => {
        radio.addEventListener('change', renderTeamMembers);
    });

    el('bp-add-member')?.addEventListener('click', addMemberRow);
    el('bp-add-task')?.addEventListener('click', () => {
        state.tasks.push({
            id: `manual-${Date.now()}`,
            title: '',
            description: '',
            milestone: '',
            skill_required: '',
            estimated_hours: 4,
            priority: priorities[0]?.value ?? 'medium',
            start_date: state.project?.start_date ?? new Date().toISOString().slice(0, 10),
            due_date: state.project?.end_date ?? new Date().toISOString().slice(0, 10),
            assigned_to: null,
        });
        renderTasks();
    });

    el('bp-regenerate')?.addEventListener('click', backToForm);
    el('bp-cancel-draft')?.addEventListener('click', () => {
        if (confirm('Discard the AI draft? This cannot be undone.')) {
            state.project = null;
            state.tasks = [];
            backToForm();
        }
    });

    el('bp-finalize-btn')?.addEventListener('click', handleFinalize);

    renderTeamMembers();
}
