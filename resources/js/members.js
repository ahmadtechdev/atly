import { confirmAction, messageFromResponse, notifyError, notifySuccess } from './notify';

function closeAllMenus(except = null) {
    document.querySelectorAll('[data-member-menu]').forEach((menu) => {
        if (menu === except) {
            return;
        }

        menu.classList.add('hidden');
    });
}

function csrf() {
    return (
        window.atlyTasks?.csrf
        || window.atlyProjects?.csrf
        || window.atlyWorkspaces?.csrf
        || window.atlyInvitations?.csrf
        || document.querySelector('meta[name="csrf-token"]')?.content
        || ''
    );
}

async function patchRole(row, roleValue) {
    const url = row.dataset.updateUrl;

    if (!url) {
        return;
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body: new URLSearchParams({ _method: 'PATCH', role: roleValue }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            notifyError(messageFromResponse(data, 'Could not update role.'));

            return;
        }

        notifySuccess(data.message || 'Role updated.');
        row.dataset.currentRole = roleValue;

        const label = row.querySelector('[data-member-role-label]');
        const trigger = row.querySelector('[data-member-menu-trigger]');

        if (label) {
            label.textContent = roleValue.charAt(0).toUpperCase() + roleValue.slice(1);
        }

        if (trigger) {
            // Reload the page so server-rendered classes/state stay consistent.
            // Cheap and reliable for now; we can replace with surgical DOM updates later.
            window.setTimeout(() => window.location.reload(), 400);
        }
    } catch {
        notifyError('Network error. Please try again.');
    }
}

async function removeMember(row) {
    const url = row.dataset.removeUrl;
    const name = row.dataset.memberName || 'this member';
    const confirmed = await confirmAction(`Remove ${name}? They will lose access immediately.`);

    if (!confirmed) {
        return;
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body: new URLSearchParams({ _method: 'DELETE' }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            notifyError(messageFromResponse(data, 'Could not remove member.'));

            return;
        }

        notifySuccess(data.message || 'Member removed.');
        row.classList.add('opacity-50', 'pointer-events-none');
        window.setTimeout(() => window.location.reload(), 400);
    } catch {
        notifyError('Network error. Please try again.');
    }
}

export function initMemberMenus() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-member-menu-trigger]');

        if (trigger) {
            const row = trigger.closest('[data-member-row]');
            const menu = row?.querySelector('[data-member-menu]');

            if (!menu) {
                return;
            }

            const willOpen = menu.classList.contains('hidden');
            closeAllMenus(willOpen ? menu : null);
            menu.classList.toggle('hidden', !willOpen);

            return;
        }

        const setRoleBtn = event.target.closest('[data-member-set-role]');

        if (setRoleBtn) {
            const row = setRoleBtn.closest('[data-member-row]');
            closeAllMenus();

            if (row) {
                patchRole(row, setRoleBtn.dataset.memberSetRole);
            }

            return;
        }

        const removeBtn = event.target.closest('[data-member-remove]');

        if (removeBtn) {
            const row = removeBtn.closest('[data-member-row]');
            closeAllMenus();

            if (row) {
                removeMember(row);
            }

            return;
        }

        // Click outside any menu — close them all.
        if (!event.target.closest('[data-member-menu]')) {
            closeAllMenus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllMenus();
        }
    });
}
