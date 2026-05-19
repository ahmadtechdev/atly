const STORAGE_KEY = 'atly-sidebar-collapsed';

export function initSidebar() {
    const sidebar = document.getElementById('dashboard-sidebar');
    const toggle = document.getElementById('sidebar-toggle');

    if (! sidebar || ! toggle) {
        return;
    }

    const applyCollapsed = (collapsed) => {
        document.body.dataset.sidebarCollapsed = collapsed ? 'true' : 'false';
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    };

    try {
        applyCollapsed(localStorage.getItem(STORAGE_KEY) === 'true');
    } catch {
        applyCollapsed(false);
    }

    toggle.addEventListener('click', () => {
        const collapsed = document.body.dataset.sidebarCollapsed !== 'true';
        applyCollapsed(collapsed);

        try {
            localStorage.setItem(STORAGE_KEY, collapsed ? 'true' : 'false');
        } catch {
            // Ignore
        }
    });
}
