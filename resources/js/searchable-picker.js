function escapeHtml(value) {
    const el = document.createElement('div');
    el.textContent = value ?? '';

    return el.innerHTML;
}

function debounce(fn, delay = 250) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

/**
 * Searchable single-select dropdown.
 * Read config from data-* on the wrapper:
 *   data-searchable-picker
 *   data-search-url      endpoint -> { results: [{id,name,subtitle?,color?}] }
 *   data-name            hidden input name (e.g. project_id)
 *   data-empty-label     label shown for "no value" choice (default: "None")
 *   data-placeholder     placeholder when nothing is selected
 *   data-selected-id
 *   data-selected-label
 *   data-selected-color  optional Tailwind color name
 */
function setupPicker(root) {
    if (root.dataset.pickerInitialized === '1') {
        return;
    }
    root.dataset.pickerInitialized = '1';

    const searchUrl = root.dataset.searchUrl;
    const inputName = root.dataset.name || 'id';
    const emptyLabel = root.dataset.emptyLabel || 'None';
    const placeholder = root.dataset.placeholder || emptyLabel;
    const initialId = root.dataset.selectedId || '';
    const initialLabel = root.dataset.selectedLabel || (initialId ? '' : placeholder);
    const initialColor = root.dataset.selectedColor || '';

    const dotClass = (color) => (color ? `bg-${color}-500` : 'bg-atly-ink-soft/40');

    root.innerHTML = `
        <div class="relative">
            <input type="hidden" name="${inputName}" data-picker-value value="${escapeHtml(initialId)}">
            <button type="button" data-picker-trigger class="flex w-full items-center justify-between gap-2 rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-left text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                <span class="flex min-w-0 items-center gap-2">
                    <span data-picker-color class="size-2.5 shrink-0 rounded-full ${dotClass(initialColor)}"></span>
                    <span data-picker-label class="truncate ${initialId ? 'text-atly-ink' : 'text-atly-ink-soft'}">${escapeHtml(initialLabel)}</span>
                </span>
                <svg class="size-4 shrink-0 text-atly-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
            </button>
            <div data-picker-dropdown class="absolute left-0 right-0 z-30 mt-1 hidden max-h-72 overflow-hidden rounded-xl border border-atly-border bg-atly-card shadow-atly-lg">
                <div class="border-b border-atly-border p-2">
                    <input type="search" data-picker-search placeholder="Search..." autocomplete="off" class="w-full rounded-lg border border-transparent bg-atly-muted/40 px-3 py-2 text-sm text-atly-ink focus:border-atly-accent focus:bg-atly-surface focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                </div>
                <ul data-picker-results class="max-h-48 overflow-y-auto py-1 text-sm"></ul>
            </div>
        </div>
    `;

    const hiddenInput = root.querySelector('[data-picker-value]');
    const trigger = root.querySelector('[data-picker-trigger]');
    const dropdown = root.querySelector('[data-picker-dropdown]');
    const searchInput = root.querySelector('[data-picker-search]');
    const results = root.querySelector('[data-picker-results]');
    const labelEl = root.querySelector('[data-picker-label]');
    const colorEl = root.querySelector('[data-picker-color]');

    const renderResults = (items) => {
        const rows = [
            `<li><button type="button" data-picker-option data-value="" class="flex w-full items-center gap-2 px-3 py-2 text-left text-atly-ink-soft hover:bg-atly-muted/40"><span class="size-2.5 rounded-full bg-atly-ink-soft/40"></span><span>${escapeHtml(emptyLabel)}</span></button></li>`,
        ];

        if (items.length === 0) {
            rows.push('<li class="px-3 py-3 text-center text-xs text-atly-ink-soft">No matches</li>');
        } else {
            items.forEach((item) => {
                const color = item.color ?? '';
                const subtitle = item.subtitle ? `<span class="ml-auto truncate text-xs text-atly-ink-soft">${escapeHtml(item.subtitle)}</span>` : '';
                rows.push(`<li><button type="button" data-picker-option data-value="${item.id}" data-label="${escapeHtml(item.name)}" data-color="${escapeHtml(color)}" class="flex w-full items-center gap-2 px-3 py-2 text-left text-atly-ink hover:bg-atly-muted/40"><span class="size-2.5 shrink-0 rounded-full ${dotClass(color)}"></span><span class="truncate">${escapeHtml(item.name)}</span>${subtitle}</button></li>`);
            });
        }

        results.innerHTML = rows.join('');
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

    const openDropdown = () => {
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        fetchResults('');
        requestAnimationFrame(() => searchInput.focus());
    };

    const closeDropdown = () => {
        dropdown.classList.add('hidden');
    };

    const selectOption = (value, label, color) => {
        hiddenInput.value = value || '';

        if (value) {
            labelEl.textContent = label || '';
            labelEl.classList.remove('text-atly-ink-soft');
            labelEl.classList.add('text-atly-ink');
        } else {
            labelEl.textContent = placeholder;
            labelEl.classList.add('text-atly-ink-soft');
            labelEl.classList.remove('text-atly-ink');
        }

        colorEl.className = `size-2.5 shrink-0 rounded-full ${dotClass(value && color ? color : '')}`;
        closeDropdown();

        root.dispatchEvent(new CustomEvent('picker:changed', {
            bubbles: true,
            detail: { name: inputName, id: value || null, label },
        }));
    };

    trigger.addEventListener('click', () => {
        if (dropdown.classList.contains('hidden')) {
            openDropdown();
        } else {
            closeDropdown();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closeDropdown();
        }
    });

    searchInput.addEventListener('input', debounce((event) => {
        fetchResults(event.target.value.trim());
    }, 200));

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            closeDropdown();
            trigger.focus();
        }
    });

    results.addEventListener('click', (event) => {
        const button = event.target.closest('[data-picker-option]');
        if (!button) {
            return;
        }
        selectOption(button.dataset.value, button.dataset.label, button.dataset.color);
    });
}

export function initSearchablePickers() {
    document.querySelectorAll('[data-searchable-picker]').forEach(setupPicker);
}
