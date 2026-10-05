(() => {
    // Keep the existing form controls and row handlers when presenting mobile cards.
    document.querySelectorAll('[data-mobile-entry]').forEach(table => {
        const headers = [...table.querySelectorAll('thead th')].map(header => header.textContent.trim().replace(/\s+/g, ' '));
        const labelRows = () => {
            table.querySelectorAll('tbody tr').forEach(row => {
                if (row.cells.length !== headers.length) return;
                [...row.cells].forEach((cell, index) => {
                    cell.dataset.label = headers[index];
                    cell.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(input => {
                        if (!input.labels?.length && !input.hasAttribute('aria-label') && !input.hasAttribute('aria-labelledby')) {
                            input.setAttribute('aria-label', headers[index]);
                        }
                    });
                    cell.querySelectorAll('button').forEach(button => {
                        if (!button.textContent.trim() && !button.hasAttribute('aria-label')) {
                            button.setAttribute('aria-label', button.title || 'Hapus baris');
                        }
                    });
                });
            });
        };
        labelRows();
        table.classList.add('mobile-entry-table');
        new MutationObserver(labelRows).observe(table.tBodies[0], { childList: true, subtree: true });
    });

    // Keyboard users can also reach and scroll wide data tables.
    document.querySelectorAll('.table-responsive').forEach(container => {
        if (container.querySelector('[data-mobile-entry]')) return;
        container.setAttribute('tabindex', '0');
        if (!container.hasAttribute('role')) container.setAttribute('role', 'region');
        if (!container.hasAttribute('aria-label') && !container.hasAttribute('aria-labelledby')) {
            container.setAttribute('aria-label', 'Tabel data, geser ke samping untuk melihat kolom lainnya');
        }
    });
})();
