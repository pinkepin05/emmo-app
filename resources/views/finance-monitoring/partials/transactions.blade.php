@php $tabs = ['' => 'Semua', 'OPR' => 'Operasional', 'PD' => 'Perjalanan Dinas', 'EDK' => 'Event Dalam Kota', 'ELK' => 'Event Luar Kota']; @endphp
<div class="card shadow-sm" id="monitoring-transactions">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><h2 class="h5 mb-0">Transaksi User</h2><span class="text-muted small">Total: {{ $transactions->total() }}</span></div>
        <ul class="nav nav-pills flex-wrap gap-1">
            @foreach ($tabs as $key => $label)
            @php $params = ['company' => $company->id, 'user' => $user->id, 'periode' => $periode]; if ($key !== '') $params['jenis_filter'] = $key; @endphp
            <li class="nav-item"><a class="nav-link {{ ($current_filter ?? '') === $key ? 'active' : '' }}" href="{{ route('monitoring-finance.user', $params) }}">{{ $label }}</a></li>
            @endforeach
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Tanggal</th><th>Jenis</th><th>Referensi</th><th>Status Pengajuan</th><th>Keputusan Finance</th><th class="text-end">Nominal</th><th>Pemilik Saldo</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($transactions as $trx)
                    @php
                        $sourceStatusLabel = match ($trx['source_status']) { 'VALIDATED' => 'Dikonfirmasi', 'DRAFT' => 'Draft', 'PENDING_OWNER' => 'Menunggu Pemohon', 'REJECTED_OWNER' => 'Ditolak Pemohon', default => $trx['source_status'] };
                        $financeStatusLabel = match ($trx['finance_status']) { 'APPROVED' => 'Disetujui', 'REJECTED' => 'Ditolak Finance', 'REVISION' => 'Revisi', 'PERLU_RECHECK' => 'Perlu Dicek Ulang', default => 'Belum Dicek' };
                        $financeStatusClass = match ($trx['finance_status']) { 'APPROVED' => 'bg-success', 'REJECTED' => 'bg-danger', 'REVISION' => 'bg-warning text-dark', 'PERLU_RECHECK' => 'bg-info text-dark', default => 'bg-secondary' };
                        $sourceStatusClass = match ($trx['source_status']) { 'VALIDATED' => 'bg-success', 'DRAFT' => 'bg-warning text-dark', 'PENDING_OWNER' => 'bg-info text-dark', 'REJECTED_OWNER' => 'bg-danger', default => 'bg-secondary' };
                    @endphp
                    <tr>
                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($trx['date'])->format('d/m/Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $trx['label'] }}</span></td>
                        <td class="text-break">{{ $trx['reference'] }}</td>
                        <td><span class="badge {{ $sourceStatusClass }}">{{ $sourceStatusLabel }}</span></td>
                        <td><span class="badge {{ $financeStatusClass }}">{{ $financeStatusLabel }}</span></td>
                        <td class="text-end text-nowrap fw-semibold">Rp {{ number_format((float) $trx['nominal_user'], 2, ',', '.') }}</td>
                        <td>{{ $trx['bs_owner_name'] }} @if ($trx['bs_owner_differs']) <div><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Berbeda</small></div> @endif</td>
                        <td class="text-end"><a href="{{ $trx['url'] }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                    @empty <tr><td colspan="8" class="text-center py-5 text-muted">Tidak ada transaksi.</td></tr> @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($transactions->hasPages()) <div class="card-footer bg-white">{{ $transactions->links() }}</div> @endif
</div>