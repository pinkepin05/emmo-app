(() => {
    const form = document.getElementById('bs-check-form');
    if (!form) return;
    const format = value => {
        const negative = value < 0n;
        const absolute = negative ? -value : value;
        return (negative ? '-' : '') + (absolute / 100n).toLocaleString('id-ID') + ',' + (absolute % 100n).toString().padStart(2, '0');
    };
    const recalculate = () => {
        let original = 0n;
        let approved = 0n;
        let valid = true;
        form.querySelectorAll('[data-check-item]').forEach(row => {
            const amount = row.querySelector('[data-approved]').value;
            const difference = row.querySelector('[data-difference]');
            if (!/^\d{1,13}(?:\.\d{1,2})?$/.test(amount)) {
                difference.textContent = '—';
                valid = false;
                return;
            }
            const parts = amount.split('.');
            const minor = BigInt(parts[0]) * 100n + BigInt((parts[1] || '').padEnd(2, '0'));
            const base = BigInt(row.dataset.originalMinor);
            original += base;
            approved += minor;
            difference.textContent = format(base - minor);
            difference.classList.toggle('text-danger', base !== minor);
        });
        document.getElementById('check-total-approved').textContent = valid ? format(approved) : '—';
        document.getElementById('check-total-difference').textContent = valid ? format(original - approved) : '—';
        const liveApproved = document.getElementById('check-live-approved');
        const liveDifference = document.getElementById('check-live-difference');
        if (liveApproved) liveApproved.textContent = valid ? 'Rp ' + format(approved) : 'Periksa nominal';
        if (liveDifference) liveDifference.textContent = valid ? 'Rp ' + format(original - approved) : '—';
    };
    form.addEventListener('input', recalculate);
    document.getElementById('tanggal_pengecekan').addEventListener('change', event => {
        document.getElementById('periode').value = event.target.value.slice(0, 7);
    });
    form.addEventListener('submit', event => {
        if (form.dataset.submitting === 'true') { event.preventDefault(); return; }
        const action = event.submitter?.value === 'draft' ? 'draft' : 'confirm';
        // Disabled submit buttons are not included in POST data. Preserve the clicked action.
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'save_action'; input.value = action;
        form.append(input);
        form.dataset.submitting = 'true';
        form.querySelectorAll('button[name="save_action"]').forEach(button => {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            if (button.value === action) button.textContent = action === 'draft' ? 'Menyimpan draft…' : 'Menyimpan konfirmasi…';
        });
    });
    const decision = document.getElementById('keputusan');
    const applyDecision = () => {
        const rejected = decision.value === 'REJECTED';
        form.querySelectorAll('[data-approved]').forEach(input => {
            if (rejected && !input.readOnly) { input.dataset.previousAmount = input.value; input.value = '0'; }
            if (!rejected && input.readOnly) input.value = input.dataset.previousAmount || '0';
            input.readOnly = rejected;
        });
        document.getElementById('catatan_keputusan').required = rejected;
        const help = document.getElementById('check-decision-help');
        const required = document.getElementById('check-note-required');
        const label = document.getElementById('check-approved-label');
        if (required) required.textContent = rejected ? '* Wajib saat konfirmasi' : '';
        if (label) label.textContent = 'Total disetujui';
        if (help) help.textContent = {
            APPROVED: 'Nilai ini menjadi persetujuan Finance. Selisih tetap ditampilkan pada laporan agar dapat ditinjau.',
            REJECTED: 'Pengajuan ditolak dan nominal persetujuan menjadi nol. Alasan wajib diisi. User dapat memperbaiki dan mengonfirmasi pengajuan kembali untuk diperiksa ulang.'
        }[decision.value] || 'Pilih Disetujui Finance atau Ditolak sebelum menyimpan pemeriksaan.';
        recalculate();
    };
    if (decision) { decision.addEventListener('change', applyDecision); applyDecision(); }
    else recalculate();
})();
