<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Stok Kas - Dashboard Keuangan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('cash-flow.partials.styles')
    @include('partials.mobile-assets')
</head>
<body class="cf-body">

    @include('partials.application-time')

    <div class="cf-wrap">

        @php
            Carbon\Carbon::setLocale('id');
            $rpSigned  = fn($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
            $statusMap = \App\Models\Periode::whereIn('periode', $snapshots->pluck('periode'))
                            ->pluck('status', 'periode');
            $showAllPeriods = $showAllPeriods ?? false;
            $historySnapshots = $snapshots->where('periode', '<', $currentPeriod);
        @endphp

        {{-- ===== Header ===== --}}
        <div class="cf-header">
            <div>
                <h1 class="cf-title">Stok Kas</h1>
                <p class="cf-subtitle">
                    Total Keluar mengikuti nominal yang dikonfirmasi Finance, termasuk event.
                    Pengeluaran ini mengurangi Sisa Saldo BS.
                </p>
            </div>
            <div class="cf-actions">
                <a href="{{ route('cash-flow.export', $showAllPeriods ? ['semua_periode' => 1] : []) }}" class="btn-cf primary">
                    <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
                </a>
                <a href="{{ route('dashboard.transaksi') }}" class="btn-cf ghost">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- ===== Summary Periode Aktif ===== --}}
        @if($currentSnapshot)
            @php
                $currentStatus      = $statusMap[$currentPeriod] ?? 'OPEN';
                $isClosed           = $currentStatus === 'CLOSED';
                $showClosingBalance = $isClosed && $currentSnapshot->saldo_akhir !== null;
            @endphp

            <div class="cf-card accent">
                <div class="cf-card-head">
                    <div>
                        <div class="eyebrow">Periode Berjalan</div>
                        <h2 class="period-lg">
                            {{ str_replace('-', '', $currentPeriod) }}
                            <span>· {{ Carbon\Carbon::createFromFormat('!Y-m', $currentPeriod)->translatedFormat('F Y') }}</span>
                        </h2>
                    </div>
                    <div class="h-actions">
                        <span class="pill {{ $isClosed ? 'closed' : 'open' }}">
                            @if(!$isClosed)<span class="dot"></span>@endif
                            {{ $currentStatus }}
                        </span>
                        <a href="{{ route('cash-flow.show', ['periode' => str_replace('-', '', $currentPeriod)] + ($showAllPeriods ? ['semua_periode' => 1] : [])) }}" class="btn-detail">
                            <i class="bi bi-eye-fill"></i> Detail
                        </a>
                        <a href="{{ route('cash-flow.export-period', str_replace('-', '', $currentPeriod)) }}" class="btn-icon" title="Export Excel periode ini">
                            <i class="bi bi-file-earmark-excel"></i>
                        </a>
                    </div>
                </div>

                @if(!$currentSnapshot->opening_final)
                    <div class="cf-banner warn">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Saldo awal menunggu penutupan periode sebelumnya. Angka saat ini bersifat estimasi sementara.
                    </div>
                @endif

                @if($currentSnapshot->penyesuaian != 0)
                    <div class="cf-banner neutral">
                        <i class="bi bi-pencil-square"></i>
                        Penyesuaian Manual BS: <strong class="amt">{{ $rpSigned($currentSnapshot->penyesuaian) }}</strong>
                    </div>
                @endif

                <div class="cf-stats">
                    <div class="cf-stat">
                        <span class="cf-ico ico-blue"><i class="bi bi-wallet2"></i></span>
                        <span class="lbl">Saldo Awal</span>
                        <span class="val amt {{ $currentSnapshot->saldo_awal < 0 ? 'neg' : 'neu' }}">
                            {{ $rpSigned($currentSnapshot->saldo_awal) }}
                        </span>
                    </div>
                    <div class="cf-stat">
                        <span class="cf-ico ico-green"><i class="bi bi-box-arrow-in-down"></i></span>
                        <span class="lbl">Total Masuk</span>
                        <span class="val amt pos">+ Rp {{ number_format($currentSnapshot->total_masuk, 0, ',', '.') }}</span>
                    </div>
                    <div class="cf-stat">
                        <span class="cf-ico ico-red"><i class="bi bi-box-arrow-up"></i></span>
                        <span class="lbl">Total Keluar</span>
                        <span class="val amt neg">- Rp {{ number_format($currentSnapshot->total_keluar, 0, ',', '.') }}</span>
                    </div>
                    @if($showClosingBalance || $currentSnapshot->finance_confirmed)
                        <div class="cf-stat">
                            <span class="cf-ico ico-slate"><i class="bi bi-cash-stack"></i></span>
                            <span class="lbl">{{ $showClosingBalance ? 'Saldo Akhir' : 'Sisa Saldo BS' }}</span>
                            <span class="val amt {{ $currentSnapshot->saldo_berjalan < 0 ? 'neg' : 'neu' }}">
                                {{ $rpSigned($currentSnapshot->saldo_berjalan) }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="bd-wrap">
                    @include('cash-flow.partials.expense-breakdown', ['snapshot' => $currentSnapshot])
                </div>
            </div>
        @endif

        {{-- ===== Riwayat Periode ===== --}}
        <div class="cf-section-head">
            <h3>
                <i class="bi bi-clock-history"></i> Riwayat Periode
                @if($historySnapshots->count() > 0)
                    <span class="cf-badge">{{ $historySnapshots->count() }}</span>
                @endif
            </h3>
        </div>

        @forelse($historySnapshots as $snapshot)
            @php
                $status      = $statusMap[$snapshot->periode] ?? 'OPEN';
                $isOpen      = $status === 'OPEN';
                $showEnd     = !$isOpen && $snapshot->saldo_akhir !== null;
                $periodLabel = str_replace('-', '', $snapshot->periode);
                $periodName  = Carbon\Carbon::createFromFormat('!Y-m', $snapshot->periode)->translatedFormat('M Y');
            @endphp

            <article class="h-card">
                <div class="h-top">
                    <div class="h-id">
                        <span class="cf-ico {{ $isOpen ? 'ico-blue' : 'ico-slate' }}">
                            <i class="bi {{ $isOpen ? 'bi-calendar2-check' : 'bi-archive' }}"></i>
                        </span>
                        <div>
                            <h4 class="h-title">{{ $periodLabel }} <span>· {{ $periodName }}</span></h4>
                            <div class="h-meta">
                                <i class="bi bi-stopwatch"></i>
                                Diperbarui: {{ $snapshot->last_updated?->diffForHumans() ?? 'belum ada update' }}
                            </div>
                        </div>
                    </div>

                    <div class="h-actions">
                        <span class="pill {{ $isOpen ? 'open' : 'closed' }} d-none d-md-inline-flex">{{ $status }}</span>
                        <a href="{{ route('cash-flow.show', ['periode' => $periodLabel] + ($showAllPeriods ? ['semua_periode' => 1] : [])) }}" class="btn-detail">
                            <i class="bi bi-eye-fill"></i> Detail
                        </a>
                        <a href="{{ route('cash-flow.export-period', $periodLabel) }}" class="btn-icon" title="Export Excel periode ini">
                            <i class="bi bi-file-earmark-excel"></i>
                        </a>
                    </div>
                </div>

                @if(!$snapshot->opening_final)
                    <div class="h-alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Saldo awal masih menunggu konfirmasi penutupan periode sebelumnya.
                    </div>
                @endif

                @if(!$snapshot->finance_confirmed)
                    <p class="h-meta">Arsip periode ditutup menggunakan perhitungan sebelum sinkronisasi Finance.</p>
                @endif
                <div class="h-stats {{ $showEnd || $snapshot->finance_confirmed ? 'with-end' : '' }}">
                    <div class="h-stat">
                        <span class="lbl">Awal</span>
                        <span class="val amt {{ $snapshot->saldo_awal < 0 ? 'text-danger' : '' }}">
                            {{ $rpSigned($snapshot->saldo_awal) }}
                        </span>
                    </div>
                    <div class="h-stat">
                        <span class="lbl">Masuk</span>
                        <span class="val amt pos">+ Rp {{ number_format($snapshot->total_masuk, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-stat">
                        <span class="lbl">Keluar</span>
                        <span class="val amt neg">- Rp {{ number_format($snapshot->total_keluar, 0, ',', '.') }}</span>
                    </div>
                    @if($showEnd || $snapshot->finance_confirmed)
                        <div class="h-stat end">
                            <span class="lbl">{{ $showEnd ? 'Akhir' : 'Sisa Saldo BS' }}</span>
                            <span class="val amt {{ $snapshot->saldo_berjalan < 0 ? 'text-danger' : '' }}">
                                {{ $rpSigned($snapshot->saldo_berjalan) }}
                            </span>
                        </div>
                    @endif
                </div>

                @if($snapshot->penyesuaian != 0)
                    <div class="h-meta mt-2">
                        <i class="bi bi-pencil-square"></i>
                        Penyesuaian manual: <strong class="amt">{{ $rpSigned($snapshot->penyesuaian) }}</strong>
                    </div>
                @endif

                <div class="h-break">
                    @include('cash-flow.partials.expense-breakdown', ['snapshot' => $snapshot])
                </div>
            </article>
        @empty
            <div class="cf-empty">
                <i class="bi bi-inbox"></i>
                <h4>Belum Ada Data Historis</h4>
                @if($currentSnapshot)
                    <p>Belum ada periode lain. Periode berjalan ditampilkan di atas.</p>
                @else
                    <p class="mb-4">Mulai catat transaksi pertama Anda untuk melihat laporan cash flow bulanan.</p>
                    <a href="{{ route('transaksi.create') }}" class="btn-cf primary">
                        <i class="bi bi-plus-lg"></i> Buat Transaksi Baru
                    </a>
                @endif
            </div>
        @endforelse

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
