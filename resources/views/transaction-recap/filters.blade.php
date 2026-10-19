<form method="GET" action="{{ route($pageRoute, $pageParams) }}#pemakaian-bs" class="recap-filter" aria-label="Filter transaksi">
    @unless($isDashboard)
        <input type="hidden" name="pemakaian_status" value="{{ $currentStatus }}">
    @endunless
    
    <div class="filter-header">
        <div class="filter-title">
            <i class="bi bi-funnel"></i>
            {{ $isDashboard ? 'Filter Periode' : 'Filter Transaksi' }}
        </div>
        <a href="{{ route($pageRoute, $pageParams) }}#pemakaian-bs" class="filter-reset-btn">
            <i class="bi bi-arrow-counterclockwise"></i> Reset Semua
        </a>
    </div>
    
    <div class="recap-filter-grid {{ $isDashboard ? 'recap-filter-grid-period' : 'recap-filter-grid-module' }}">
        @unless($isDashboard)
        <div class="recap-filter-search">
            <label for="pemakaian_referensi">Cari nomor pengajuan / transaksi</label>
            <input class="form-control" id="pemakaian_referensi" name="pemakaian_referensi" 
                   value="{{ $filters['pemakaian_referensi'] ?? '' }}" maxlength="64" 
                   placeholder="Ketik nomor transaksi…" type="search">
        </div>
        @endunless
        <div>
            <label for="pemakaian_periode">{{ $isDashboard ? 'Periode rekap' : 'Periode pengajuan' }}</label>
            <input class="form-control" type="month" name="pemakaian_periode" 
                   id="pemakaian_periode" value="{{ $filters['pemakaian_periode'] }}">
        </div>
        <div class="recap-filter-actions">
            <button class="btn btn-primary btn-sm" type="submit">
                <i class="bi bi-search"></i> Tampilkan
            </button>
        </div>
    </div>
    
    @unless($isDashboard)
        <nav class="recap-status-tabs" aria-label="Filter status Finance">
            @foreach(['CHECKED' => 'Sudah Dicek', 'PENDING' => 'Belum Dicek'] as $value => $label)
                @php $count = $checkStatusCounts[$value]; @endphp
                <a href="{{ route($pageRoute, array_merge($reportFilters, ['pemakaian_status' => $currentStatus === $value ? '' : $value])) }}#pemakaian-bs"
                   data-check-status="{{ $value }}"
                   @if($currentStatus === $value) aria-current="true" @endif>
                    {{ $label }}
                    <span class="tab-count @if($count === 0) is-zero @endif" aria-label="{{ $count }} transaksi">{{ $count }}</span>
                </a>
            @endforeach
        </nav>
    @endunless
</form>
