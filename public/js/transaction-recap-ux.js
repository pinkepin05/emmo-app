/* ============================================
   ENHANCED UX - Transaction Recap Scripts
   Interactive features for better UX
   ============================================ */

class TransactionRecapUX {
    constructor() {
        this.init();
    }

    init() {
        this.setupFilterChips();
        this.setupToastNotifications();
        this.setupQuickActions();
        this.setupTableEnhancements();
        this.setupLoadingStates();
        this.setupKeyboardShortcuts();
    }

    /* ===== Filter Chips ===== */
    setupFilterChips() {
        const filterForm = document.querySelector('.recap-filter');
        if (!filterForm) return;

        this.updateFilterChips();
        
        filterForm.addEventListener('submit', (e) => {
            setTimeout(() => this.updateFilterChips(), 100);
        });
    }

    updateFilterChips() {
        const chipsContainer = document.querySelector('.filter-chips');
        if (!chipsContainer) return;

        const urlParams = new URLSearchParams(window.location.search);
        const filters = [];

        const filterMap = {
            'pemakaian_periode': { label: 'Periode', icon: 'bi-calendar' },
            'pemakaian_jenis': { label: 'Modul', icon: 'bi-grid' },
            'pemakaian_status': { label: 'Status', icon: 'bi-check-circle' },
            'pemakaian_referensi': { label: 'Referensi', icon: 'bi-search' }
        };

        const statusLabels = {
            '': 'Semua status',
            'CHECKED': 'Sudah Dicek',
            'FOLLOW_UP': 'Perlu Tindak Lanjut',
            'PENDING': 'Belum Dicek',
            'REVISION': 'Perlu Revisi (keputusan lama)',
            'APPROVED': 'Dikonfirmasi Finance',
            'REJECTED': 'Ditolak Penuh',
            'NOT_APPROVED': 'Ditolak Penuh / Sebagian',
            'DIFFERENCE': 'Selisih Konfirmasi Finance'
        };

        const typeLabels = {
            'OPR': 'Operasional Sales',
            'PD': 'Perjalanan Dinas',
            'EDK': 'Event Dalam Kota',
            'ELK': 'Event Luar Kota'
        };

        urlParams.forEach((value, key) => {
            if (filterMap[key] && value) {
                let displayValue = value;
                
                if (key === 'pemakaian_status') {
                    displayValue = statusLabels[value] || value;
                } else if (key === 'pemakaian_jenis') {
                    displayValue = typeLabels[value] || value;
                } else if (key === 'pemakaian_periode') {
                    const [year, month] = value.split('-');
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    displayValue = `${months[parseInt(month) - 1]} ${year}`;
                }

                filters.push({
                    key,
                    label: filterMap[key].label,
                    icon: filterMap[key].icon,
                    value: displayValue,
                    rawValue: value
                });
            }
        });

        chipsContainer.innerHTML = '';
        
        if (filters.length === 0) {
            chipsContainer.style.display = 'none';
            return;
        }

        chipsContainer.style.display = 'flex';
        
        const label = document.createElement('span');
        label.className = 'filter-chips-label';
        label.textContent = 'Filter aktif:';
        chipsContainer.appendChild(label);

        filters.forEach(filter => {
            const chip = document.createElement('div');
            chip.className = 'filter-chip';
            
            const icon = document.createElement('i');
            icon.className = `bi ${filter.icon}`;
            icon.setAttribute('aria-hidden', 'true');
            chip.appendChild(icon);
            
            const text = document.createElement('span');
            text.textContent = `${filter.label}: ${filter.value}`;
            chip.appendChild(text);
            
            const removeBtn = document.createElement('a');
            removeBtn.className = 'filter-chip-remove';
            removeBtn.href = '#';
            removeBtn.innerHTML = '&times;';
            removeBtn.setAttribute('aria-label', `Hapus filter ${filter.label}`);
            removeBtn.onclick = (e) => {
                e.preventDefault();
                this.removeFilter(filter.key);
            };
            chip.appendChild(removeBtn);
            
            chipsContainer.appendChild(chip);
        });

        if (filters.length > 1) {
            const clearAll = document.createElement('a');
            clearAll.className = 'filter-chip';
            clearAll.href = '#';
            clearAll.innerHTML = '<i class="bi bi-x-circle" aria-hidden="true"></i> Hapus semua';
            clearAll.onclick = (e) => {
                e.preventDefault();
                this.clearAllFilters();
            };
            chipsContainer.appendChild(clearAll);
        }
    }

    removeFilter(key) {
        const url = new URL(window.location);
        url.searchParams.delete(key);
        window.location = url.toString();
    }

    clearAllFilters() {
        const url = new URL(window.location);
        const paramsToDelete = ['pemakaian_periode', 'pemakaian_jenis', 'pemakaian_status', 'pemakaian_referensi'];
        paramsToDelete.forEach(key => url.searchParams.delete(key));
        window.location = url.toString();
    }

    /* ===== Toast Notifications ===== */
    setupToastNotifications() {
        const flashMessage = document.querySelector('[data-flash-message]');
        if (flashMessage) {
            const type = flashMessage.dataset.flashType || 'info';
            const message = flashMessage.textContent;
            this.showToast(message, type);
        }

        document.querySelectorAll('form[data-ajax]').forEach(form => {
            form.addEventListener('submit', (e) => {
                this.showToast('Permintaan sedang diproses...', 'info');
            });
        });
    }

    showToast(message, type = 'info', title = '') {
        const container = this.getOrCreateToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-x-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };

        toast.innerHTML = `
            <div class="toast-icon">
                <i class="bi ${icons[type] || icons.info}"></i>
            </div>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" aria-label="Tutup">
                <i class="bi bi-x"></i>
            </button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            this.removeToast(toast);
        }, 5000);

        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.removeToast(toast);
        });
    }

    getOrCreateToastContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    removeToast(toast) {
        toast.classList.add('toast-hiding');
        setTimeout(() => {
            toast.remove();
        }, 250);
    }

    /* ===== Quick Actions ===== */
    setupQuickActions() {
        const quickActionsBar = document.querySelector('.quick-actions-bar');
        if (!quickActionsBar) return;

        quickActionsBar.addEventListener('keydown', (e) => {
            const buttons = quickActionsBar.querySelectorAll('.quick-action-btn');
            const currentIndex = Array.from(buttons).indexOf(document.activeElement);
            
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                const nextIndex = (currentIndex + 1) % buttons.length;
                buttons[nextIndex].focus();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                const prevIndex = (currentIndex - 1 + buttons.length) % buttons.length;
                buttons[prevIndex].focus();
            }
        });
    }

    /* ===== Table Enhancement ===== */
    setupTableEnhancements() {
        const table = document.querySelector('.usage-table');
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', (e) => {
                if (e.target.closest('a, button')) return;
                row.classList.toggle('selected');
            });
        });

        table.addEventListener('keydown', (e) => {
            const focusedRow = document.activeElement.closest('tr');
            if (!focusedRow) return;

            const allRows = Array.from(table.querySelectorAll('tbody tr'));
            const currentIndex = allRows.indexOf(focusedRow);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const nextRow = allRows[currentIndex + 1];
                if (nextRow) nextRow.querySelector('a, button')?.focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevRow = allRows[currentIndex - 1];
                if (prevRow) prevRow.querySelector('a, button')?.focus();
            }
        });
    }

    /* ===== Loading States ===== */
    setupLoadingStates() {
        document.querySelectorAll('[data-loading]').forEach(button => {
            button.addEventListener('click', (e) => {
                if (button.classList.contains('loading')) {
                    e.preventDefault();
                    return;
                }
                
                button.classList.add('loading');
                const originalText = button.textContent;
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
                
                setTimeout(() => {
                    button.classList.remove('loading');
                    button.disabled = false;
                    button.textContent = originalText;
                }, 3000);
            });
        });
    }

    /* ===== Keyboard Shortcuts ===== */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('#pemakaian_referensi');
                if (searchInput) searchInput.focus();
            }
            
            if (e.key === 'Escape') {
                document.querySelectorAll('.toast').forEach(toast => {
                    this.removeToast(toast);
                });
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new TransactionRecapUX();
    });
} else {
    new TransactionRecapUX();
}

window.TransactionRecapUX = TransactionRecapUX;
