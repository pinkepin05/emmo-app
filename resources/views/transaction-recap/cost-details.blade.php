@php
    $costMoney = fn ($minor) => $minor === null ? '—' : 'Rp '.number_format(\App\Support\Money::decimal($minor), 2, ',', '.');
@endphp
<section class="recap-card mt-3" id="rincian-pengajuan" aria-labelledby="hasil-finance-title">
    <div id="hasil-finance">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h2 id="hasil-finance-title">Rincian biaya dan hasil pemeriksaan{{ $historicalItems ? ' sebelumnya' : '' }}</h2>
                @if($displayDecision)
                    <p class="recap-meta mb-0">{{ $displayDecision->document_no }} · {{ $displayDecision->checker_name }} · {{ $displayDecision->tanggal_pengecekan->format('d/m/Y') }}</p>
                @endif
            </div>
            @if($selectedDecision && $selectedDecision->id !== $latestDecision->id)
                <a class="btn btn-sm btn-outline-primary" href="{{ $detailUrl }}#hasil-finance">Hasil terbaru</a>
            @endif
        </div>
        @if($historicalItems)
            <p class="alert alert-info small">Ini adalah hasil pada versi pengajuan saat diperiksa. Perubahan transaksi memerlukan pengecekan ulang Finance.</p>
        @elseif($outdatedResult)
            <p class="alert alert-info small">Rincian saat ini belum dicek ulang Finance. Jejak Audit memuat hasil pada versi pengajuan saat diperiksa.</p>
        @elseif(!$displayDecision)
            <p class="small text-muted">Nominal disetujui dan selisih akan ditampilkan setelah pemeriksaan Finance.</p>
        @endif
        @if($displayDecision)
            <p class="small"><strong>{{ \App\Services\BsUsageCheckService::decisionLabel($displayDecision->keputusan) }}</strong>@if($displayDecision->catatan_keputusan) · {{ $displayDecision->catatan_keputusan }}@endif</p>
        @endif
        @if($costDetails['grouped'])
            <p class="alert alert-info small">Dokumen lama ini menyimpan gabungan per kategori. Nominal historis tetap dipertahankan; pemeriksaan baru dilakukan per rincian biaya.</p>
        @endif
        <div class="table-responsive">
            <table class="table recap-cost-table" aria-labelledby="hasil-finance-title">
                <thead><tr>
                    <th scope="col">Item</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Foto</th>
                    <th scope="col" class="recap-cost-money">Nominal Asal</th>
                    <th scope="col" class="recap-cost-money">Nominal Disetujui</th>
                    <th scope="col" class="recap-cost-money">Selisih</th>
                </tr></thead>
                <tbody>
                    @forelse($costDetails['rows'] as $row)
                        <tr>
                            <td data-label="Item">{{ $row['item'] }}</td>
                            <td data-label="Keterangan">
                                <div>{{ $row['remark'] ?: '—' }}</div>
                                @if($row['internal_note'])
                                    <div class="recap-cost-note"><strong>Catatan internal Finance</strong><br>{{ $row['internal_note'] }}</div>
                                @endif
                            </td>
                            <td data-label="Foto">
                                <div class="recap-cost-photos">
                                    @forelse($row['photos'] as $photoUrl)
                                        @include('partials.transaction-photo-link', ['photoUrl' => $photoUrl])
                                    @empty
                                        <span aria-label="Tidak ada foto">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td data-label="Nominal Asal" class="recap-cost-money">{{ $costMoney($row['original']) }}</td>
                            <td data-label="Nominal Disetujui" class="recap-cost-money">{{ $costMoney($row['approved']) }}</td>
                            <td data-label="Selisih" class="recap-cost-money {{ $row['difference'] ? 'text-danger' : '' }}">{{ $costMoney($row['difference']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada rincian biaya.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr>
                    <th scope="row" colspan="3">Total</th>
                    <td data-label="Nominal Asal" class="recap-cost-money">{{ $costMoney($costDetails['original']) }}</td>
                    <td data-label="Nominal Disetujui" class="recap-cost-money">{{ $costMoney($costDetails['approved']) }}</td>
                    <td data-label="Selisih" class="recap-cost-money">{{ $costMoney($costDetails['difference']) }}</td>
                </tr></tfoot>
            </table>
        </div>
    </div>
</section>
