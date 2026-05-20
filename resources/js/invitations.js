import { messageFromResponse, notifyError, notifySuccess, notifyWarning } from './notify';

export function initInvitations() {
    const modal = document.getElementById('invite-modal');

    if (!modal) {
        return;
    }

    const form = modal.querySelector('#invite-form');
    const typeInput = form.querySelector('input[name="invitable_type"]');
    const idInput = form.querySelector('input[name="invitable_id"]');
    const emailInput = form.querySelector('input[name="email"]');
    const messageInput = form.querySelector('textarea[name="message"]');
    const kindLabel = modal.querySelector('[data-invite-kind]');
    const targetLabel = modal.querySelector('[data-invite-target]');
    const submitBtn = form.querySelector('[data-invite-submit]');

    const config = window.atlyInvitations || {};

    const titleCase = (value) => (value ? value.charAt(0).toUpperCase() + value.slice(1) : '');

    const openModal = ({ type, id, label }) => {
        form.reset();
        typeInput.value = type || '';
        idInput.value = id || '';
        kindLabel.textContent = titleCase(type) || 'Item';
        targetLabel.textContent = label || '—';
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        requestAnimationFrame(() => emailInput.focus());
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-open-invite-modal]');

        if (!trigger) {
            return;
        }

        event.preventDefault();
        openModal({
            type: trigger.dataset.invitableType,
            id: trigger.dataset.invitableId,
            label: trigger.dataset.invitableLabel,
        });
    });

    modal.querySelectorAll('[data-close-invite-modal]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!config.storeUrl || !config.csrf) {
            notifyError('Invitations are not available right now.');
            return;
        }

        const roleInput = form.querySelector('input[name="role"]:checked');

        const payload = {
            invitable_type: typeInput.value,
            invitable_id: idInput.value,
            email: emailInput.value.trim(),
            role: roleInput?.value || null,
            message: (messageInput.value || '').trim() || null,
        };

        submitBtn.disabled = true;

        try {
            const response = await fetch(config.storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': config.csrf,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                closeModal();

                const pendingRegistration = data.recipient_status === 'pending_registration';
                const text = data.message || 'Invitation sent.';

                if (pendingRegistration) {
                    notifyWarning(text, 12);
                } else {
                    notifySuccess(text);
                }

                return;
            }

            notifyError(messageFromResponse(data, 'Unable to send invitation.'));
        } catch {
            notifyError('Network error. Please try again.');
        } finally {
            submitBtn.disabled = false;
        }
    });
}
