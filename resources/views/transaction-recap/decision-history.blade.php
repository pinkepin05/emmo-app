@if($financeHistory->isNotEmpty())
    <section class="recap-card" aria-labelledby="decision-history-title">
        <h2 id="decision-history-title">Riwayat keputusan Finance</h2>
        <p class="small text-muted">Keputusan dan nominal berikut mengikuti versi pengajuan saat diperiksa. Perbaikan atau keputusan baru tidak menghapus riwayat.</p>
        @foreach($financeHistory as $decision)
            <article class="border rounded p-3 mb-3">
                <strong>{{ \App\Services\BsUsageCheckService::decisionLabel($decision->keputusan) }}</strong>
                <div class="recap-meta">{{ $decision->document_no }} · {{ \App\Support\AppTime::format($decision->created_at) }} · {{ $decision->checker_name }}</div>
                <p class="small my-2">Pengajuan Rp {{ number_format($decision->total_asal, 2, ',', '.') }}
                    @if($decision->keputusan !== 'REVISION') · Disetujui Rp {{ number_format($decision->total_disetujui, 2, ',', '.') }}@endif
                </p>
                @if($decision->catatan_keputusan)<p class="small text-break mb-0" style="white-space:pre-wrap">{{ $decision->catatan_keputusan }}</p>@endif
            </article>
        @endforeach
        {{ $financeHistory->links() }}
    </section>
@endif
