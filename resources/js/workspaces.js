import { messageFromResponse, notifyError, notifySuccess } from './notify';

function debounce(fn, delay = 300) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

export function initWorkspaces() {
    const config = window.atlyWorkspaces;

    if (!config) {
        return;
    }

    const modal = document.getElementById('workspace-quick-modal');
    const form = document.getElementById('workspace-quick-form');
    const listWrapper = document.getElementById('workspaces-list-wrapper');
    const searchInput = document.getElementById('workspace-search');

    const openModal = () => {
        modal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        modal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        form?.reset();
    };

    document.querySelectorAll('[data-open-workspace-modal]').forEach((button) => {
        button.addEventListener('click', openModal);
    });

    document.querySelectorAll('[data-close-workspace-modal]').forEach((button) => {
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
        } finally {
            listWrapper.classList.remove('opacity-60', 'pointer-events-none');
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => refreshList(), 250));
    }

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = form.querySelector('[type="submit"]');
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

            const data = await response.json().catch(() => ({}));

            if (response.status === 422 || !response.ok) {
                notifyError(messageFromResponse(data, 'Please check the form.'));

                return;
            }

            closeModal();
            notifySuccess(data.message || 'Workspace created successfully.');

            if (listWrapper) {
                await refreshList();
            } else {
                window.location.href = config.indexUrl;
            }
        } catch {
            notifyError('Network error. Please try again.');
        } finally {
            submitButton?.removeAttribute('disabled');
        }
    });
}
