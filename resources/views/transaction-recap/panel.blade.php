@include('transaction-recap.assets')
@php
    $money = fn ($amount) => 'Rp '.number_format($amount, 2, ',', '.');
    $isDashboard = request()->routeIs('dashboard.transaksi');
    $pageParams = $isDashboard ? [] : ['jenis' => $moduleType];
    $reportFilters = array_diff_key($filters, ['pemakaian_page' => true, 'pemakaian_jenis' => true, 'tampilan' => true]);
    $reportFilters = $pageParams + $reportFilters;
    $typeLabels = \App\Services\BsUsageCheckService::LABELS;
    $statusLabels = ['' => 'Semua status', 'FOLLOW_UP' => 'Perlu Tindak Lanjut', 'PENDING' => 'Pending / Belum Dicek', 'REVISION' => 'Perlu Revisi (keputusan lama)', 'RECHECK' => 'Perlu Cek Ulang', 'APPROVED' => 'Disetujui', 'REJECTED' => 'Ditolak', 'DIFFERENCE' => 'Selisih Persetujuan Finance'];
    $currentStatus = $filters['pemakaian_status'] ?? '';
    $pageRoute = $isDashboard ? 'dashboard.transaksi' : 'transaction-recap.module';
    $scopeLabel = auth()->user()->can('akses.approval-finance') || auth()->user()->canSeeAllTransactions() ? 'Rekap seluruh user' : 'Rekap pengajuan Anda';
@endphp
<section class="recap-workspace usage-panel" id="pemakaian-bs" aria-labelledby="usage-title" @if($isDashboard) data-recap-dashboard @else data-recap-module="{{ $moduleType }}" @endif>
    @unless($isDashboard)
        <a class="recap-back" href="{{ route('dashboard.transaksi', array_diff_key($reportFilters, ['jenis' => true])) }}#pemakaian-bs"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke Dashboard Transaksi</a>
    @endunless
    <header class="recap-head">
        <div>
            <div class="recap-eyebrow">{{ $scopeLabel }}</div>
            @if($isDashboard)
                <h2 id="usage-title">Rekap Pengajuan dan Hasil Finance</h2>
                <p>Pilih kartu modul untuk melihat pengajuan dan hasil Finance. Pilih status pada kartu untuk melihat transaksi yang perlu ditindaklanjuti.</p>
            @else
                <h1 id="usage-title">Daftar {{ $typeLabels[$moduleType] }}</h1>
                <p>Pengajuan {{ $typeLabels[$moduleType] }} yang sudah dikonfirmasi user, beserta hasil Finance dan tindak lanjutnya.</p>
            @endif
        </div>
    </header>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @include('transaction-recap.filters')
    @if($isDashboard)
        <div class="recap-section-head"><h3>Modul transaksi</h3><p>{{ $summary->confirmed_count }} pengajuan · {{ $money($summary->nominal_user) }}</p></div>
        <div class="usage-modules">
            @foreach($typeLabels as $type => $label)
                @php
                    $module = $modules[$type];
                    $moduleFilters = ['jenis' => $type] + $reportFilters;
                    $icons = ['OPR' => 'bi-briefcase', 'PD' => 'bi-signpost-split', 'EDK' => 'bi-building', 'ELK' => 'bi-globe2'];
                @endphp
                <article class="usage-module" data-module-card>
                    <div class="recap-module-head"><a class="recap-module-link" href="{{ route('transaction-recap.module', $moduleFilters) }}#pemakaian-bs" aria-label="Lihat pengajuan {{ $label }}"><span class="recap-module-icon"><i class="bi {{ $icons[$type] }}" aria-hidden="true"></i></span><span>{{ $label }}</span><i class="bi bi-chevron-right recap-module-chevron" aria-hidden="true"></i></a></div>
                    <div class="recap-module-total-label">Total Pengajuan</div>
                    <div class="recap-module-total">{{ $money($module->nominal_user) }}</div>
                    <div class="recap-module-caption">{{ $module->confirmed_count }} pengajuan dikonfirmasi</div>
                    @if((int) $module->confirmed_count === 0)
                        <p class="recap-module-empty"><i class="bi bi-info-circle" aria-hidden="true"></i><span>{{ $currentStatus || !empty($filters['pemakaian_referensi']) ? 'Belum ada pengajuan yang sesuai filter ini.' : 'Belum ada pengajuan pada periode ini' }}</span></p>
                    @endif
                    @include('transaction-recap.amounts', ['totals' => $module, 'amountFilters' => $moduleFilters, 'amountLabel' => $label])
                    <p class="recap-followup-hint">Pilih nominal atau status untuk melihat rincian transaksi.</p>
                </article>
            @endforeach
        </div>
    @endif
    @unless($isDashboard)
    <div data-recap-report>
        <div class="recap-amount-summary">
            <div class="recap-total recap-submission-total"><span>Total Pengajuan · {{ $summary->confirmed_count }} transaksi</span><strong>{{ $money($summary->nominal_user) }}</strong></div>
            @include('transaction-recap.amounts', ['totals' => $summary, 'amountFilters' => $reportFilters, 'amountLabel' => $typeLabels[$moduleType]])
        </div>
    <div class="recap-section-head">
        <h2 data-recap-list-title>Pengajuan dan hasil Finance terakhir</h2>
        <p>{{ $rows->total() ? $rows->firstItem().'–'.$rows->lastItem().' dari '.$rows->total() : '0' }} transaksi{{ $currentStatus ? ' · '.$statusLabels[$currentStatus] : '' }}</p>
    </div>
    @include('transaction-recap.table')
    @if($rows->hasPages())<nav class="mt-3" aria-label="Halaman daftar transaksi">{{ $rows->links() }}</nav>@endif
    </div>
    @endunless
    <details class="recap-guide">
        <summary>Cara membaca rekap dan menemukan pengajuan</summary>
        <p>Rekap hanya memuat transaksi yang sudah dikonfirmasi user. Belum Dicek berarti menunggu pemeriksaan Finance. Ditolak berarti Finance menolak pengajuan dengan alasan. Setelah user memperbaiki dan mengonfirmasi pengajuan kembali, status menjadi Perlu Cek Ulang. Pengajuan Event yang belum muncul dapat dikonfirmasi melalui rincian Event.</p>
        <p>Pending / Belum Dicek adalah seluruh nominal pengajuan yang belum diperiksa Finance. Ditolak adalah keputusan untuk versi yang telah diperiksa. Pengajuan ulang setelah perbaikan masuk Perlu Cek Ulang. Nominal tiap status ditampilkan terpisah.</p>
        <p>Selisih Persetujuan Finance dihitung hanya dari transaksi berstatus Disetujui: nominal pengajuan dikurangi nominal yang disetujui. Contoh: pengajuan Rp250.000 disetujui Rp200.000, sehingga selisihnya Rp50.000. Selisih ini ditampilkan terpisah dari Pending / Belum Dicek.</p>
        <p>Total Pengajuan = Disetujui Finance + Ditolak + Pending / Belum Dicek + Perlu Cek Ulang + Selisih Persetujuan Finance. Jika ada keputusan lama Perlu Revisi, nominalnya juga ditampilkan terpisah dan termasuk Total Pengajuan. Jika Finance menyetujui lebih besar dari pengajuan, selisihnya negatif.</p>
        <p>Jumlah transaksi dengan selisih merupakan bagian dari transaksi Disetujui Finance, sehingga jumlah keduanya tidak dijumlahkan. Perbedaan antaritem tetap dapat dibuka meskipun total selisihnya nol.</p>
        <p>Semua total mengikuti filter dan mencakup seluruh halaman. Hasil ini memakai pemeriksaan Finance terakhir dan tidak mengubah saldo BS atau Stok Kas.</p>
        @include('transaction-recap.module-links')
    </details>
</section>
