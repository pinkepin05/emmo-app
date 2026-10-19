@php
    $reviewOpen = app(\App\Services\PeriodService::class)->isOpen($transaksi->periode);
    $isInputter = (int) auth()->id() === (int) $transaksi->user_id;
@endphp
<div class="alert {{ $transaksi->status === 'VALIDATED' ? 'alert-success' : ($transaksi->status === 'REJECTED_OWNER' ? 'alert-danger' : 'alert-warning') }}">
    <strong>{{ $transaksi->status_label }}</strong>
    @if($transaksi->status === 'DRAFT')
        <p class="mb-0">Saldo BS baru berkurang setelah konfirmasi Finance. {{ $transaksi->needsOwnerApproval() ? 'Konfirmasi anggota mengirim permintaan kepada pemohon untuk disetujui sebelum pengecekan Finance.' : 'Periksa rincian lalu Konfirmasi untuk mengirim pengajuan ke Finance.' }}</p>
        @if($canEdit ?? false)
            <form class="mt-3" method="POST" action="{{ route('transaksi_perjalanan_dinas.validate', $transaksi->transaksi_id) }}" onsubmit="return confirm('{{ $transaksi->needsOwnerApproval() ? 'Kirim rincian ini kepada pemohon untuk persetujuan pemakaian BS?' : 'Konfirmasi transaksi ini? Pemakaian BS akan dibukukan dan transaksi tidak dapat diubah.' }}');">
                @csrf
                <button class="btn btn-success">{{ $transaksi->needsOwnerApproval() ? 'Konfirmasi & Ajukan Pemakaian BS' : 'Konfirmasi' }}</button>
            </form>
        @endif
    @elseif($transaksi->status === 'PENDING_OWNER')
        <p class="mb-1">Diajukan kepada {{ $transaksi->bsOwner?->nama ?? $transaksi->bonSementara?->user?->nama ?? 'pemilik saldo' }} pada {{ \App\Support\AppTime::format($transaksi->submitted_at) }}.</p>
        <p class="mb-0">Saldo belum berkurang. Rincian terkunci selama menunggu persetujuan. Permintaan tidak disetujui otomatis.</p>
        @if($reviewOpen && $isInputter)
            <form class="mt-3" method="POST" action="{{ route('transaksi_perjalanan_dinas.withdraw', $transaksi->transaksi_id) }}" onsubmit="return confirm('Tarik permintaan dan kembalikan transaksi menjadi draft?');">
                @csrf
                <input type="hidden" name="approval_version" value="{{ $transaksi->approval_version }}">
                <button class="btn btn-outline-secondary">Tarik Permintaan ke Draft</button>
            </form>
        @endif
    @elseif($transaksi->status === 'REJECTED_OWNER')
        <p class="mb-1">Alasan: {{ $transaksi->owner_rejection_reason }}</p>
        <p class="mb-0">Saldo tidak berkurang. Anggota dapat memperbaiki dan mengajukan ulang.</p>
        @if($reviewOpen && $isInputter)
            <form class="mt-3" method="POST" action="{{ route('transaksi_perjalanan_dinas.revise', $transaksi->transaksi_id) }}">
                @csrf
                <input type="hidden" name="approval_version" value="{{ $transaksi->approval_version }}">
                <button class="btn btn-outline-secondary">Buka Draft untuk Perbaikan</button>
            </form>
        @endif
    @elseif($transaksi->status === 'VALIDATED')
        <p class="mb-0">Transaksi sudah final dan tidak dapat diubah.</p>
        <div class="mt-2">Waktu Konfirmasi: <strong>{{ \App\Support\AppTime::format($transaksi->validated_at) }}</strong></div>
    @endif
</div>
