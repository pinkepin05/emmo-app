<div class="recap-section-head"><h2>Draft pemeriksaan Finance</h2><p>{{ $drafts->total() }} draft</p></div>
<p class="small text-muted">Draft menyimpan pekerjaan sementara dan dapat dilanjutkan oleh Finance. Hasilnya masuk dashboard dan laporan setelah Simpan Konfirmasi.</p>
<div class="recap-list"><div class="table-responsive"><table class="table usage-table" role="table">
    <caption class="visually-hidden">Draft pemeriksaan yang belum dikonfirmasi</caption>
    <thead role="rowgroup"><tr role="row"><th scope="col">Draft &amp; pengajuan</th><th scope="col">Pemilik</th><th scope="col">Tanggal pengecekan</th><th scope="col">Terakhir disimpan</th><th scope="col">Tindakan</th></tr></thead>
    <tbody role="rowgroup">@forelse($drafts as $draft)
        <tr role="row">
            <td role="cell"><strong>Draft #{{ $draft->id }}</strong><div>{{ $draft->no_referensi }}</div><div class="recap-meta">{{ \App\Services\BsUsageCheckService::LABELS[$draft->jenis_transaksi] }}</div>
                @if($draft->revisionOf)<div class="recap-meta">Pemeriksaan sebelumnya: <a href="{{ route('bs-usage-checks.show', $draft->revisionOf) }}">{{ $draft->revisionOf->document_no }}</a></div>@endif
            </td>
            <td role="cell" data-label="Pemilik">{{ $draft->user_name }}</td>
            <td role="cell" data-label="Tanggal pengecekan">{{ $draft->tanggal_pengecekan->format('d/m/Y') }}</td>
            <td role="cell" data-label="Terakhir disimpan">{{ $draft->checker_name }}<div class="recap-meta">{{ \App\Support\AppTime::format($draft->updated_at) }}</div></td>
            <td role="cell">
                <div class="recap-actions">
                    @if($draft->source)
                        <a class="btn btn-sm btn-primary" href="{{ route('bs-usage-checks.create', [$draft->jenis_transaksi, $draft->transaksi_pemakaian_id]) }}">Lanjutkan Draft</a>
                    @else<span class="text-muted small">Sumber sudah tidak tersedia.</span>@endif
                </div>
                <details class="recap-decision-note"><summary>Lihat isi draft</summary>
                    <p>Rencana keputusan: {{ \App\Services\BsUsageCheckService::decisionLabel($draft->keputusan) }}</p>
                    @if($draft->catatan_keputusan)<p>{{ $draft->catatan_keputusan }}</p>@endif
                    @foreach($draft->details as $item)<p><strong>{{ $item['cost_category_name'] }}</strong><br>Asal: {{ $money($item['nominal_asal']) }}<br>Hasil sementara: {{ $item['nominal_disetujui'] === null ? 'Belum diisi' : $money($item['nominal_disetujui']) }}<br>{{ $item['catatan'] ?? '' }}</p>@endforeach
                </details>
            </td>
        </tr>
    @empty<tr><td colspan="5"><div class="recap-empty"><h3>Belum ada draft yang cocok dengan filter.</h3><p>Pilih transaksi, kemudian gunakan Simpan Draft untuk melanjutkan pemeriksaan nanti.</p><a class="btn btn-outline-primary btn-sm" href="{{ route('bs-usage-checks.index') }}">Pilih transaksi</a></div></td></tr>@endforelse</tbody>
</table></div></div>
<div class="mt-3">{{ $drafts->links() }}</div>
