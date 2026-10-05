(() => {
    const sidebar = document.getElementById('appSidebar');
    const toggle = document.getElementById('menuToggle');
    const closeButton = document.getElementById('sidebarClose');
    const overlay = document.getElementById('sidebarOverlay');
    const main = document.querySelector('.main-content');
    const mobile = window.matchMedia('(max-width: 991.98px)');
    const desktopCollapsedClass = 'sidebar-desktop-collapsed';
    const storageKey = 'emmo.sidebar.collapsed';

    if (!sidebar || !toggle || !main) return;

    function updateToggle(expanded) {
        const label = expanded ? 'Tutup menu navigasi' : 'Buka menu navigasi';

        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
    }

    function isDesktopCollapsed() {
        return document.documentElement.classList.contains(desktopCollapsedClass);
    }

    function saveDesktopState(collapsed) {
        try {
            localStorage.setItem(storageKey, collapsed ? '1' : '0');
        } catch (_) {}
    }

    function setDesktopCollapsed(collapsed, persist = true) {
        document.documentElement.classList.toggle(desktopCollapsedClass, collapsed);
        sidebar.inert = collapsed;
        updateToggle(!collapsed);

        if (persist) saveDesktopState(collapsed);
    }

    function setMobileOpen(open, restoreFocus = true) {
        const isOpen = Boolean(open);

        sidebar.classList.toggle('is-open', isOpen);
        document.body.classList.toggle('sidebar-open', isOpen);
        updateToggle(isOpen);
        sidebar.inert = !isOpen;
        main.inert = isOpen;

        if (isOpen) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
            closeButton?.focus();
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');

            if (restoreFocus) toggle.focus();
        }
    }

    toggle.addEventListener('click', () => {
        if (mobile.matches) {
            setMobileOpen(!sidebar.classList.contains('is-open'));
            return;
        }

        setDesktopCollapsed(!isDesktopCollapsed());
    });
    closeButton?.addEventListener('click', () => setMobileOpen(false));
    overlay?.addEventListener('click', () => setMobileOpen(false));

    sidebar.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', () => {
            if (mobile.matches) setMobileOpen(false);
        });
    });

    document.addEventListener('keydown', event => {
        if (!mobile.matches || !sidebar.classList.contains('is-open')) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            setMobileOpen(false);
        }

        if (event.key === 'Tab') {
            const items = [...sidebar.querySelectorAll('a[href], button:not([disabled]), summary')]
                .filter(element => element.getClientRects().length > 0
                    && (element.matches('summary') || !element.closest('details:not([open])')));
            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });

    mobile.addEventListener('change', () => {
        const hadSidebarFocus = sidebar.contains(document.activeElement);
        sidebar.classList.remove('is-open');
        document.body.classList.remove('sidebar-open');
        main.inert = false;

        if (mobile.matches) {
            sidebar.inert = true;
            updateToggle(false);
            if (hadSidebarFocus) toggle.focus();
        } else {
            setDesktopCollapsed(isDesktopCollapsed(), false);
        }
    });

    window.addEventListener('pageshow', () => {
        if (mobile.matches) {
            setMobileOpen(false, false);
        } else {
            setDesktopCollapsed(isDesktopCollapsed(), false);
        }
    });

    if (mobile.matches) {
        setMobileOpen(false, false);
    } else {
        setDesktopCollapsed(isDesktopCollapsed(), false);
    }
})();
