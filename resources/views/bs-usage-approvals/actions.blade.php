@if($transaksi->status === 'PENDING_OWNER' && $transaksi->bsCashOwnerId() === (int) auth()->id() && app(\App\Services\PeriodService::class)->isOpen($transaksi->periode))
    <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Keputusan Pemohon</h2>
        <p>Periksa seluruh rincian biaya. Persetujuan mengunci pengajuan untuk diperiksa Finance. Saldo BS Anda baru berkurang sesuai nominal yang dikonfirmasi Finance.</p>
        <form method="POST" action="{{ route('bs-usage-approvals.approve', $transaksi->id) }}" class="mb-3" onsubmit="return confirm('Setujui seluruh rincian dan konfirmasi pemakaian saldo BS Anda?');">
            @csrf
            <input type="hidden" name="approval_version" value="{{ $transaksi->approval_version }}">
            <button class="btn btn-success">Setujui &amp; Konfirmasi</button>
        </form>
        <form method="POST" action="{{ route('bs-usage-approvals.reject', $transaksi->id) }}">
            @csrf
            <input type="hidden" name="approval_version" value="{{ $transaksi->approval_version }}">
            <label for="rejection-reason" class="form-label">Alasan penolakan</label>
            <textarea id="rejection-reason" name="reason" class="form-control mb-2" required maxlength="1000" rows="2">{{ old('reason') }}</textarea>
            <button class="btn btn-outline-danger">Tolak Pemakaian</button>
        </form>
    </div></div>
@endif
