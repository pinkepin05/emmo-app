<form method="GET" action="{{ route($pageRoute, $pageParams) }}#pemakaian-bs" class="recap-filter" aria-label="Filter transaksi">
    <input type="hidden" name="pemakaian_status" value="{{ $currentStatus }}">
    
    @php
        $statusCounts = [
            '' => $summary->confirmed_count ?? 0,
            'FOLLOW_UP' => $summary->pending_count ?? 0,
            'PENDING' => $summary->unchecked_count ?? 0,
            'REVISION' => $summary->revision_count ?? 0,
            'RECHECK' => $summary->recheck_count ?? 0,
            'APPROVED' => $summary->checked_count ?? 0,
            'REJECTED' => $summary->rejected_count ?? 0,
            'DIFFERENCE' => $summary->different_count ?? 0,
        ];
    @endphp
    
    <div class="filter-header">
        <div class="filter-title">
            <i class="bi bi-funnel"></i>
            Filter Transaksi
        </div>
        <a href="{{ route($pageRoute, $pageParams) }}#pemakaian-bs" class="filter-reset-btn">
            <i class="bi bi-arrow-counterclockwise"></i> Reset Semua
        </a>
    </div>
    
    <div class="recap-filter-grid recap-filter-grid-module">
        <div class="recap-filter-search">
            <label for="pemakaian_referensi">Cari nomor pengajuan</label>
            <input class="form-control" id="pemakaian_referensi" name="pemakaian_referensi" 
                   value="{{ $filters['pemakaian_referensi'] ?? '' }}" maxlength="64" 
                   placeholder="Ketik nomor transaksi…" type="search">
        </div>
        <div>
            <label for="pemakaian_periode">Periode pengajuan</label>
            <input class="form-control" type="month" name="pemakaian_periode" 
                   id="pemakaian_periode" value="{{ $filters['pemakaian_periode'] }}">
        </div>
        <div class="recap-filter-actions">
            <button class="btn btn-primary btn-sm" type="submit">
                <i class="bi bi-search"></i> Tampilkan
            </button>
        </div>
    </div>
    
    <div class="recap-filter-note">
        <span>{{ $filters['pemakaian_periode'] ? 'Periode '.$filters['pemakaian_periode'] : 'Seluruh periode' }}</span>
        <a href="{{ route($pageRoute, array_merge($reportFilters, ['pemakaian_periode' => app(\App\Services\PeriodService::class)->current()])) }}#pemakaian-bs">Bulan ini</a>
        <a href="{{ route($pageRoute, array_merge($reportFilters, ['pemakaian_periode' => ''])) }}#pemakaian-bs">Semua periode</a>
        <span>Filter diterapkan setelah memilih Tampilkan.</span>
    </div>
    
    <nav class="recap-status-tabs" aria-label="Filter status Finance">
        @foreach($statusLabels as $value => $label)
            @continue($value === 'REVISION' && !$summary->revision_count && $currentStatus !== 'REVISION')
            @php $count = (int) ($statusCounts[$value] ?? 0); @endphp
            <a href="{{ route($pageRoute, array_merge($reportFilters, ['pemakaian_status' => $value])) }}#pemakaian-bs" 
               @if($currentStatus === $value) aria-current="true" @endif>
                {{ $label }}
                <span class="tab-count @if($count === 0) is-zero @endif" aria-label="{{ $count }} transaksi">{{ $count }}</span>
            </a>
        @endforeach
    </nav>
</form>