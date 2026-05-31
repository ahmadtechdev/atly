const STORAGE_KEY = 'atly-sidebar-collapsed';
const MOBILE_BREAKPOINT = 1024;

export function initSidebar() {
    const sidebar = document.getElementById('dashboard-sidebar');

    if (! sidebar) {
        return;
    }

    const collapseToggle = document.getElementById('sidebar-toggle');
    const mobileOpen = document.getElementById('sidebar-mobile-open');
    const mobileClose = document.getElementById('sidebar-mobile-close');
    const backdrop = document.getElementById('dashboard-sidebar-backdrop');

    const applyCollapsed = (collapsed) => {
        document.body.dataset.sidebarCollapsed = collapsed ? 'true' : 'false';
        collapseToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    };

    const isAnyModalOpen = () => {
        return Boolean(document.querySelector('[id$="-modal"]:not(.hidden), #task-detail-drawer:not(.hidden)'));
    };

    const isMobileViewport = () => window.innerWidth < MOBILE_BREAKPOINT;

    const setMobileOpen = (open) => {
        document.body.dataset.mobileSidebarOpen = open ? 'true' : 'false';
        mobileOpen?.setAttribute('aria-expanded', open ? 'true' : 'false');

        if (isMobileViewport()) {
            sidebar.classList.toggle('translate-x-0', open);
            sidebar.classList.toggle('-translate-x-full', ! open);
            backdrop?.classList.toggle('hidden', ! open);
            backdrop?.setAttribute('aria-hidden', open ? 'false' : 'true');
        } else if (! open) {
            backdrop?.classList.add('hidden');
            backdrop?.setAttribute('aria-hidden', 'true');
        }

        if (open) {
            document.body.classList.add('overflow-hidden');
        } else if (! isAnyModalOpen()) {
            document.body.classList.remove('overflow-hidden');
        }
    };

    try {
        applyCollapsed(localStorage.getItem(STORAGE_KEY) === 'true');
    } catch {
        applyCollapsed(false);
    }

    setMobileOpen(false);

    collapseToggle?.addEventListener('click', () => {
        const collapsed = document.body.dataset.sidebarCollapsed !== 'true';
        applyCollapsed(collapsed);

        try {
            localStorage.setItem(STORAGE_KEY, collapsed ? 'true' : 'false');
        } catch {
            // Ignore
        }
    });

    mobileOpen?.addEventListener('click', () => setMobileOpen(true));
    mobileClose?.addEventListener('click', () => setMobileOpen(false));
    backdrop?.addEventListener('click', () => setMobileOpen(false));

    sidebar.addEventListener('click', (event) => {
        if (window.innerWidth >= MOBILE_BREAKPOINT) {
            return;
        }

        const isNav = event.target.closest('a[href]');
        const isQuickAction = event.target.closest('[data-open-task-modal], [data-open-project-modal], [data-open-workspace-modal]');

        if (isNav || isQuickAction) {
            setMobileOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.dataset.mobileSidebarOpen === 'true') {
            setMobileOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= MOBILE_BREAKPOINT && document.body.dataset.mobileSidebarOpen === 'true') {
            setMobileOpen(false);
        }
    });
}
