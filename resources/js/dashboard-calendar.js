export function initDashboardCalendar() {
    const calendar = document.querySelector('[data-calendar]');

    if (! calendar) {
        return;
    }

    const baseUrl = calendar.dataset.calendarUrl;

    if (! baseUrl) {
        return;
    }

    let inFlight = null;

    const loadMonth = async (month, pushState = true) => {
        if (! month) {
            return;
        }

        if (inFlight) {
            inFlight.abort();
        }

        const controller = new AbortController();
        inFlight = controller;

        const url = new URL(baseUrl, window.location.origin);
        url.searchParams.set('month', month);
        url.searchParams.set('calendar_only', '1');

        calendar.classList.add('opacity-60', 'pointer-events-none');

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controller.signal,
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            calendar.innerHTML = data.html;

            if (pushState) {
                const pageUrl = new URL(window.location.href);
                pageUrl.searchParams.set('month', data.month);
                pageUrl.searchParams.delete('calendar_only');
                window.history.pushState({ atlyCalendarMonth: data.month }, '', pageUrl.toString());
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = url.toString();
            }
        } finally {
            if (inFlight === controller) {
                inFlight = null;
            }

            calendar.classList.remove('opacity-60', 'pointer-events-none');
        }
    };

    calendar.addEventListener('click', (event) => {
        const link = event.target.closest('[data-calendar-nav]');

        if (! link || ! calendar.contains(link)) {
            return;
        }

        event.preventDefault();
        loadMonth(link.dataset.month);
    });

    window.addEventListener('popstate', (event) => {
        const month = event.state?.atlyCalendarMonth
            ?? new URL(window.location.href).searchParams.get('month');

        if (month) {
            loadMonth(month, false);
        }
    });
}
