import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

function chartColors() {
    const styles = getComputedStyle(document.documentElement);

    return {
        ink: styles.getPropertyValue('--color-atly-ink').trim() || '#424874',
        accent: styles.getPropertyValue('--color-atly-accent').trim() || '#a6b1e1',
        muted: styles.getPropertyValue('--color-atly-muted').trim() || '#dcd6f7',
        surface: styles.getPropertyValue('--color-atly-card').trim() || '#ffffff',
        inkSoft: styles.getPropertyValue('--color-atly-ink-soft').trim() || '#6b7280',
    };
}

function parseJsonDataset(element, fallback = {}) {
    try {
        return JSON.parse(element.dataset.chart ?? '{}');
    } catch {
        return fallback;
    }
}

export function initDashboardCharts() {
    const statusEl = document.getElementById('status-chart');
    const priorityEl = document.getElementById('priority-chart');
    const activityEl = document.getElementById('activity-chart');

    if (! statusEl && ! priorityEl && ! activityEl) {
        return;
    }

    const colors = chartColors();

    Chart.defaults.color = colors.inkSoft;
    Chart.defaults.borderColor = colors.muted;
    Chart.defaults.font.family = "'Instrument Sans', sans-serif";

    if (statusEl) {
        const data = parseJsonDataset(statusEl);
        const labels = Object.keys(data);
        const values = Object.values(data);

        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: [colors.muted, colors.accent, '#34d399'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }

    if (priorityEl) {
        const data = parseJsonDataset(priorityEl);
        const labels = Object.keys(data).map((key) => key.charAt(0).toUpperCase() + key.slice(1));

        new Chart(priorityEl, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Tasks',
                    data: Object.values(data),
                    backgroundColor: [colors.muted, colors.accent, colors.ink],
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    if (activityEl) {
        const payload = parseJsonDataset(activityEl, []);
        const labels = payload.map((row) => row.label);
        const created = payload.map((row) => row.created);
        const completed = payload.map((row) => row.completed);

        new Chart(activityEl, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Created',
                        data: created,
                        borderColor: colors.accent,
                        backgroundColor: colors.accent + '33',
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Completed',
                        data: completed,
                        borderColor: '#34d399',
                        backgroundColor: '#34d39933',
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }
}
