function formatSeconds(seconds) {
    if (!Number.isFinite(seconds) || seconds <= 0) {
        return '0m';
    }

    const total = Math.floor(seconds);
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);

    if (hours === 0 && minutes === 0) {
        return '<1m';
    }

    if (hours === 0) {
        return `${minutes}m`;
    }

    if (minutes === 0) {
        return `${hours}h`;
    }

    return `${hours}h ${minutes}m`;
}

function formatHms(seconds) {
    const total = Math.max(0, Math.floor(seconds));
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;

    return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

function tickClock() {
    const clock = document.getElementById('time-tracker-clock');
    const date = document.getElementById('time-tracker-date');

    if (!clock) {
        return;
    }

    const now = new Date();
    const hours = now.getHours();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 === 0 ? 12 : hours % 12;
    const text = `${String(displayHours).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')} ${ampm}`;

    clock.textContent = text;

    if (date) {
        date.textContent = now.toISOString().slice(0, 10);
    }
}

function tickRunningSession() {
    document.querySelectorAll('[data-running-timer]').forEach((node) => {
        const startedAt = Number(node.dataset.startedAtMs);

        if (!Number.isFinite(startedAt)) {
            return;
        }

        const elapsed = (Date.now() - startedAt) / 1000;
        node.textContent = formatHms(elapsed);
    });
}

function tickTaskRowTimers() {
    document.querySelectorAll('[data-task-time-running]').forEach((node) => {
        const startedAt = Number(node.dataset.startedAtMs);
        const base = Number(node.dataset.baseSeconds || 0);

        if (!Number.isFinite(startedAt)) {
            return;
        }

        const label = node.querySelector('[data-task-time-label]');

        if (!label) {
            return;
        }

        const elapsed = base + (Date.now() - startedAt) / 1000;
        label.textContent = formatSeconds(elapsed);
    });
}

export function initTimeTrackerClock() {
    if (!document.getElementById('time-tracker-clock')) {
        return;
    }

    tickClock();
    tickRunningSession();
    setInterval(() => {
        tickClock();
        tickRunningSession();
    }, 1000);
}

export function initTaskRowLiveTimers() {
    tickTaskRowTimers();
    setInterval(tickTaskRowTimers, 30 * 1000);

    window.atlyTickTaskTimers = tickTaskRowTimers;
}
