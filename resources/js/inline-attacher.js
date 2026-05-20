function escapeHtml(value) {
    const el = document.createElement('div');
    el.textContent = value ?? '';

    return el.innerHTML;
}

function clampLabel(value, max = 30) {
    if (!value) {
        return '';
    }

    return value.length > max ? `${value.slice(0, max)}...` : value;
}

function debounce(fn, delay = 200) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function getCsrf() {
    return (
        document.querySelector('meta[name="csrf-token"]')?.content
        || window.atlyTasks?.csrf
        || window.atlyProjects?.csrf
        || window.atlyWorkspaces?.csrf
        || ''
    );
}

const dotClass = (color) => (color ? `bg-${color}-500` : 'bg-atly-ink-soft/40');
let activeAttacherClose = null;

/**
 * Inline "attach to X" chip + popover picker.
 * data-inline-attacher root expects:
 *   data-update-url     PATCH endpoint to set the relationship
 *   data-search-url     GET endpoint returning { results: [{id,name,subtitle?,color?}] }
 *   data-field-name     form field key (e.g. project_id)
 *   data-entity-label   singular noun shown in empty state (e.g. project)
 *   data-current-id     currently linked id (optional)
 *   data-current-label  currently linked label (optional)
 *   data-current-color  currently linked color name (optional)
 *   data-current-href   url for the chip label to link to (optional)
 */
function setupAttacher(root) {
    if (root.dataset.attacherInitialized === '1') {
        return;
    }
    root.dataset.attacherInitialized = '1';

    const updateUrl = root.dataset.updateUrl;
    const searchUrl = root.dataset.searchUrl;
    const fieldName = root.dataset.fieldName || 'id';
    const entityLabel = root.dataset.entityLabel || 'item';

    const state = {
        id: root.dataset.currentId || '',
        label: root.dataset.currentLabel || '',
        color: root.dataset.currentColor || '',
        href: root.dataset.currentHref || '',
    };

    root.classList.add('relative', 'inline-flex', 'min-w-0', 'max-w-full');

    const renderTrigger = () => {
        if (state.id) {
            return `
                <button type="button" data-attacher-trigger class="group/attach inline-flex w-fit max-w-full min-w-0 items-center gap-1.5 rounded-full bg-atly-muted/50 px-2 py-0.5 text-[11px] font-medium text-atly-ink-soft transition hover:bg-atly-muted/80 hover:text-atly-ink">
                    <span class="size-1.5 shrink-0 rounded-full ${dotClass(state.color)}"></span>
                    <span class="min-w-0 truncate">${escapeHtml(clampLabel(state.label))}</span>
                    <svg class="size-3 shrink-0 opacity-60 transition group-hover/attach:opacity-100" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>
            `;
        }

        return `
            <button type="button" data-attacher-trigger class="inline-flex w-fit max-w-full items-center gap-1 rounded-full border border-dashed border-atly-border px-2 py-0.5 text-[11px] font-medium text-atly-ink-soft/80 transition hover:border-atly-accent hover:text-atly-ink">
                <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span class="truncate">Add ${escapeHtml(entityLabel)}</span>
            </button>
        `;
    };

    root.innerHTML = renderTrigger();

    const dropdown = document.createElement('div');
    dropdown.dataset.attacherDropdown = '';
    dropdown.className = 'fixed z-50 hidden w-64 max-w-[18rem] overflow-hidden rounded-xl border border-atly-border bg-atly-card shadow-atly-lg';
    dropdown.innerHTML = `
        <div class="border-b border-atly-border p-2">
            <input type="search" data-attacher-search placeholder="Search ${escapeHtml(entityLabel)}s..." autocomplete="off" class="w-full rounded-lg border border-transparent bg-atly-muted/40 px-3 py-1.5 text-xs text-atly-ink focus:border-atly-accent focus:bg-atly-surface focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
        </div>
        <ul data-attacher-results class="max-h-56 overflow-y-auto py-1 text-xs"></ul>
    `;
    document.body.appendChild(dropdown);

    const searchInput = dropdown.querySelector('[data-attacher-search]');
    const results = dropdown.querySelector('[data-attacher-results]');

    const renderResults = (items) => {
        const rows = [];

        if (state.id) {
            rows.push(`<li><button type="button" data-attacher-option data-value="" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30"><svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg><span>Remove ${escapeHtml(entityLabel)}</span></button></li>`);
        }

        if (items.length === 0 && rows.length === 0) {
            rows.push('<li class="px-3 py-3 text-center text-xs text-atly-ink-soft">No matches</li>');
        } else {
            items.forEach((item) => {
                const isSelected = String(item.id) === String(state.id);
                const color = item.color || '';
                const subtitle = item.subtitle
                    ? `<span class="ml-auto truncate text-[10px] text-atly-ink-soft">${escapeHtml(item.subtitle)}</span>`
                    : '';
                const selectedMark = isSelected
                    ? '<svg class="ml-auto size-3 shrink-0 text-atly-accent-strong" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5" /></svg>'
                    : '';

                rows.push(`<li><button type="button" data-attacher-option data-value="${item.id}" data-label="${escapeHtml(item.name)}" data-color="${escapeHtml(color)}" class="flex w-full items-center gap-2 px-3 py-1.5 text-left ${isSelected ? 'bg-atly-muted/30 text-atly-ink' : 'text-atly-ink hover:bg-atly-muted/40'}"><span class="size-2 shrink-0 rounded-full ${dotClass(color)}"></span><span class="min-w-0 flex-1 truncate">${escapeHtml(clampLabel(item.name))}</span>${selectedMark || subtitle}</button></li>`);
            });
        }

        results.innerHTML = rows.join('');
        positionDropdown();
    };

    const fetchResults = async (query = '') => {
        try {
            const url = new URL(searchUrl, window.location.origin);
            if (query) {
                url.searchParams.set('search', query);
            }
            const response = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            renderResults(data.results || []);
        } catch (error) {
            renderResults([]);
        }
    };

    const positionDropdown = () => {
        const trigger = root.querySelector('[data-attacher-trigger]');
        if (!trigger) {
            return;
        }
        const rect = trigger.getBoundingClientRect();
        const dropdownWidth = dropdown.offsetWidth || 256;
        let left = rect.left;
        const overflow = left + dropdownWidth - (window.innerWidth - 8);
        if (overflow > 0) {
            left -= overflow;
        }
        if (left < 8) {
            left = 8;
        }

        const dropdownHeight = dropdown.offsetHeight || 280;
        let top = rect.bottom + 4;
        if (top + dropdownHeight > window.innerHeight - 8 && rect.top > dropdownHeight) {
            top = rect.top - 4 - dropdownHeight;
        }

        dropdown.style.top = `${top}px`;
        dropdown.style.left = `${left}px`;
    };

    const closeDropdown = () => {
        dropdown.classList.add('hidden');
        if (activeAttacherClose === closeDropdown) {
            activeAttacherClose = null;
        }
    };

    const openDropdown = () => {
        if (activeAttacherClose && activeAttacherClose !== closeDropdown) {
            activeAttacherClose();
        }
        activeAttacherClose = closeDropdown;
        positionDropdown();
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        results.innerHTML = '<li class="px-3 py-3 text-center text-xs text-atly-ink-soft">Loading...</li>';
        positionDropdown();
        fetchResults('');
        requestAnimationFrame(() => searchInput.focus());
    };

    const scrollHandler = () => {
        if (!dropdown.classList.contains('hidden')) {
            closeDropdown();
        }
    };
    window.addEventListener('scroll', scrollHandler, true);
    window.addEventListener('resize', scrollHandler);

    const refreshTrigger = () => {
        const oldTrigger = root.querySelector('[data-attacher-trigger]');
        if (oldTrigger) {
            const placeholder = document.createElement('div');
            placeholder.innerHTML = renderTrigger().trim();
            const newTrigger = placeholder.firstElementChild;
            oldTrigger.replaceWith(newTrigger);
        }
    };

    const selectOption = async (value, label, color) => {
        try {
            const formData = new FormData();
            formData.append('_method', 'PATCH');
            formData.append('_token', getCsrf());
            formData.append(fieldName, value || '');

            const response = await fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: formData,
            });

            if (!response.ok) {
                return;
            }

            state.id = value || '';
            state.label = label || '';
            state.color = color || '';
            refreshTrigger();
            closeDropdown();

            root.dispatchEvent(new CustomEvent('attacher:changed', {
                bubbles: true,
                detail: { fieldName, id: value || null, label, color },
            }));
        } catch (error) {
            // swallow — keep UI responsive
        }
    };

    root.addEventListener('click', (event) => {
        event.stopPropagation();

        const trigger = event.target.closest('[data-attacher-trigger]');
        if (trigger) {
            event.preventDefault();
            if (dropdown.classList.contains('hidden')) {
                openDropdown();
            } else {
                closeDropdown();
            }
            return;
        }
    });

    dropdown.addEventListener('click', (event) => {
        event.stopPropagation();

        const option = event.target.closest('[data-attacher-option]');
        if (!option) {
            return;
        }

        event.preventDefault();
        selectOption(option.dataset.value || '', option.dataset.label || '', option.dataset.color || '');
    });

    dropdown.addEventListener('keydown', (event) => {
        if (event.target === searchInput) {
            event.stopPropagation();
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            closeDropdown();
        }
    });

    searchInput.addEventListener('input', debounce((event) => {
        fetchResults(event.target.value.trim());
    }, 200));

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target) && !dropdown.contains(event.target)) {
            closeDropdown();
        }
    });
}

export function initInlineAttachers(scope = document) {
    scope.querySelectorAll('[data-inline-attacher]').forEach(setupAttacher);
}

if (typeof window !== 'undefined') {
    window.atlyInitAttachers = (scope) => initInlineAttachers(scope || document);
}
