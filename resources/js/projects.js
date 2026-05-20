function debounce(fn, delay = 300) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

export function initProjects() {
    const config = window.atlyProjects;

    if (!config) {
        return;
    }

    const modal = document.getElementById('project-quick-modal');
    const form = document.getElementById('project-quick-form');
    const listWrapper = document.getElementById('projects-list-wrapper');
    const searchInput = document.getElementById('project-search');

    const workspacePicker = () => modal?.querySelector('[data-searchable-picker][data-name="workspace_id"]');

    const openModal = (prefill = {}) => {
        modal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        if (prefill.workspaceId) {
            window.atlySetSearchablePicker?.(workspacePicker(), {
                id: prefill.workspaceId,
                label: prefill.workspaceLabel || '',
                color: prefill.workspaceColor || '',
            });
        }
    };

    const closeModal = () => {
        modal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        form?.reset();
        window.atlySetSearchablePicker?.(workspacePicker(), { id: '', label: '', color: '' });
        document.getElementById('project-modal-errors')?.classList.add('hidden');
    };

    document.querySelectorAll('[data-open-project-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            openModal({
                workspaceId: button.dataset.prefillWorkspaceId,
                workspaceLabel: button.dataset.prefillWorkspaceLabel,
                workspaceColor: button.dataset.prefillWorkspaceColor,
            });
        });
    });

    document.querySelectorAll('[data-close-project-modal]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    const deleteModal = document.getElementById('project-delete-modal');

    if (deleteModal) {
        const openDelete = () => {
            deleteModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };
        const closeDelete = () => {
            deleteModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-open-project-delete]').forEach((button) => {
            button.addEventListener('click', openDelete);
        });

        document.querySelectorAll('[data-close-project-delete]').forEach((button) => {
            button.addEventListener('click', closeDelete);
        });

        deleteModal.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDelete();
            }
        });
    }

    const refreshList = async (url = null) => {
        if (!listWrapper) {
            return;
        }

        const query = searchInput?.value.trim() ?? '';
        const fetchUrl = url ?? `${config.indexUrl}?${new URLSearchParams(query ? { search: query } : {}).toString()}`;

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
            window.atlyInitAttachers?.(listWrapper);
        } finally {
            listWrapper.classList.remove('opacity-60', 'pointer-events-none');
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => refreshList(), 250));
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const errorsEl = document.getElementById('project-modal-errors');
        const submitButton = form.querySelector('[type="submit"]');
        errorsEl?.classList.add('hidden');
        submitButton?.setAttribute('disabled', 'disabled');

        try {
            const response = await fetch(config.storeUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: new FormData(form),
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
            } else {
                window.location.href = config.indexUrl;
            }
        } finally {
            submitButton?.removeAttribute('disabled');
        }
    });
}
