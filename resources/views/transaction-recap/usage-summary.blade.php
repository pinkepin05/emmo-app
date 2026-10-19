@php
    $transactionListRoutes = [
        'OPR' => 'transaksi_operasional.index',
        'PD' => 'transaksi_perjalanan_dinas.index',
        'EDK' => 'perongkosan_event_dalam_kota.index',
        'ELK' => 'perongkosan_event_luar_kota.index',
    ];
    $usageStages = [
        'REPORTED' => ['Total pemakaian', 'bi-file-earmark-text', 'nominal_user', 'confirmed_count', 'Seluruh pemakaian yang sudah dilaporkan.'],
        'APPROVED' => ['Disetujui Finance', 'bi-check-circle', 'nominal_finance', 'checked_count', 'Bagian nominal yang sudah disetujui.'],
        'PENDING' => ['Belum dicek Finance', 'bi-hourglass-split', 'nominal_unchecked', 'unchecked_count', 'Nominal yang masih menunggu pemeriksaan.'],
        'REJECTED' => ['Tidak disetujui', 'bi-x-circle', 'nominal_not_approved', 'not_approved_count', 'Termasuk nominal yang ditolak sebagian.'],
        'REVISION' => ['Perlu Revisi', 'bi-pencil-square', 'nominal_revision', 'revision_count', 'Keputusan lama yang masih menunggu perbaikan.'],
    ];
    $financeStages = array_diff_key($usageStages, ['REPORTED' => true]);
    $usageAmountsMatch = \App\Support\Money::minor($summary->nominal_user) === array_sum(array_map(
        fn ($key) => \App\Support\Money::minor($summary->{$key}),
        ['nominal_finance', 'nominal_unchecked', 'nominal_not_approved', 'nominal_revision']
    ));
@endphp
<section aria-labelledby="usage-recap-title" data-usage-recap>
    <div class="recap-section-head">
        <h3 id="usage-recap-title">Rekap Pemakaian &amp; Konfirmasi BS</h3>
        <p class="recap-usage-count">{{ $summary->confirmed_count }} transaksi dilaporkan</p>
    </div>
    <div class="recap-usage-panel">
        <div class="recap-usage-totals">
        @foreach($usageStages as $stage => [$title, $icon, $amountKey, $countKey, $note])
            @continue($stage === 'REVISION' && !(int) $summary->revision_count)
            <article class="recap-usage-stage" data-usage-stage="{{ $stage }}">
                <h4 class="recap-funding-label"><i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $title }}</h4>
                <strong class="recap-module-total">{{ $money($summary->{$amountKey}) }}</strong>
                <span class="recap-module-caption">{{ $summary->{$countKey} }} transaksi</span>
                <p class="recap-funding-note">{{ $note }}</p>
            </article>
        @endforeach
        </div>
        <div class="recap-usage-detail-head">
            <h4>Rincian per jenis biaya</h4>
            <p>Pilih jenis biaya untuk membuka transaksi, atau nominal status untuk melihat pemeriksaan Finance.</p>
        </div>
        <div class="recap-usage-columns" aria-hidden="true">
            <span>Jenis biaya &amp; total pemakaian</span>
            <span>Disetujui Finance</span>
            <span>Belum dicek Finance</span>
            <span>Tidak disetujui</span>
        </div>
        <div class="recap-usage-rows">
            @foreach($typeLabels as $type => $label)
                @php
                    $usageModule = $modules[$type];
                    $financeUrl = route('transaction-recap.module', [
                        'jenis' => $type,
                        'pemakaian_periode' => $filters['pemakaian_periode'],
                    ]).'#pemakaian-bs';
                @endphp
                <div class="recap-usage-row {{ !(int) $usageModule->confirmed_count ? 'is-empty' : '' }}" data-usage-row="{{ $type }}">
                    <a class="recap-usage-reported" data-usage-history="{{ $type }}" data-usage-reported
                       href="{{ route($transactionListRoutes[$type], ['periode' => $filters['pemakaian_periode'], 'dilaporkan' => 1]) }}"
                       aria-label="Daftar Transaksi {{ $label }}">
                        <span class="recap-usage-category"><i class="bi {{ $moduleIcons[$type] }}" aria-hidden="true"></i>{{ $label }}<i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                        <span class="recap-usage-category-amount">{{ $money($usageModule->nominal_user) }}<small>{{ $usageModule->confirmed_count }} transaksi</small></span>
                    </a>
                    <a class="recap-usage-finance" data-usage-history="{{ $type }}" data-usage-finance
                       href="{{ $financeUrl }}" aria-label="Daftar Pengecekan Finance {{ $label }}, semua status">
                        @foreach($financeStages as $stage => [$title, $icon, $amountKey, $countKey, $note])
                            @continue($stage === 'REVISION' && !(int) $usageModule->revision_count)
                            <span class="recap-usage-status {{ (float) $usageModule->{$amountKey} === 0.0 ? 'is-zero' : '' }}" data-finance-status="{{ $stage }}">
                                <span class="recap-usage-status-title"><i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $title }}</span>
                                <strong>{{ $money($usageModule->{$amountKey}) }}</strong>
                                <small>{{ $usageModule->{$countKey} }} transaksi</small>
                            </span>
                        @endforeach
                        <i class="bi bi-arrow-right recap-usage-finance-arrow" aria-hidden="true"></i>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="recap-usage-explanation">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div>
                @if($usageAmountsMatch)
                    <p><strong>Total pemakaian</strong> = disetujui + belum dicek + tidak disetujui{{ (int) $summary->revision_count ? ' + perlu revisi' : '' }}.</p>
                @else
                    <p><strong>Total pemakaian</strong> berasal dari laporan user. Nominal persetujuan Finance dapat berbeda dari nominal yang dilaporkan.</p>
                @endif
                <p>Jumlah transaksi per status tidak dijumlahkan. Satu transaksi bisa disetujui sebagian dan sebagian lainnya tidak disetujui.</p>
            </div>
        </div>
    </div>
    @if(!(int) $summary->confirmed_count)
        <p class="recap-amount-note">Belum ada pemakaian dilaporkan pada periode ini.</p>
    @endif
</section>
