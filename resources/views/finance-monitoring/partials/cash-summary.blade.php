@php
    $totalMasuk = is_array($summary) ? ($summary['total_masuk'] ?? '0.00') : ($summary->total_masuk ?? '0.00');
    $totalKeluar = is_array($summary) ? ($summary['total_keluar'] ?? '0.00') : ($summary->total_keluar ?? '0.00');
    $saldoBerjalan = is_array($summary) ? ($summary['saldo_berjalan'] ?? '0.00') : ($summary->saldo_berjalan ?? '0.00');
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h2 class="h5 mb-0">{{ $title ?? 'Ringkasan Stok Kas' }}</h2></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4"><div class="border-start border-primary border-4 ps-3"><div class="text-muted small">Total Uang Masuk</div><div class="fs-5 fw-bold text-nowrap">Rp {{ number_format((float) $totalMasuk, 2, ',', '.') }}</div></div></div>
            <div class="col-12 col-md-4"><div class="border-start border-danger border-4 ps-3"><div class="text-muted small">Total Uang Keluar</div><div class="fs-5 fw-bold text-nowrap">Rp {{ number_format((float) $totalKeluar, 2, ',', '.') }}</div></div></div>
            <div class="col-12 col-md-4"><div class="border-start border-success border-4 ps-3"><div class="text-muted small">Sisa Stok Kas Berjalan</div><div class="fs-5 fw-bold text-nowrap">Rp {{ number_format((float) $saldoBerjalan, 2, ',', '.') }}</div></div></div>
        </div>
    </div>
</div>