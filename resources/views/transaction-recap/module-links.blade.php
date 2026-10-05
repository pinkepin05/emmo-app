<div class="recap-help-links">
    @foreach(['akses.transaksi-operasional' => ['transaksi_operasional.index', 'Operasional Sales'], 'akses.transaksi-perjalanan-dinas' => ['transaksi_perjalanan_dinas.index', 'Perjalanan Dinas'], 'akses.event-dalam-kota' => ['perongkosan_event_dalam_kota.index', 'Event Dalam Kota'], 'akses.event-luar-kota' => ['perongkosan_event_luar_kota.index', 'Event Luar Kota']] as $permission => [$moduleRoute, $moduleLabel])
        @can($permission)<a class="btn btn-sm btn-outline-secondary" href="{{ route($moduleRoute) }}">Daftar {{ $moduleLabel }}</a>@endcan
    @endforeach
</div>
