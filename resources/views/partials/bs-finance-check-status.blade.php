<div class="my-2">
    <span class="badge {{ $transaction->finance_checks_exists ? 'bg-info text-dark' : 'bg-light text-secondary border' }}">
        {{ $transaction->finance_checks_exists ? 'Sudah Dicek Finance' : 'Belum Dicek Finance' }}
    </span>
    @can('akses.approval-finance')
        <a class="small ms-2" href="{{ route('bs-usage-checks.create', ['jenis' => $jenis, 'source' => $transaction->id]) }}">{{ $transaction->finance_checks_exists ? 'Cek Ulang' : 'Cek Pemakaian' }}</a>
        @if($transaction->finance_checks_exists)
            <a class="small ms-2" href="{{ route('bs-usage-checks.index', ['tampilan' => 'rincian', 'mode' => 'semua', 'jenis_source' => $jenis, 'source_id' => $transaction->id]) }}">Riwayat Finance</a>
        @endif
    @endcan
</div>
