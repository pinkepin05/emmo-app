document.addEventListener('DOMContentLoaded', function () {
    const fields = document.getElementById('funding-bs-fields');
    if (!fields) return;
    const form = fields.closest('form');
    const date = form.querySelector('[name="tanggal"]');
    const owner = document.getElementById('bs_owner_id_select');
    const info = document.getElementById('bs-saldo-info');
    const display = document.getElementById('bs-balance-display');
    const opening = document.getElementById('bs-opening-info');
    let generation = 0;
    function changed() { document.dispatchEvent(new CustomEvent('bs-balance-updated')); }
    async function refreshBalance() {
        const label = form.querySelector('[data-bs-confirm-label]');
        if (label) label.textContent = owner && owner.value !== fields.dataset.inputter ? 'Simpan & Ajukan Pemakaian' : 'Simpan & Konfirmasi';
        const current = ++generation;
        info.dataset.balance = '';
        display.textContent = 'Memuat...';
        changed();
        const params = new URLSearchParams({module: fields.dataset.module, periode: (date?.value || fields.dataset.period).slice(0, 7)});
        if (fields.dataset.transaction) params.set('transaction_id', fields.dataset.transaction);
        try {
            const response = await fetch(fields.dataset.balanceUrl + '?' + params, {headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error('Saldo tidak dapat dimuat');
            const data = await response.json();
            if (current !== generation) return;
            const selected = String(owner?.value || fields.dataset.inputter);
            const balance = data.owners.find(item => String(item.id) === selected);
            if (!balance) throw new Error('Akses saldo tidak tersedia');
            info.dataset.balance = balance.balance;
            display.textContent = 'Rp ' + new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(balance.balance);
            opening.hidden = balance.opening_final;
        } catch (error) {
            if (current !== generation) return;
            display.textContent = 'Tidak tersedia. Saldo akan diperiksa saat Konfirmasi.';
        }
        changed();
    }
    date?.addEventListener('change', refreshBalance);
    owner?.addEventListener('change', refreshBalance);
    refreshBalance();
});
