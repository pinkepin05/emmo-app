(() => {
    const form = document.getElementById('bs-check-form');
    if (!form) return;
    const format = value => {
        const negative = value < 0n;
        const absolute = negative ? -value : value;
        return (negative ? '-' : '') + (absolute / 100n).toLocaleString('id-ID') + ',' + (absolute % 100n).toString().padStart(2, '0');
    };
    const decimal = minor => (minor / 100n).toString() + '.' + (minor % 100n).toString().padStart(2, '0');
    const parseDecimal = value => {
        if (!/^\d{1,13}(?:\.\d{1,2})?$/.test(value)) return null;
        const [whole, fraction = ''] = value.split('.');
        return BigInt(whole) * 100n + BigInt(fraction.padEnd(2, '0'));
    };
    const parseDisplay = value => {
        // Indonesian input: dots group thousands, a comma separates cents.
        if (!/^(?:\d{1,13}|\d{1,3}(?:\.\d{3}){1,4})(?:,\d{0,2})?$/.test(value)) return null;
        return parseDecimal(value.replace(/\./g, '').replace(/,$/, '').replace(',', '.'));
    };
    const formatInput = minor => minor % 100n === 0n ? (minor / 100n).toLocaleString('id-ID') : format(minor);
    const rows = [...form.querySelectorAll('[data-check-item]')];
    const sync = row => {
        const input = row.querySelector('[data-approved]');
        const raw = row.querySelector('[data-approved-value]');
        const value = input.value.trim();
        const minor = raw ? parseDisplay(value) : parseDecimal(value);
        const invalid = value !== '' && minor === null;
        input.setCustomValidity(invalid ? 'Gunakan nominal seperti 70.000 atau 70.000,50, maksimal dua angka desimal.' : '');
        // POST values always use decimal rupiah; calculations below use integer cents.
        if (raw) raw.value = minor === null ? '' : decimal(minor);
        return { input, minor, invalid };
    };
    rows.forEach(row => {
        const input = row.querySelector('[data-approved]');
        const raw = row.querySelector('[data-approved-value]');
        if (!raw) return;
        const initial = parseDecimal(raw.value);
        input.value = initial === null ? raw.value : formatInput(initial);
        input.addEventListener('focus', () => {
            const { minor } = sync(row);
            if (minor !== null) input.value = decimal(minor).replace(/\.00$/, '').replace('.', ',');
        });
        input.addEventListener('blur', () => {
            const { minor } = sync(row);
            if (minor !== null) input.value = formatInput(minor);
            recalculate();
        });
    });
    const recalculate = () => {
        let original = 0n;
        let approved = 0n;
        let valid = true;
        rows.forEach(row => {
            const { minor } = sync(row);
            const difference = row.querySelector('[data-difference]');
            const base = BigInt(row.dataset.originalMinor);
            original += base;
            if (minor === null) {
                difference.textContent = '—';
                difference.classList.remove('text-danger');
                valid = false;
                return;
            }
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
    form.addEventListener('change', recalculate);
    document.getElementById('tanggal_pengecekan').addEventListener('change', event => {
        document.getElementById('periode').value = event.target.value.slice(0, 7);
    });
    form.addEventListener('submit', event => {
        if (form.dataset.submitting === 'true') { event.preventDefault(); return; }
        const action = event.submitter?.value === 'draft' ? 'draft' : 'confirm';
        recalculate();
        const amounts = rows.map(sync);
        const invalid = amounts.find(amount => amount.invalid || (action === 'confirm' && amount.minor === null));
        if (invalid) {
            event.preventDefault();
            if (!invalid.invalid) invalid.input.setCustomValidity('Isi nominal disetujui sebelum konfirmasi. Gunakan 0 untuk menolak item.');
            invalid.input.reportValidity();
            return;
        }
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
        if (label) label.textContent = 'Total Konfirmasi';
        if (help) help.textContent = {
            APPROVED: 'Nilai ini menjadi persetujuan Finance. Selisih tetap ditampilkan pada laporan agar dapat ditinjau.',
            REJECTED: 'Pengajuan ditolak dan nominal persetujuan menjadi nol. Alasan wajib diisi. User dapat memperbaiki dan mengonfirmasi pengajuan kembali untuk diperiksa ulang.'
        }[decision.value] || 'Pilih Disetujui Finance atau Ditolak sebelum menyimpan pemeriksaan.';
        recalculate();
    };
    if (decision) { decision.addEventListener('change', applyDecision); applyDecision(); }
    else recalculate();
})();
