(() => {
    const modal = document.getElementById('decisionModal');
    const form = document.getElementById('decisionForm');
    if (!modal || !form) return;
    const checks = [...document.querySelectorAll('[data-approval-select]')];
    const selectAll = document.getElementById('selectAllPending');
    const clear = document.getElementById('clearSelection');
    const batchButtons = [...document.querySelectorAll('[data-batch-action]')];
    const message = document.getElementById('decisionMessage');
    const submit = document.getElementById('decisionSubmitBtn');
    const hiddenIds = document.getElementById('batchHiddenInputs');
    const selected = () => checks.filter(input => input.checked);
    const total = inputs => inputs.reduce((sum, input) => sum + BigInt(input.dataset.amountMinor), 0n);
    const rupiah = minor => 'Rp ' + (minor / 100n).toLocaleString('id-ID') + ',' + (minor % 100n).toString().padStart(2, '0');

    const updateSelection = () => {
        const chosen = selected();
        document.getElementById('batchCount').textContent = chosen.length;
        document.getElementById('batchTotal').textContent = rupiah(total(chosen));
        document.getElementById('batchLimit').hidden = chosen.length <= 100;
        selectAll.checked = checks.length > 0 && chosen.length === checks.length;
        selectAll.indeterminate = chosen.length > 0 && chosen.length < checks.length;
        selectAll.disabled = checks.length === 0;
        clear.disabled = chosen.length === 0;
        batchButtons.forEach(button => { button.disabled = chosen.length === 0 || chosen.length > 100; });
        checks.forEach(input => input.closest('[data-approval-row]').classList.toggle('is-selected', input.checked));
    };
    checks.forEach(input => input.addEventListener('change', updateSelection));
    document.querySelectorAll('[data-selectable]').forEach(row => {
        row.addEventListener('click', event => {
            if (event.target.closest('a, button, input, textarea, select, label')) return;
            const input = row.querySelector('[data-approval-select]');
            input.checked = !input.checked;
            updateSelection();
        });
    });
    selectAll.addEventListener('change', () => {
        checks.forEach(input => { input.checked = selectAll.checked; });
        updateSelection();
    });
    clear.addEventListener('click', () => {
        checks.forEach(input => { input.checked = false; });
        updateSelection();
    });
    document.getElementById('periodSelect')?.addEventListener('change', event => {
        const url = new URL(window.location.href);
        url.searchParams.set('periode', event.target.value);
        window.location.href = url.toString();
    });

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        if (!button) { event.preventDefault(); return; }
        const batch = button.hasAttribute('data-batch-action');
        const chosen = batch ? selected() : [];
        if (batch && (chosen.length === 0 || chosen.length > 100)) { event.preventDefault(); return; }
        const approve = button.dataset.mode === 'approve';
        form.action = button.dataset.action;
        form.dataset.batch = batch ? 'true' : 'false';
        form.dataset.submitting = 'false';
        hiddenIds.replaceChildren();
        const list = document.getElementById('decisionBatchList');
        list.replaceChildren();
        chosen.forEach(input => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden'; hidden.name = 'ids[]'; hidden.value = input.value;
            hiddenIds.append(hidden);
            const item = document.createElement('li');
            item.textContent = input.dataset.docno + ' · ' + rupiah(BigInt(input.dataset.amountMinor));
            list.append(item);
        });
        document.getElementById('decisionBatchSummary').hidden = !batch;
        document.getElementById('decisionTotal').textContent = batch ? rupiah(total(chosen)) : '';
        document.getElementById('decisionDocNo').textContent = batch ? chosen.length + ' pengajuan terpilih' : button.dataset.docno;
        document.getElementById('decisionTitle').textContent = (approve ? 'Setujui' : 'Tolak') + (batch ? ' Pengajuan Terpilih' : ' Pengajuan');
        document.getElementById('decisionSubtitle').textContent = approve
            ? (batch ? 'Pesan berikut akan diterapkan ke semua pengajuan terpilih dan diteruskan ke Finance.' : 'Tambahkan pesan atau catatan untuk pemohon (opsional).')
            : (batch ? 'Alasan penolakan berikut akan diterapkan ke semua pengajuan terpilih.' : 'Mohon berikan alasan penolakan agar pemohon dapat memperbaikinya.');
        document.getElementById('decisionLabel').textContent = approve ? 'Pesan Persetujuan (Opsional)' : 'Alasan Penolakan (Wajib)';
        message.name = approve ? 'approval_message' : 'rejection_reason';
        message.placeholder = approve ? 'Contoh: OK, segera cairkan di Finance...' : 'Tuliskan alasan penolakan...';
        message.required = !approve;
        message.value = approve ? form.dataset.oldApprovalMessage : form.dataset.oldRejectionReason;
        message.setCustomValidity('');
        document.getElementById('charCount').textContent = message.value.length;
        submit.disabled = false;
        submit.removeAttribute('aria-busy');
        submit.className = 'btn ' + (approve ? 'btn-success' : 'btn-danger');
        submit.textContent = (approve ? 'Ya, Setujui' : 'Ya, Tolak') + (batch ? ' ' + chosen.length + ' Pengajuan' : '');
    });
    message.addEventListener('input', () => {
        document.getElementById('charCount').textContent = message.value.length;
        message.setCustomValidity('');
    });
    form.addEventListener('submit', event => {
        if (form.dataset.submitting === 'true' || (form.dataset.batch === 'true' && hiddenIds.children.length === 0)) {
            event.preventDefault();
            return;
        }
        if (message.required && !message.value.trim()) {
            event.preventDefault();
            message.setCustomValidity('Alasan penolakan wajib diisi.');
            message.reportValidity();
            return;
        }
        form.dataset.submitting = 'true';
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        submit.textContent = 'Memproses...';
    });
    updateSelection();
})();
