(() => {
    // Native links keep keyboard, open-in-new-tab and no-JavaScript navigation.
    // Clicking the remaining card surface opens that same module list.
    document.querySelectorAll('[data-module-card]').forEach(card => {
        card.addEventListener('click', event => {
            if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            if (event.target.closest('a, button, input, select, textarea, summary')) return;
            if (window.getSelection()?.toString()) return;
            const link = card.querySelector('.recap-module-link');
            if (link) link.click();
        });
    });
})();
