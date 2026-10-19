<section aria-labelledby="funding-recap-title" data-funding-recap>
    <div class="recap-section-head"><h3 id="funding-recap-title">Rekap Pengajuan &amp; Pencairan BS</h3></div>
    <div class="recap-funding-grid recap-funding-panel">
        @foreach([
            'diajukan' => ['Total Diajukan', 'bi-file-earmark-text', 'Sesuai periode pengajuan BS.'],
            'dicairkan' => ['Total Dicairkan', 'bi-wallet2', 'Pencairan atas pengajuan pada periode terpilih.'],
            'pending' => ['Total Pending', 'bi-hourglass-split', 'Menunggu persetujuan Atasan atau Finance.'],
            'ditolak' => ['Total Ditolak', 'bi-x-circle', 'Penolakan penuh dan selisih pencairan Finance.'],
        ] as $stage => [$title, $icon, $note])
            <a class="recap-funding-card" data-funding-stage="{{ $stage }}" href="{{ route('transaction-recap.funding', ['tahap' => $stage] + array_intersect_key($reportFilters, array_flip(['pemakaian_periode', 'pemakaian_referensi']))) }}">
                <span class="recap-funding-label"><i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $title }}</span>
                <strong class="recap-module-total">{{ $money($funding[$stage]['amount']) }}</strong>
                <span class="recap-module-caption">{{ $funding[$stage]['count'] }} pengajuan</span>
                @if($stage === 'ditolak')
                    <span class="recap-module-caption">
                        Termasuk selisih Finance:
                        {{ $money($funding['ditolak']['selisih_finance']) }}
                    </span>
                @endif
                <span class="recap-funding-note">{{ $note }}</span>
                <span class="recap-funding-action">Lihat {{ $stage === 'diajukan' ? 'Pengajuan' : 'Riwayat' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
            </a>
        @endforeach
    </div>
</section>
