@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/finance-audit-trail.css') }}?v={{ filemtime(public_path('css/finance-audit-trail.css')) }}">
    @endpush
@endonce
<section class="finance-audit" aria-labelledby="decision-history-title">
    <h2 id="decision-history-title"><i class="bi bi-clock-history" aria-hidden="true"></i> Jejak Audit (Audit Trail)</h2>
    <div class="finance-audit-panel">
        <p class="finance-audit-context">Riwayat pemeriksaan <strong>{{ $auditReference }}</strong> · {{ $auditHistory->total() }} pemeriksaan</p>
        @if($auditHistory->isNotEmpty())
            <p class="finance-audit-hint">Urutan dari pemeriksaan awal hingga cek ulang. Setiap hasil mengikuti pengajuan saat pemeriksaan dikonfirmasi.</p>
            <ol class="finance-audit-timeline" aria-label="Jejak pemeriksaan transaksi">
                @foreach($auditHistory as $auditEntry)
                    @php
                        [$auditTone, $auditIcon] = match($auditEntry->keputusan) {
                            'APPROVED' => ['approved', 'bi-check-circle'],
                            'REJECTED' => ['rejected', 'bi-x-circle'],
                            'REVISION' => ['revision', 'bi-pencil-square'],
                            default => ['neutral', 'bi-clipboard-check'],
                        };
                        $auditNumber = $auditHistory->firstItem() + $loop->index;
                        $auditOriginal = $auditEntry->details->sum(fn ($detail) => \App\Support\Money::minor($detail->nominal_asal));
                        $auditApproved = $auditEntry->details->sum(fn ($detail) => \App\Support\Money::minor($detail->nominal_disetujui));
                        $auditMoney = fn ($minor) => 'Rp '.number_format(\App\Support\Money::decimal($minor), 2, ',', '.');
                    @endphp
                    <li class="finance-audit-entry {{ $auditTone }}" data-audit-check="{{ $auditEntry->id }}">
                        <span class="finance-audit-icon" aria-hidden="true"><i class="bi {{ $auditIcon }}"></i></span>
                        <article class="finance-audit-content">
                            <div class="finance-audit-heading">
                                <h3>{{ $auditNumber === 1 ? 'Pemeriksaan Finance' : 'Cek Ulang Finance' }} — {{ \App\Services\BsUsageCheckService::decisionLabel($auditEntry->keputusan) }}</h3>
                                @if((int) ($auditSelectedId ?? 0) === (int) $auditEntry->id)<span class="finance-audit-selected">Hasil yang ditampilkan</span>@endif
                            </div>
                            <p class="finance-audit-meta">Oleh: <strong>{{ $auditEntry->checker_name }}</strong> · {{ \App\Support\AppTime::format($auditEntry->created_at) }}</p>
                            <p class="finance-audit-document">{{ $auditEntry->document_no }} · Tanggal pengecekan {{ $auditEntry->tanggal_pengecekan->format('d/m/Y') }}</p>
                            @if($auditEntry->catatan_keputusan)<p class="finance-audit-note">{{ $auditEntry->catatan_keputusan }}</p>@endif
                            <div class="finance-audit-amounts">
                                <div><span>Nominal pengajuan</span><strong>{{ $auditMoney($auditOriginal) }}</strong></div>
                                <div><span>{{ $auditEntry->keputusan === 'REVISION' ? 'Hasil sementara' : 'Disetujui Finance' }}</span><strong class="finance-audit-approved">{{ $auditMoney($auditApproved) }}</strong></div>
                                <div><span>Selisih pemeriksaan</span><strong>{{ $auditMoney($auditOriginal - $auditApproved) }}</strong></div>
                            </div>
                            <details class="finance-audit-details">
                                <summary>Rincian pemeriksaan · {{ $auditEntry->details->count() }} item</summary>
                                @include('bs-usage-checks.detail-items', ['check' => $auditEntry, 'showInternalNotes' => auth()->user()->can('akses.approval-finance')])
                            </details>
                        </article>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="finance-audit-empty">Belum ada pemeriksaan yang dikonfirmasi.</p>
        @endif
        @if($auditHistory->hasPages())
            <div class="finance-audit-pagination">{{ $auditHistory->links() }}</div>
        @endif
    </div>
</section>
