@php($sourceDecision = $transaksi->financeChecks()->latest('id')->first())
@if($sourceDecision && in_array($sourceDecision->keputusan, ['REJECTED', 'REVISION'], true))
    <div class="alert alert-warning mb-4">
        <strong>{{ \App\Services\BsUsageCheckService::decisionLabel($sourceDecision->keputusan) }} — {{ $sourceDecision->document_no }}</strong>
        <p class="my-2 text-break" style="white-space:pre-wrap">{{ $sourceDecision->catatan_keputusan }}</p>
        @if($transaksi->status === 'DRAFT' && $transaksi->finance_correction_check_id)
            <p>Simpan perbaikan kemudian konfirmasikan kembali agar Finance dapat memeriksa ulang. Keputusan sebelumnya tetap tersimpan.</p>
        @endif
        <a class="btn btn-sm btn-outline-dark" href="{{ route('transaction-recap.show', [$financeSourceType, $transaksi->id]) }}">Lihat Hasil Finance &amp; Tindak Lanjut</a>
    </div>
@endif
