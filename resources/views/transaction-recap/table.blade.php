<div class="recap-list">
    @if($rows->isEmpty())
        <div class="recap-empty-enhanced">
            <div class="empty-illustration">
                <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="80" fill="#eef2ff" class="floating"/>
                    <path d="M70 90 L90 110 L130 70" stroke="#4f46e5" stroke-width="8" stroke-linecap="round" class="floating-delay"/>
                    <rect x="60" y="120" width="80" height="40" rx="8" fill="#c7d2fe" class="floating"/>
                </svg>
            </div>
            <h3 class="empty-title">Belum ada pengajuan yang sesuai</h3>
            <p class="empty-description">
                Tidak ada transaksi yang ditemukan dengan filter saat ini. 
                Coba ubah periode, hapus filter, atau periksa pengajuan baru.
            </p>
            <div class="empty-actions">
                <a class="btn btn-outline-secondary" href="{{ route($pageRoute, $pageParams) }}#pemakaian-bs">
                    <i class="bi bi-x-circle"></i> Reset Filter
                </a>
            </div>
            @include('transaction-recap.module-links')
        </div>
    @else
        <div class="table-responsive">
            <table class="table usage-table" role="table">
                <caption class="visually-hidden" data-recap-caption>
                    Pengajuan dan hasil pemeriksaan Finance terakhir per transaksi
                </caption>
                <thead role="rowgroup">
                    <tr role="row">
                        <th scope="col">Pengajuan</th>
                        <th scope="col">User & konfirmasi</th>
                        <th scope="col" class="money">Nominal pengajuan</th>
                        <th scope="col" class="money">Disetujui Finance</th>
                        <th scope="col" class="money">{{ $currentStatus === 'NOT_APPROVED' ? 'Nominal Ditolak' : 'Selisih Persetujuan Finance' }}</th>
                        <th scope="col">Status Finance</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody role="rowgroup">
                @foreach($rows as $row)
                    @php
                        $detailParams = array_merge($reportFilters, [
                            'jenis' => $row->jenis, 
                            'source' => $row->source_id, 
                            'pemakaian_page' => $rows->currentPage(), 
                            'dari' => $isDashboard ? 'dashboard' : 'laporan'
                        ]);
                        $detailUrl = route('transaction-recap.show', $detailParams);
                        $transactionDetailUrl = match ($row->jenis) {
                            'OPR' => route('transaksi_operasional.show', $row->source_id),
                            'PD' => route('transaksi_perjalanan_dinas.show', $row->no_referensi),
                            'EDK' => route('perongkosan_event_dalam_kota.show', $row->source_id),
                            'ELK' => route('perongkosan_event_luar_kota.show', $row->source_id),
                        };
                    @endphp
                    <tr role="row" tabindex="0">
                        <td role="cell">
                            <a class="recap-reference" href="{{ $transactionDetailUrl }}">
                                {{ $row->no_referensi ?: $row->jenis.' #'.$row->source_id }}
                            </a>
                            <div class="recap-meta">
                                {{ $typeLabels[$row->jenis] }}<br>
                                {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}
                            </div>
                        </td>
                        <td role="cell" class="recap-person-cell">
                            <div class="recap-person">{{ $row->user_name }}</div>
                            <div class="recap-meta">
                                {{ $row->validated_at ? \App\Support\AppTime::format($row->validated_at) : 'Waktu konfirmasi belum tercatat' }}
                                @if($row->confirmer_name)<br>Oleh {{ $row->confirmer_name }}@endif
                            </div>
                        </td>
                        <td role="cell" class="money fw-semibold" data-label="Nominal pengajuan">
                            {{ $money($row->nominal_user) }}
                        </td>
                        <td role="cell" class="money recap-number-cell" data-label="Disetujui Finance">
                            {{ in_array($row->finance_status, ['APPROVED', 'REJECTED']) ? $money($row->nominal_finance) : '—' }}
                        </td>
                        <td role="cell" class="money recap-number-cell" data-label="{{ $currentStatus === 'NOT_APPROVED' ? 'Nominal Ditolak' : 'Selisih Persetujuan Finance' }}">
                            {{ $currentStatus === 'NOT_APPROVED' ? $money($row->nominal_not_approved) : ($row->finance_status === 'APPROVED' ? $money($row->selisih) : '—') }}
                        </td>
                        <td role="cell">
                            @include('transaction-recap.status')
                            @if($row->catatan_keputusan)
                                <details class="recap-decision-note">
                                    <summary>{{ $row->needs_recheck ? 'Catatan sebelumnya' : 'Catatan Finance' }}</summary>
                                    <p>{{ $row->catatan_keputusan }}</p>
                                </details>
                            @endif
                        </td>
                        <td role="cell">
                            <div class="recap-actions">
                                <a class="btn btn-sm btn-outline-primary" href="{{ $detailUrl }}">
                                    <i class="bi bi-eye" aria-hidden="true"></i> Detail Transaksi
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="recap-table-footer">
            <span>Total seluruh hasil filter · {{ $rows->total() }} pengajuan</span>
            <div class="recap-footer-totals">
                <span>Pengajuan <strong>{{ $money($summary->nominal_user) }}</strong></span>
                <span>Disetujui <strong>{{ $money($summary->nominal_finance) }}</strong></span>
                <span>Total Ditolak <strong>{{ $money($summary->nominal_not_approved) }}</strong></span>
                <span>Pending / Belum Dicek <strong>{{ $money($summary->nominal_unchecked) }}</strong></span>
                @if($summary->revision_count)<span>Perlu Revisi (keputusan lama) <strong>{{ $money($summary->nominal_revision) }}</strong></span>@endif
            </div>
        </div>
    @endif
</div>
