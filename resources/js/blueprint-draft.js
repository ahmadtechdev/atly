import { notifyError, notifySuccess } from './notify';

const state = {
    tasks: [],
    members: [],
};

let priorities = [];
let config = null;

function el(id) {
    return document.getElementById(id);
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showErrors(messages) {
    const target = el('bpd-errors');
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

function flattenValidationErrors(payload) {
    if (!payload) return [];
    if (payload.errors && typeof payload.errors === 'object') {
        return Object.values(payload.errors).flat();
    }
    if (payload.message) return [payload.message];
    return [];
}

function statusClass(status) {
    return {
        accepted: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
        declined: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200',
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
    }[status] || 'bg-atly-muted text-atly-ink-soft';
}

function statusLabel(status) {
    return {
        accepted: 'Accepted',
        declined: 'Declined',
        pending: 'Pending',
    }[status] || '—';
}

function renderMembers() {
    const container = el('bpd-members');
    if (!container) return;

    container.innerHTML = '';

    state.members.forEach((member, index) => {
        const template = el('bpd-member-template');
        if (!template) return;
        const node = template.content.firstElementChild.cloneNode(true);
        node.dataset.index = index;

        node.querySelector('[data-field="name"]').value = member.name || '';
        node.querySelector('[data-field="email"]').value = member.email || '';
        node.querySelector('[data-field="skills"]').value = member.skills || '';
        node.querySelector('[data-field="split"]').value = member.split ?? '';

        const status = member.status || 'pending';
        const statusNode = node.querySelector('[data-status]');
        if (statusNode) {
            statusNode.className = `member-status inline-flex items-center gap-1 rounded-full px-2 py-1 text-[10px] font-semibold uppercase ${statusClass(status)}`;
            statusNode.textContent = statusLabel(status);
            if (!member.email) {
                statusNode.textContent = 'No email';
                statusNode.className = `member-status inline-flex items-center gap-1 rounded-full px-2 py-1 text-[10px] font-semibold uppercase ${statusClass('pending')}`;
            }
        }

        node.querySelectorAll('[data-field]').forEach((input) => {
            input.addEventListener('input', () => {
                state.members[index][input.dataset.field] = input.value;
            });
        });

        node.querySelector('[data-remove-member]')?.addEventListener('click', () => {
            if (!confirm('Remove this team member from the draft?')) return;
            state.members.splice(index, 1);
            renderMembers();
        });

        container.appendChild(node);
    });
}

function renderTasks() {
    const list = el('bpd-tasks');
    const count = el('bpd-tasks-count');
    if (!list) return;

    if (count) count.textContent = `(${state.tasks.length})`;

    if (state.tasks.length === 0) {
        list.innerHTML = `<div class="rounded-xl border border-dashed border-atly-border bg-atly-surface px-6 py-10 text-center text-sm text-atly-ink-soft">No tasks yet. Add one to get started.</div>`;
        return;
    }

    list.innerHTML = state.tasks.map((task, index) => taskCardHtml(task, index)).join('');

    list.querySelectorAll('[data-task-row]').forEach((row) => {
        const index = Number(row.dataset.index);

        row.querySelectorAll('[data-field]').forEach((input) => {
            input.addEventListener('input', () => {
                state.tasks[index][input.dataset.field] = input.value;
            });
        });

        row.querySelector('[data-remove-task]')?.addEventListener('click', () => {
            state.tasks.splice(index, 1);
            renderTasks();
        });
    });
}

function taskCardHtml(task, index) {
    const memberOptions = state.members
        .filter((m) => m.name)
        .map((m) => `<option value="${escapeHtml(m.name)}" ${task.assigned_to === m.name ? 'selected' : ''}>${escapeHtml(m.name)}</option>`)
        .join('');

    return `
        <div class="rounded-xl border border-atly-border bg-atly-surface p-4" data-task-row data-index="${index}">
            <div class="flex items-start justify-between gap-3">
                <input type="text" data-field="title" value="${escapeHtml(task.title)}" placeholder="Task title" class="w-full rounded-lg border border-atly-border bg-atly-card px-3 py-2 text-sm font-semibold text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                <button type="button" data-remove-task class="rounded-lg p-2 text-atly-ink-soft hover:bg-red-50 hover:text-red-600" aria-label="Remove task">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <textarea data-field="description" rows="2" placeholder="Description" class="mt-2 w-full rounded-lg border border-atly-border bg-atly-card px-3 py-2 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">${escapeHtml(task.description ?? '')}</textarea>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-atly-ink-soft">Priority</span>
                    <select data-field="priority" class="rounded-lg border border-atly-border bg-atly-card px-2.5 py-1.5 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                        ${priorities.map((p) => `<option value="${escapeHtml(p.value)}" ${task.priority === p.value ? 'selected' : ''}>${escapeHtml(p.label)}</option>`).join('')}
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
                ${config.draft.is_team ? `
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-atly-ink-soft">Assigned to</span>
                        <select data-field="assigned_to" class="rounded-lg border border-atly-border bg-atly-card px-2.5 py-1.5 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                            <option value="">— Unassigned —</option>
                            ${memberOptions}
                        </select>
                    </label>
                ` : ''}
            </div>
        </div>
    `;
}

async function postJson(url, payload, method = 'POST') {
    const response = await fetch(url, {
        method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': config.csrf,
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
        body: JSON.stringify(payload),
    });
    const data = await response.json().catch(() => ({}));
    return { ok: response.ok, data };
}

function buildPayload() {
    return {
        project: {
            name: el('bpd-name').value.trim(),
            description: el('bpd-description').value.trim() || null,
            color: document.querySelector('#bpd-color input[name="bpd-color"]:checked')?.value ?? 'sky',
        },
        workspace_id: el('bpd-workspace').value || null,
        tasks: state.tasks.map((t) => ({
            title: t.title,
            description: t.description || null,
            priority: t.priority,
            start_date: t.start_date,
            due_date: t.due_date,
            assigned_to: t.assigned_to || null,
            milestone: t.milestone || null,
            skill_required: t.skill_required || null,
            estimated_hours: t.estimated_hours || null,
        })),
        members: state.members.map((m) => ({
            id: m.id ?? null,
            name: m.name,
            email: m.email || null,
            skills: m.skills || null,
            split: m.split !== '' && m.split !== null && m.split !== undefined ? Number(m.split) : null,
        })),
    };
}

async function handleSave() {
    showErrors(null);
    const btn = el('bpd-save');
    btn?.setAttribute('disabled', 'disabled');
    try {
        const { ok, data } = await postJson(config.updateUrl, buildPayload(), 'PUT');
        if (!ok) {
            const list = flattenValidationErrors(data);
            showErrors(list.length ? list : [data.message || 'Could not save.']);
            return;
        }
        notifySuccess('Draft updated.');
    } catch (e) {
        showErrors(['Network error.']);
    } finally {
        btn?.removeAttribute('disabled');
    }
}

async function handleInvite() {
    showErrors(null);
    const btn = el('bpd-invite');
    btn?.setAttribute('disabled', 'disabled');
    try {
        await postJson(config.updateUrl, buildPayload(), 'PUT');
        const { ok, data } = await postJson(config.inviteUrl, {});
        if (!ok) {
            const list = flattenValidationErrors(data);
            showErrors(list.length ? list : [data.message || 'Could not send invitations.']);
            return;
        }
        notifySuccess(data.message || 'Invitations sent.');
        window.location.reload();
    } catch (e) {
        showErrors(['Network error.']);
    } finally {
        btn?.removeAttribute('disabled');
    }
}

async function handleFinalize() {
    if (config.draft.is_team) {
        const acceptedCount = state.members.filter((m) => m.status === 'accepted').length;
        const total = state.members.filter((m) => m.email).length;
        if (acceptedCount < total && !confirm(`Only ${acceptedCount} of ${total} invited members have accepted. Finalize anyway? Tasks assigned to unconfirmed members will be left unassigned.`)) {
            return;
        }
    }

    showErrors(null);
    const btn = el('bpd-finalize');
    btn?.setAttribute('disabled', 'disabled');
    try {
        await postJson(config.updateUrl, buildPayload(), 'PUT');
        const { ok, data } = await postJson(config.finalizeUrl, {});
        if (!ok) {
            const list = flattenValidationErrors(data);
            showErrors(list.length ? list : [data.message || 'Could not finalize.']);
            return;
        }
        notifySuccess(data.message || 'Project created.');
        window.location.href = data.redirect;
    } catch (e) {
        showErrors(['Network error.']);
    } finally {
        btn?.removeAttribute('disabled');
    }
}

async function handleDelete() {
    if (!confirm('Delete this draft? This cannot be undone. Pending invitations will be cancelled.')) return;
    try {
        const { ok, data } = await postJson(config.destroyUrl, {}, 'DELETE');
        if (!ok) {
            notifyError(data.message || 'Could not delete.');
            return;
        }
        window.location.href = config.draftsIndexUrl;
    } catch (e) {
        notifyError('Network error.');
    }
}

export function initBlueprintDraft() {
    config = window.atlyBlueprintDraft;
    if (!config) return;

    priorities = Array.isArray(config.priorities) ? config.priorities : [];
    state.tasks = Array.isArray(config.draft.tasks) ? [...config.draft.tasks] : [];
    state.members = Array.isArray(config.draft.members) ? config.draft.members.map((m) => ({ ...m })) : [];

    renderMembers();
    renderTasks();

    el('bpd-save')?.addEventListener('click', handleSave);
    el('bpd-invite')?.addEventListener('click', handleInvite);
    el('bpd-finalize')?.addEventListener('click', handleFinalize);
    el('bpd-delete')?.addEventListener('click', handleDelete);
    el('bpd-add-member')?.addEventListener('click', () => {
        state.members.push({ name: '', email: '', skills: '', split: null, status: 'pending' });
        renderMembers();
    });
    el('bpd-add-task')?.addEventListener('click', () => {
        state.tasks.push({
            title: '',
            description: '',
            priority: priorities[0]?.value ?? 'medium',
            start_date: new Date().toISOString().slice(0, 10),
            due_date: new Date().toISOString().slice(0, 10),
            assigned_to: null,
        });
        renderTasks();
    });

    document.querySelectorAll('#bpd-members [data-member-row] input').forEach((i) => i.addEventListener('input', renderTasks));
}
