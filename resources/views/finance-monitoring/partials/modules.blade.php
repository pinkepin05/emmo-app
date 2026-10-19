@php
    $modules = [
        ['jenis' => 'OPR', 'label' => 'Operasional Sales', 'icon' => 'bi-truck'],
        ['jenis' => 'PD', 'label' => 'Perjalanan Dinas', 'icon' => 'bi-airplane'],
        ['jenis' => 'EDK', 'label' => 'Event Dalam Kota', 'icon' => 'bi-geo-alt'],
        ['jenis' => 'ELK', 'label' => 'Event Luar Kota', 'icon' => 'bi-globe2'],
    ];
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h2 class="h5 mb-0">Daftar Transaksi per Modul</h2></div>
    <div class="card-body">
        <p class="text-muted small mb-3">Pilih modul untuk membuka daftar transaksi {{ $user->nama }} pada halaman modul terkait.</p>
        <div class="row g-3">
            @foreach ($modules as $m)
                <div class="col-6 col-md-3">
                    <a href="{{ route('monitoring-finance.module.index', [
                        'company' => $company->getKey(),
                        'user' => $user->getKey(),
                        'jenis' => $m['jenis'],
                        'periode' => $periode,
                    ]) }}" class="card h-100 text-decoration-none border">
                        <div class="card-body text-center">
                            <i class="bi {{ $m['icon'] }} fs-3 text-primary d-block mb-2"></i>
                            <span class="fw-semibold">{{ $m['label'] }}</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>