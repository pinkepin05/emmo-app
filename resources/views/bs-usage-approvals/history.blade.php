@if($transaksi->approvalLogs->isNotEmpty())
    <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Riwayat Persetujuan Pemakaian BS</h2>
        <div class="table-responsive"><table class="table table-sm align-middle">
            <thead><tr><th>Waktu</th><th>Pengajuan ke-</th><th>Tindakan</th><th>Oleh</th><th class="text-end">Nominal</th></tr></thead>
            <tbody>
                @foreach($transaksi->approvalLogs as $log)
                    <tr>
                        <td class="text-nowrap">{{ \App\Support\AppTime::format($log->created_at) }}</td>
                        <td>{{ $log->approval_version }}</td>
                        <td>{{ $log->action_label }}@if($log->reason)<div class="small">Alasan: {{ $log->reason }}</div>@endif</td>
                        <td>{{ $log->actor?->nama ?? '-' }}</td>
                        <td class="text-end text-nowrap">Rp {{ number_format($log->nominal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div></div>
@endif
