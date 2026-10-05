<!DOCTYPE html>
<html lang="id">
<head>
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    @php Carbon\Carbon::setLocale('id'); @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Cash Flow - {{ Carbon\Carbon::createFromFormat('!Y-m', $periode)->translatedFormat('F Y') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('cash-flow.partials.styles')

    {{-- ========================================================== --}}
    {{-- CSS TAMBAHAN untuk pagination & fragment anchor            --}}
    {{-- ========================================================== --}}
    <style>
        /* Agar saat mendarat via fragment (#sec-masuk), puncak card
           tidak tertutup bar sticky/fixed. Sesuaikan dgn tinggi bar Anda. */
        .cf-card { scroll-margin-top: 76px; }

        /* Info "Menampilkan x–y dari z" */
        .trx-range {
            text-align: center;
            font-size: .75rem;
            color: #64748b;
            padding: .75rem 0 0;
        }

        /* Pembungkus paginator agar rapi di tengah */
        .trx-paginate {
            display: flex;
            justify-content: center;
            padding: .5rem 0 .75rem;
        }
        .trx-paginate .pagination { margin-bottom: 0; }

        /* Penyesuaian BS tetap pakai progressive disclosure (biasanya sedikit item) */
        .trx-list.is-collapsed .trx-item:nth-child(n+6) { display: none; }
        .trx-list { position: relative; }
        .trx-list.is-collapsed::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 64px;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, #ffffff 95%);
            pointer-events: none;
        }
        .trx-more {
            display: flex;
            justify-content: center;
            padding: .5rem 0 .75rem;
        }
        .btn-toggle-list {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-width: 240px;
        }
        .btn-toggle-list .bi { transition: transform .2s ease; }
        .btn-toggle-list.expanded .bi { transform: rotate(180deg); }
        /* Indicator saat HTMX sedang memuat */
.htmx-request .trx-list,
.htmx-request .trx-paginate {
    opacity: 0.4;
    transition: opacity 0.15s ease;
    pointer-events: none;
}

/* Indikator "spinner" kecil di card yang sedang loading */
.cf-card.htmx-request::before {
    content: '';
    position: absolute;
    top: 12px; right: 12px;
    width: 18px; height: 18px;
    border: 2px solid #e2e8f0;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    z-index: 5;
}
@keyframes spin { to { transform: rotate(360deg); } }

.cf-card { position: relative; }
    </style>
</head>
<body class="cf-body">

    @include('partials.application-time')

    <div class="cf-wrap narrow">

        @php
            $rpSigned           = fn($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
            $isClosed           = $status === 'CLOSED';
            $showClosingBalance = $isClosed && $snapshot->saldo_akhir !== null;
            $periodLabel        = str_replace('-', '', $periode);
            $periodName         = Carbon\Carbon::createFromFormat('!Y-m', $periode)->translatedFormat('F Y');
        @endphp

        {{-- ===== Header ===== --}}
        <div class="cf-header">
            <div>
                <h1 class="cf-title">Detail Cash Flow</h1>
                <p class="cf-subtitle">
                    {{ $periodLabel }} · {{ $periodName }} — saldo laporan mencakup BS dan event
                    sesuai rincian. Sisa Saldo BS dihitung terpisah.
                </p>
            </div>
            <div class="cf-actions">
                <a href="{{ route('bs-balance.show', ['periode' => $periode]) }}" class="btn-cf ghost">Rincian Saldo BS</a>
                <a href="{{ route('cash-flow.export-period', $periodLabel) }}" class="btn-cf primary" title="Export periode ini ke Excel">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
                <a href="{{ route('cash-flow.index') }}" class="btn-cf ghost">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- ===== Ringkasan ===== --}}
        <div class="cf-card accent">
            <div class="cf-card-head">
                <div>
                    <div class="eyebrow">Ringkasan Stok Kas</div>
                    <h2 class="period-lg">{{ $periodLabel }} <span>· {{ $periodName }}</span></h2>
                </div>
                <span class="pill {{ $isClosed ? 'closed' : 'open' }}">
                    @if(!$isClosed)<span class="dot"></span>@endif
                    {{ $status }}
                </span>
            </div>

            @if(!$snapshot->opening_final)
                <div class="cf-banner warn">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Saldo awal menunggu penutupan periode sebelumnya.
                </div>
            @endif

            @if($snapshot->penyesuaian != 0)
                <div class="cf-banner neutral">
                    <i class="bi bi-pencil-square"></i>
                    Termasuk penyesuaian BS: <strong class="amt">{{ $rpSigned($snapshot->penyesuaian) }}</strong>
                </div>
            @endif

            <div class="cf-stats">
                <div class="cf-stat">
                    <span class="lbl">Saldo Awal</span>
                    <span class="val amt {{ $snapshot->saldo_awal < 0 ? 'neg' : 'neu' }}">{{ $rpSigned($snapshot->saldo_awal) }}</span>
                </div>
                <div class="cf-stat">
                    <span class="lbl">Total Masuk</span>
                    <span class="val amt pos">+ Rp {{ number_format($totalMasuk, 0, ',', '.') }}</span>
                </div>
                <div class="cf-stat">
                    <span class="lbl">Total Keluar</span>
                    <span class="val amt neg">- Rp {{ number_format($totalKeluar, 0, ',', '.') }}</span>
                </div>
                @if($showClosingBalance)
                    <div class="cf-stat">
                        <span class="lbl">Saldo Akhir</span>
                        <span class="val amt {{ $snapshot->saldo_akhir < 0 ? 'neg' : 'neu' }}">{{ $rpSigned($snapshot->saldo_akhir) }}</span>
                    </div>
                @endif
            </div>

            <div class="bd-wrap">
                @include('cash-flow.partials.expense-breakdown', ['snapshot' => $snapshot])
            </div>
        </div>

        {{-- ===== Penyesuaian BS (tanpa pagination, jumlah item biasanya sedikit) ===== --}}
        @if(count($transactions['penyesuaian']) > 0)
            @php $countPenyesuaian = count($transactions['penyesuaian']); @endphp
            <div class="cf-card" id="sec-penyesuaian">
                <div class="cf-card-head">
                    <h3 class="cf-head-title">
                        <span class="cf-ico ico-amber"><i class="bi bi-pencil-square"></i></span>
                        Penyesuaian BS
                    </h3>
                    <span class="cf-badge">{{ $countPenyesuaian }} item</span>
                </div>

                <div class="trx-list {{ $countPenyesuaian > 5 ? 'is-collapsed' : '' }}" id="list-penyesuaian">
                    @foreach($transactions['penyesuaian'] as $trx)
                        @if(!empty($trx['url']))
                            <a href="{{ $trx['url'] }}" class="trx trx-item trx-link">
                        @else
                            <div class="trx trx-item">
                        @endif
                            <span class="cf-ico ico-amber"><i class="bi bi-arrow-left-right"></i></span>
                            <div class="trx-body">
                                <div class="trx-desc">{{ $trx['description'] }}</div>
                                @if(!empty($trx['url']))
                                    <span class="trx-open">Lihat detail <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                                @endif
                            </div>
                            <div class="trx-amt neu amt">{{ $rpSigned($trx['nominal']) }}</div>
                        @if(!empty($trx['url']))</a>@else</div>@endif
                    @endforeach
                </div>

                @if($countPenyesuaian > 5)
                    <div class="trx-more">
                        <button type="button" class="btn-cf ghost btn-toggle-list"
                                data-target="list-penyesuaian"
                                data-more="Lihat semua ({{ $countPenyesuaian }} item)"
                                data-less="Sembunyikan sebagian"
                                aria-expanded="false" aria-controls="list-penyesuaian">
                            <i class="bi bi-chevron-down"></i>
                            <span class="lbl-toggle">Lihat semua ({{ $countPenyesuaian }} item)</span>
                        </button>
                    </div>
                @endif

                <div class="trx-foot">
                    <span class="lbl">Total Penyesuaian:</span>
                    <span class="val amt">{{ $rpSigned($totalPenyesuaian) }}</span>
                </div>
            </div>
        @endif

        {{-- ===== Uang Masuk (HTMX-powered pagination) ===== --}}
<div class="cf-card" id="sec-masuk">
    <div
        id="sec-masuk-content"
        hx-get="{{ route('cash-flow.transactions-partial', ['periode' => $periode, 'section' => 'masuk', 'masuk_page' => $pagedMasuk->currentPage(), 'keluar_page' => $pagedKeluar->currentPage()]) }}"
        hx-trigger="load"
        hx-swap="innerHTML"
    >
        {{-- Konten initial dari server (rendered server-side) --}}
        @include('cash-flow.partials.transaction-section', [
            'section' => 'masuk',
            'paginator' => $pagedMasuk,
            'total' => $totalMasuk,
            'totalLabel' => $totalMasuk,
            'sectionId' => 'sec-masuk',
            'otherPage' => $pagedKeluar->currentPage(),
            'otherSection' => 'keluar',
        ])
    </div>
</div>

{{-- ===== Uang Keluar (HTMX-powered pagination) ===== --}}
<div class="cf-card" id="sec-keluar">
    <div
        id="sec-keluar-content"
        hx-get="{{ route('cash-flow.transactions-partial', ['periode' => $periode, 'section' => 'keluar', 'masuk_page' => $pagedMasuk->currentPage(), 'keluar_page' => $pagedKeluar->currentPage()]) }}"
        hx-trigger="load"
        hx-swap="innerHTML"
    >
        @include('cash-flow.partials.transaction-section', [
            'section' => 'keluar',
            'paginator' => $pagedKeluar,
            'total' => $totalKeluar,
            'totalLabel' => $totalKeluar,
            'sectionId' => 'sec-keluar',
            'otherPage' => $pagedMasuk->currentPage(),
            'otherSection' => 'masuk',
        ])
    </div>
</div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JS untuk Penyesuaian BS (tetap pakai progressive disclosure) --}}
    <script>
        document.querySelectorAll('.btn-toggle-list').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const list = document.getElementById(btn.dataset.target);
                if (!list) return;

                const card = list.closest('.cf-card');
                const isCollapsed = list.classList.toggle('is-collapsed');
                btn.querySelector('.lbl-toggle').textContent = isCollapsed ? btn.dataset.more : btn.dataset.less;
                btn.classList.toggle('expanded', !isCollapsed);
                btn.setAttribute('aria-expanded', String(!isCollapsed));

                if (isCollapsed && card) {
                    const rect = card.getBoundingClientRect();
                    if (rect.top < 0) {
                        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    </script>
</body>
</html>