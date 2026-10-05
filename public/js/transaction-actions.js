(() => {
    let active;
    const close = (restoreFocus = false) => {
        if (!active) return;
        const { wrapper, toggle, menu } = active;
        active = null;
        menu.hidden = true;
        wrapper.append(menu);
        toggle.setAttribute('aria-expanded', 'false');
        if (restoreFocus) toggle.focus({ preventScroll: true });
    };
    const items = () => [...active.menu.querySelectorAll('a[href], button:not(:disabled)')];
    const open = (wrapper, focusLast = false) => {
        close();
        const toggle = wrapper.querySelector('[data-action-toggle]');
        const menu = wrapper.querySelector('[data-action-menu]');
        active = { wrapper, toggle, menu };
        // Render outside the scrolling table so the last row's menu is never clipped.
        document.body.append(menu);
        menu.hidden = false;
        menu.style.maxHeight = `${window.innerHeight - 16}px`;
        const rect = toggle.getBoundingClientRect();
        const height = menu.offsetHeight;
        const below = window.innerHeight - rect.bottom - 8;
        const above = rect.top - 8;
        const opensAbove = height > below && above > below;
        menu.style.maxHeight = `${Math.max(44, opensAbove ? above - 6 : below - 6)}px`;
        menu.style.left = `${Math.max(8, Math.min(rect.right - menu.offsetWidth, window.innerWidth - menu.offsetWidth - 8))}px`;
        menu.style.top = `${Math.max(8, opensAbove ? rect.top - menu.offsetHeight - 6 : rect.bottom + 6)}px`;
        toggle.setAttribute('aria-expanded', 'true');
        const choices = items();
        choices[focusLast ? choices.length - 1 : 0]?.focus({ preventScroll: true });
    };
    document.addEventListener('click', event => {
        const toggle = event.target.closest('[data-action-toggle]');
        if (toggle) {
            if (active?.toggle === toggle) close(true);
            else open(toggle.closest('[data-transaction-actions]'));
        } else if (active && !active.menu.contains(event.target)) close();
    });
    document.addEventListener('keydown', event => {
        const toggle = event.target.closest('[data-action-toggle]');
        if (toggle && ['ArrowDown', 'ArrowUp'].includes(event.key)) {
            event.preventDefault();
            open(toggle.closest('[data-transaction-actions]'), event.key === 'ArrowUp');
            return;
        }
        if (!active) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            close(true);
        } else if (active.menu.contains(event.target)) {
            const choices = items();
            const index = choices.indexOf(document.activeElement);
            if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                event.preventDefault();
                const next = event.key === 'Home' ? 0 : event.key === 'End' ? choices.length - 1
                    : (index + (event.key === 'ArrowDown' ? 1 : -1) + choices.length) % choices.length;
                choices[next].focus();
            } else if (event.key === 'Tab' && ((event.shiftKey && index === 0) || (!event.shiftKey && index === choices.length - 1))) {
                close(true);
                if (event.shiftKey) event.preventDefault();
            }
        }
    });
    document.addEventListener('focusin', event => {
        if (active && !active.menu.contains(event.target) && event.target !== active.toggle) close();
    });
    document.addEventListener('scroll', event => {
        if (active && !active.menu.contains(event.target)) close();
    }, true);
    window.addEventListener('resize', () => close());
})();
