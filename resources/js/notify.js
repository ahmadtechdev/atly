import alertify from 'alertifyjs/build/alertify';
import 'alertifyjs/build/css/alertify.min.css';
import 'alertifyjs/build/css/themes/default.min.css';

let initialized = false;

function initAlertify() {
    if (initialized) {
        return;
    }

    initialized = true;

    alertify.set('notifier', 'position', 'top-right');
    alertify.set('notifier', 'delay', 6);

    alertify.defaults.closable = true;
    alertify.defaults.transition = 'zoom';
    alertify.defaults.movable = false;
    alertify.defaults.resizable = false;

    alertify.defaults.glossary = {
        title: 'ATLY',
        ok: 'OK',
        cancel: 'Cancel',
    };
}

/**
 * @param {unknown} data
 * @param {string} fallback
 */
export function messageFromResponse(data, fallback = 'Something went wrong.') {
    if (!data || typeof data !== 'object') {
        return fallback;
    }

    if (data.errors && typeof data.errors === 'object') {
        const messages = Object.values(data.errors).flat().filter(Boolean);

        if (messages.length > 0) {
            return messages.join(' ');
        }
    }

    if (typeof data.message === 'string' && data.message !== '') {
        return data.message;
    }

    return fallback;
}

export function notifySuccess(message, delay = 6) {
    if (!message) {
        return;
    }

    initAlertify();
    alertify.success(message, delay);
}

export function notifyError(message, delay = 8) {
    if (!message) {
        return;
    }

    initAlertify();
    alertify.error(message, delay);
}

export function notifyWarning(message, delay = 10) {
    if (!message) {
        return;
    }

    initAlertify();
    alertify.warning(message, delay);
}

export function notify(message, type = 'success', delay = 6) {
    switch (type) {
        case 'error':
            notifyError(message, delay);
            break;
        case 'warning':
            notifyWarning(message, delay);
            break;
        default:
            notifySuccess(message, delay);
    }
}

/**
 * @param {string} message
 * @returns {Promise<boolean>}
 */
export function confirmAction(message, title = 'Please confirm') {
    initAlertify();

    return new Promise((resolve) => {
        alertify
            .confirm(title, message, () => resolve(true), () => resolve(false))
            .set({ labels: { ok: 'Yes', cancel: 'No' } });
    });
}

export function initConfirmForms() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm]');

        if (!form) {
            return;
        }

        if (form.dataset.confirmApproved === '1') {
            delete form.dataset.confirmApproved;

            return;
        }

        event.preventDefault();

        const message = form.dataset.confirm || 'Are you sure?';

        confirmAction(message).then((approved) => {
            if (!approved) {
                return;
            }

            form.dataset.confirmApproved = '1';
            form.requestSubmit();
        });
    });
}

export function initFlashMessages() {
    const items = window.atlyFlash;

    if (!Array.isArray(items) || items.length === 0) {
        return;
    }

    items.forEach((item) => {
        if (!item?.message) {
            return;
        }

        notify(item.message, item.type || 'success', item.delay ?? undefined);
    });

    delete window.atlyFlash;
}

if (typeof window !== 'undefined') {
    window.atlyNotify = {
        success: notifySuccess,
        error: notifyError,
        warning: notifyWarning,
        notify,
        confirm: confirmAction,
        fromResponse: messageFromResponse,
    };
}
