<div class="recap-section-head"><h2>Transaksi dan hasil Finance terakhir</h2><p>{{ $sources->total() }} referensi ditemukan</p></div>
<p class="small text-muted">Satu baris per transaksi, termasuk yang belum diperiksa. Nominal persetujuan dan selisih final hanya ditampilkan untuk keputusan Disetujui atau Ditolak yang masih sesuai dengan sumber.</p>
<div class="recap-list"><div class="table-responsive"><table class="table usage-table" role="table">
    <caption class="visually-hidden">Pengajuan, hasil Finance terakhir, dan tindakan pemeriksaan</caption>
    <thead role="rowgroup"><tr role="row"><th scope="col">Pengajuan</th><th scope="col">Pemilik Pengajuan</th><th scope="col" class="money">Nominal pengajuan</th><th scope="col" class="money">Disetujui Finance</th><th scope="col" class="money">Selisih final</th><th scope="col">Hasil terakhir</th><th scope="col">Tindakan</th></tr></thead>
    <tbody role="rowgroup">@forelse($sources as $source)
        @php
            $row = $source;
            $sourceDetailUrl = route('transaction-recap.show', ['jenis' => $source->jenis, 'source' => $source->source_id]);
        @endphp
        <tr role="row">
            <td role="cell"><a class="recap-reference" href="{{ $sourceDetailUrl }}">{{ $source->no_referensi ?: $source->jenis.' #'.$source->source_id }}</a><div class="recap-meta">{{ \App\Services\BsUsageCheckService::LABELS[$source->jenis] }} · {{ \Carbon\Carbon::parse($source->tanggal)->format('d/m/Y') }}</div></td>
          <td role="cell" data-label="Pemilik Pengajuan" class="recap-person-cell">
    {{ $source->user_name }}
</td>
            <td role="cell" data-label="Nominal pengajuan" class="money recap-number-cell">{{ $money($source->nominal_user) }}</td>
            <td role="cell" data-label="Disetujui Finance" class="money recap-number-cell">{{ in_array($source->finance_status, ['APPROVED', 'REJECTED']) ? $money($source->nominal_finance) : '—' }}</td>
            <td role="cell" data-label="Selisih final" class="money recap-number-cell">{{ $source->selisih !== null ? $money($source->selisih) : '—' }}</td>
            <td role="cell" data-label="Hasil terakhir">
                @include('transaction-recap.status')
                @if($source->draft)<div class="recap-meta text-primary mt-2">Draft pemeriksaan #{{ $source->draft->id }} tersedia. Belum dikonfirmasi.</div>@endif
                @if($source->check_id)
                    <div class="recap-meta mt-1">@if($source->needs_recheck) Pemeriksaan sebelumnya: @endif<a href="{{ $sourceDetailUrl }}#hasil-finance">{{ $source->check_no }}</a></div>
                    <div class="recap-meta">{{ \Carbon\Carbon::parse($source->tanggal_pengecekan)->format('d/m/Y') }} · {{ $source->checker_name }}</div>
                    @if($source->catatan_keputusan)<div class="recap-decision-note"><p>{{ $source->catatan_keputusan }}</p></div>@endif
                @endif
            </td>
            <td role="cell"><div class="recap-actions">
                <a class="btn btn-sm btn-primary" href="{{ $sourceDetailUrl }}">Detail Transaksi</a>
            </div></td>
        </tr>
    @empty<tr><td colspan="7"><div class="recap-empty"><h3>Tidak ada transaksi yang cocok dengan filter.</h3><p>Coba nomor lain atau lihat seluruh referensi yang tersedia.</p><a class="btn btn-outline-primary btn-sm" href="{{ route('bs-usage-checks.index') }}">Reset pencarian</a></div></td></tr>@endforelse</tbody>
</table></div></div>
<div class="recap-table-footer">
    <span>Total seluruh hasil filter ({{ $sources->total() }} referensi)</span>
    <div class="recap-footer-totals"><span>Pengajuan <strong>{{ $money($totals->nominal_asal) }}</strong></span><span>Disetujui Finance <strong>{{ $money($totals->nominal_disetujui) }}</strong></span><span>Selisih final <strong>{{ $money($totals->selisih) }}</strong></span></div>
</div>
<p class="small text-muted mt-2">{{ $totals->unresolved_count }} referensi belum memiliki keputusan final yang berlaku. Selisih final = pengajuan − disetujui, termasuk pengajuan yang ditolak. Nilai sementara dan hasil sebelum perubahan sumber tersedia di Detail Transaksi.</p>
<div class="mt-3">{{ $sources->links() }}</div>
<p class="small text-muted">OPR/PD yang masih draft dapat ditinjau di sini. Event tersedia setelah dikonfirmasi user. Dashboard transaksi hanya menghitung pengajuan yang sudah dikonfirmasi. Pengecekan tidak mengubah Sisa BS.</p>
