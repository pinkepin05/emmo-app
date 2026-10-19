@include('transaction-recap.assets')
@php
    $money = fn ($amount) => 'Rp '.number_format($amount, 2, ',', '.');
    $isDashboard = request()->routeIs('dashboard.transaksi');
    $pageParams = $isDashboard ? [] : ['jenis' => $moduleType];
    $reportFilters = array_diff_key($filters, ['pemakaian_page' => true, 'pemakaian_jenis' => true, 'tampilan' => true]);
    $reportFilters = $pageParams + $reportFilters;
    $typeLabels = \App\Services\BsUsageCheckService::LABELS;
    $statusLabels = ['CHECKED' => 'Sudah Dicek', 'PENDING' => 'Belum Dicek', 'FOLLOW_UP' => 'Perlu Tindak Lanjut', 'REVISION' => 'Perlu Revisi (keputusan lama)', 'APPROVED' => 'Disetujui', 'NOT_APPROVED' => 'Ditolak Penuh / Sebagian', 'REJECTED' => 'Ditolak Penuh', 'DIFFERENCE' => 'Selisih Persetujuan Finance'];
    $moduleIcons = ['OPR' => 'bi-briefcase', 'PD' => 'bi-signpost-split', 'EDK' => 'bi-building', 'ELK' => 'bi-globe2'];
    $currentStatus = $filters['pemakaian_status'] ?? '';
    $pageRoute = $isDashboard ? 'dashboard.transaksi' : 'transaction-recap.module';
    $scopeLabel = auth()->user()->can('akses.approval-finance') || auth()->user()->canSeeAllTransactions() ? 'Rekap seluruh user' : 'Rekap pengajuan Anda';
@endphp
<section class="recap-workspace usage-panel" id="pemakaian-bs" @if($isDashboard) aria-label="Rekap BS" data-recap-dashboard @else aria-labelledby="usage-title" data-recap-module="{{ $moduleType }}" @endif>
    @unless($isDashboard)
        <a class="recap-back" href="{{ route('dashboard.transaksi', array_intersect_key($filters, ['pemakaian_periode' => true])) }}#pemakaian-bs"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke Dashboard Transaksi</a>
    @endunless
    @if($isDashboard)
        {{-- Banner dashboard disembunyikan sementara.
        <header class="recap-head">
            <div>
                <div class="recap-eyebrow">{{ $scopeLabel }}</div>
                <h2 id="usage-title">Rekap Pengajuan dan Hasil Finance</h2>
            </div>
        </header>
        --}}
    @else
    <header class="recap-head">
        <div>
            <div class="recap-eyebrow">{{ $scopeLabel }}</div>
            <h1 id="usage-title">Daftar Pengecekan Finance — {{ $typeLabels[$moduleType] }}</h1>
            <p>Pengajuan {{ $typeLabels[$moduleType] }} yang sudah dikonfirmasi user, beserta hasil Finance dan tindak lanjutnya.</p>
        </div>
    </header>
    @endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @unless($isDashboard)
        @include('transaction-recap.filters')
    @endunless
    @if($isDashboard)
        @include('transaction-recap.funding-summary')
        @include('transaction-recap.usage-summary')
    @endif
    @unless($isDashboard)
    <div data-recap-report>
    <div class="recap-section-head">
        <h2 data-recap-list-title>Pengajuan dan hasil Finance terakhir</h2>
        <p>{{ $rows->total() ? $rows->firstItem().'–'.$rows->lastItem().' dari '.$rows->total() : '0' }} transaksi{{ $currentStatus ? ' · '.$statusLabels[$currentStatus] : '' }}</p>
    </div>
    @include('transaction-recap.table')
    @if($rows->hasPages())<nav class="mt-3" aria-label="Halaman daftar transaksi">{{ $rows->links() }}</nav>@endif
    </div>
    @endunless
</section>
