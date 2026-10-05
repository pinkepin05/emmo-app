<div class="recap-section-head"><h2>{{ $filters['mode'] === 'terakhir' ? 'Rincian pemeriksaan terakhir' : 'Seluruh riwayat pemeriksaan' }}</h2><p>{{ $rows->total() }} item pemeriksaan</p></div>
<p class="alert alert-info small">Nominal di sini mengikuti dokumen saat pemeriksaan disimpan. Dokumen sebelum perbaikan atau keputusan baru tetap tercatat. Perlu Revisi hanya muncul pada dokumen lama. Gunakan Daftar transaksi untuk melihat keputusan yang berlaku saat ini.</p>
@if($filters['mode'] === 'semua')<p class="small text-muted">Satu referensi dapat muncul beberapa kali. Total riwayat menjumlahkan setiap pemeriksaan, bukan total pengeluaran unik.</p>@endif
<p class="small text-muted">Status sesuai berlaku bila semua item sesuai. Selisih = Nominal Asal − Nominal Hasil Pemeriksaan. Nilai negatif berarti hasil pemeriksaan lebih besar daripada nominal asal.</p>
<div class="recap-list"><div class="table-responsive"><table class="table usage-table" role="table">
    <caption class="visually-hidden">Rincian dan catatan setiap item pemeriksaan Finance</caption>
    <thead role="rowgroup"><tr role="row"><th scope="col">Dokumen pemeriksaan</th><th scope="col">Tanggal pengecekan</th><th scope="col">User</th><th scope="col">Jenis</th><th scope="col">No referensi</th><th scope="col">Item</th><th scope="col" class="money">Nominal asal</th><th scope="col" class="money">Hasil pemeriksaan</th><th scope="col" class="money">Selisih</th><th scope="col">Catatan Finance</th></tr></thead>
    <tbody role="rowgroup">@forelse($rows as $row)
        <tr role="row">
            <td role="cell"><a class="recap-reference" href="{{ route('bs-usage-checks.show', $row->check) }}">{{ $row->check->document_no }}</a><div class="recap-meta">{{ \App\Services\BsUsageCheckService::decisionLabel($row->check->keputusan) }}</div></td>
            <td role="cell" data-label="Tanggal pengecekan">{{ $row->check->tanggal_pengecekan->format('d/m/Y') }}</td>
            <td role="cell" data-label="Pemilik pengajuan">{{ $row->check->user_name }}</td>
            <td role="cell" data-label="Jenis">{{ $row->check->jenis_transaksi }}</td>
            <td role="cell" data-label="No referensi"><a href="{{ route('bs-usage-checks.index', ['tampilan' => 'rincian', 'mode' => 'semua', 'jenis_source' => $row->check->jenis_transaksi, 'source_id' => $row->check->transaksi_pemakaian_id]) }}">{{ $row->check->no_referensi }}</a></td>
            <td role="cell" data-label="Item">{{ $row->cost_category_name }}</td>
            <td role="cell" data-label="Nominal asal" class="money recap-number-cell">{{ $money($row->nominal_asal) }}</td>
            <td role="cell" data-label="Hasil pemeriksaan" class="money recap-number-cell">{{ $money($row->nominal_disetujui) }}</td>
            <td role="cell" data-label="Selisih" class="money recap-number-cell {{ $row->selisih !== '0.00' ? 'text-danger' : '' }}">{{ $money($row->selisih) }}</td>
            <td role="cell" data-label="Catatan Finance"><div class="recap-decision-note"><p>{{ $row->catatan ?: '—' }}</p></div></td>
        </tr>
    @empty<tr><td colspan="10"><div class="recap-empty"><h3>Belum ada hasil pengecekan yang cocok dengan filter.</h3><p>Ubah filter atau pilih transaksi untuk mulai memeriksa.</p><a class="btn btn-outline-primary btn-sm" href="{{ route('bs-usage-checks.index') }}">Lihat daftar transaksi</a></div></td></tr>@endforelse</tbody>
</table></div></div>
<div class="recap-table-footer">
    <span>Total seluruh hasil filter ({{ $rows->total() }} item)</span>
    <div class="recap-footer-totals"><span>Nominal asal <strong>{{ $money($totals->nominal_asal) }}</strong></span><span>Hasil pemeriksaan <strong>{{ $money($totals->nominal_disetujui) }}</strong></span><span>Selisih <strong>{{ $money($totals->selisih) }}</strong></span></div>
</div>
<div class="mt-3">{{ $rows->links() }}</div>
