(() => {
    const init = () => {
        const panel = document.querySelector('[data-recap-dashboard]');
        if (!panel) return;

        const tabs = [...panel.querySelectorAll('[data-recap-tab]')];
        const modes = ['ringkasan', 'daftar'];
        const dashboardPath = location.pathname;
        const modeFromUrl = () => {
            const value = new URL(location.href).searchParams.get('tampilan');
            if (value === 'pengajuan' || value === 'finance') return 'daftar';
            return modes.includes(value) ? value : 'ringkasan';
        };

        const display = (mode, announce = false) => {
            panel.dataset.recapView = mode;
            tabs.forEach(tab => {
                if (tab.dataset.recapTab === mode) tab.setAttribute('aria-current', 'page');
                else tab.removeAttribute('aria-current');
                // Switching views keeps the current page and applied filters.
                const url = new URL(location.href);
                url.searchParams.set('tampilan', tab.dataset.recapTab);
                url.hash = 'pemakaian-bs';
                tab.href = url.href;
            });
            panel.querySelectorAll('[data-recap-overview]').forEach(el => { el.hidden = mode !== 'ringkasan'; });
            panel.querySelectorAll('[data-recap-report]').forEach(el => { el.hidden = mode === 'ringkasan'; });
            panel.querySelector('[name="tampilan"]').value = mode;

            // Filters, pagination and return links follow the selected view.
            // Module cards and Finance shortcuts intentionally select their own view.
            panel.querySelectorAll('a[href]').forEach(link => {
                if (link.closest('.recap-nav, .usage-modules, .recap-head-actions')) return;
                const url = new URL(link.href, location.href);
                if (url.origin !== location.origin) return;
                if (url.pathname === dashboardPath || url.searchParams.get('dari') === 'dashboard') {
                    url.searchParams.set('tampilan', mode);
                    link.href = url.href;
                }
            });
            if (announce) {
                const label = tabs.find(tab => tab.dataset.recapTab === mode).textContent.trim();
                panel.querySelector('[data-recap-announcement]').textContent = 'Tampilan ' + label + ' aktif.';
            }
        };

        tabs.forEach(tab => tab.addEventListener('click', event => {
            // Preserve native open-in-new-tab gestures and the no-JavaScript fallback.
            if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            const mode = tab.dataset.recapTab;
            if (panel.dataset.recapView === mode) return;
            history.pushState(null, '', tab.href);
            display(mode, true);
        }));
        window.addEventListener('popstate', () => display(modeFromUrl(), true));
        window.addEventListener('pageshow', () => display(modeFromUrl()));
        const initialUrl = new URL(location.href);
        if (['pengajuan', 'finance'].includes(initialUrl.searchParams.get('tampilan'))) {
            initialUrl.searchParams.set('tampilan', 'daftar');
            history.replaceState(history.state, '', initialUrl.href);
        }
        display(modeFromUrl());
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
