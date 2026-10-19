@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])

@section('title', 'Panel Transaksi - EMMO')

@push('styles')
<style>
    /* Filter dan saldo memakai dua kartu dengan jarak serta tinggi yang seimbang. */
    .dashboard-combined {
        display: grid;
        grid-template-columns: minmax(240px, 270px) minmax(0, 1fr);
        gap: 1rem;
        align-items: stretch;
        min-width: 0;
        scroll-margin-top: 6rem;
        margin-bottom: 1.5rem;
    }

    /* --- BAGIAN FILTER (KIRI) --- */
    .dashboard-filter-section {
        min-width: 0;
        padding: 1.25rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
    }
    .dashboard-period-filter { min-width: 0; scroll-margin-top: 6rem; }
    .dashboard-period-filter__controls { display: grid; gap: .65rem; }
    .dashboard-period-filter__field { min-width: 0; }
    .dashboard-filter-section h2 {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin: 0 0 1rem;
        font-size: .95rem;
        font-weight: 700;
        color: #1e293b;
    }
    .dashboard-filter-section h2 i { color: #6366f1; }
    .dashboard-filter-section label {
        display: block;
        margin-bottom: .4rem;
        font-size: .8rem;
        font-weight: 600;
        color: #475569;
    }
    .dashboard-filter-section input {
        min-width: 0;
        width: 100%;
        min-height: 44px;
        font-size: .85rem;
        padding: .5rem .75rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    .dashboard-filter-section button {
        width: 100%;
        min-height: 42px;
        margin: 0;
        font-size: .85rem;
        padding: .65rem 1rem;
    }
   /* Shortcuts - Bulan Ini & Semua Periode */
/* .dashboard-period-filter__shortcuts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.5rem;
    margin-top: .75rem;
}

/* Ubah dari link menjadi button style */
/* .dashboard-period-filter__shortcuts a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
    padding: .45rem .35rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    color: #475569;
    font-size: .74rem;
    font-weight: 600;
    background: #fff;
    cursor: pointer;
    transition: background-color .2s ease, border-color .2s ease, color .2s ease;
    min-height: 38px;
}

/* Hover state */
/* .dashboard-period-filter__shortcuts a:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #1e293b;
} */

/* Active/current state - saat button dipilih */
/* .dashboard-period-filter__shortcuts a[aria-current="true"] {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
} */

/* Focus state untuk aksesibilitas */
/* .dashboard-period-filter__shortcuts a:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
} */ */ */
    .dashboard-filter-section p {
        margin: .8rem 0 0;
        color: #64748b;
        font-size: .76rem;
        line-height: 1.6;
    }
    .dashboard-filter-section .dashboard-period-filter__active { margin-top: 1rem; padding-top: .85rem; border-top: 1px solid #edf1f7; }
    .dashboard-period-filter__active strong { color: #334155; }
    .dashboard-filter-section #dashboard-period-note { margin-top: .5rem; font-size: .72rem; }

    /* --- BAGIAN SALDO BS (KANAN) --- */
    .dashboard-saldo-section {
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        position: relative;
        border-radius: 16px;
        box-shadow: 0 3px 12px rgba(91, 33, 182, .08);
    }

    .dashboard-saldo-section__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .dashboard-saldo-section__summary { flex: 1; min-width: 0; }
    .dashboard-saldo-section__identity > div { min-width: 0; }

    .dashboard-saldo-section__identity {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }
    .dashboard-saldo-section__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.25);
        font-size: 1.25rem;
    }
    .dashboard-saldo-section__title {
        margin: 0 0 .2rem;
        font-size: 1.05rem;
        font-weight: 800;
    }
    .dashboard-saldo-section__period {
        margin: 0;
        font-size: .85rem;
        color: rgba(255,255,255,.9);
    }
    .dashboard-saldo-section__balance {
        margin: .85rem 0 0;
        font-size: clamp(2rem, 4vw, 2.8rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -.03em;
        font-variant-numeric: tabular-nums;
        overflow-wrap: anywhere;
    }

    /* Tombol Lihat Detail */
    .dashboard-saldo-section__link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: .6rem .9rem;
        border-radius: 10px;
        background: #fff;
        color: #5b21b6;
        text-decoration: none;
        font-size: .8rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .dashboard-saldo-section__link:hover { background: #f5f3ff; color: #4c1d95; }
    .dashboard-saldo-section__link:focus-visible { outline: 3px solid #fff; outline-offset: 3px; }

    .dashboard-saldo-section__description {
        margin: 0 0 1.2rem;
        font-size: .82rem;
        line-height: 1.6;
        color: rgba(255,255,255,.9);
        max-width: 85ch;
    }
    .dashboard-saldo-section__notice {
        margin: 0 0 1rem;
        padding: .65rem .85rem;
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 9px;
        background: rgba(255,255,255,.1);
        font-size: .8rem;
    }

    .dashboard-saldo-section__totals {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: auto;
    }
    .dashboard-saldo-section__total {
        min-width: 0;
        padding: 0.85rem 0.9rem;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 12px;
        background: rgba(255,255,255,.08);
    }
    .dashboard-saldo-section__label {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .45rem;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: rgba(255,255,255,.9);
    }
    .dashboard-saldo-section__amount {
        font-size: clamp(1.1rem, 2vw, 1.3rem);
        font-weight: 700;
        line-height: 1.3;
        font-variant-numeric: tabular-nums;
        overflow-wrap: anywhere;
    }
    .dashboard-saldo-section__amount.is-incoming { color: #a7f3d0; }
    .dashboard-saldo-section__amount.is-outgoing { color: #fecaca; }

    /* Responsive */
    @media(max-width: 1199.98px){
        .dashboard-combined {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media(min-width: 768px) and (max-width: 1199.98px){
        .dashboard-period-filter { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: .75rem 1.25rem; }
        .dashboard-filter-section h2 { grid-column: 1 / -1; margin: 0; }
        .dashboard-period-filter__controls { grid-template-columns: minmax(0, 1fr) auto; align-items: end; }
        /* .dashboard-period-filter__shortcuts { align-self: end; margin: 0; }
        .dashboard-period-filter__shortcuts a { min-height: 44px; } */
        .dashboard-filter-section .dashboard-period-filter__active { margin: 0; padding: 0; border: 0; }
        .dashboard-filter-section #dashboard-period-note { margin: 0; }
        .dashboard-period-filter__controls {
    grid-column: 1 / -1;
}
    }

    @media(max-width: 575.98px){
        .dashboard-combined { gap: .85rem; scroll-margin-top: 8rem; }
        .dashboard-period-filter { scroll-margin-top: 8rem; }
        .dashboard-filter-section,
        .dashboard-saldo-section {
            padding: 1rem;
        }
        .dashboard-saldo-section__header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        .dashboard-saldo-section__link {
            width: 100%;
            justify-content: center;
        }
        .dashboard-saldo-section__identity {
            gap: .75rem;
        }
        .dashboard-saldo-section__totals {
            grid-template-columns: 1fr;
            gap: .75rem;
        }
        .dashboard-saldo-section__total {
            padding: .85rem 1rem;
        }
    }
</style>
@endpush

@section('content')
    @php
        $breadcrumbs = [
            ...(auth()->user()->canAccessPortal() ? ['Portal' => route('dashboard')] : []),
            'Panel Transaksi' => route('dashboard.transaksi'),
        ];
    @endphp

    {{-- Welcome Banner --}}
    <div class="mb-4 p-4" style="background:linear-gradient(135deg,#3b82f6 0%,#6366f1 100%);border-radius:18px;color:white;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-40%;right:-5%;width:280px;height:280px;background:radial-gradient(circle,rgba(255,255,255,0.15),transparent 70%);border-radius:50%;"></div>
        <div style="position:relative;z-index:1;">
            <h2 style="font-weight:800;margin-bottom:0.25rem;">Dashboard Transaksi</h2>
            <p style="opacity:0.9;margin:0;">Kelola dan pantau semua transaksi operasional perusahaan secara terpusat, efisien, dan transparan.</p>
        </div>
    </div>

    {{-- Filter Periode dan Saldo BS --}}
    @php
        $thisMonth = app(\App\Services\PeriodService::class)->current();
        $selectedPeriodLabel = $selectedPeriod !== ''
            ? \Carbon\Carbon::createFromFormat('!Y-m', $selectedPeriod)->locale('id')->translatedFormat('F Y')
            : 'Semua Periode';
        $cashDetailUrl = route('cash-flow.index', ['semua_periode' => 1]);
        $rpSigned = fn($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
    @endphp

    <div class="dashboard-combined" id="dashboard-overview">
        {{-- BAGIAN KIRI: Filter Periode --}}
        <div class="dashboard-filter-section">
            @include('dashboard.period-filter')
        </div>

        {{-- BAGIAN KANAN: Saldo BS --}}
        <section class="dashboard-saldo-section" aria-labelledby="dashboard-saldo-title">
            <div class="dashboard-saldo-section__header">
                <div class="dashboard-saldo-section__summary">
                    <div class="dashboard-saldo-section__identity">
                        <span class="dashboard-saldo-section__icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                        <div>
                            <h2 id="dashboard-saldo-title" class="dashboard-saldo-section__title">Saldo BS</h2>
                            <p class="dashboard-saldo-section__period">Stok Kas &middot; @if($selectedPeriod !== ''){{ str_replace('-', '', $selectedPeriod) }} &middot; @endif{{ $selectedPeriodLabel }}</p>
                        </div>
                    </div>
                    <p class="dashboard-saldo-section__balance">{{ $rpSigned($cashSnapshot->saldo_berjalan) }}</p>
                </div>
                @can('akses.cash-flow')
                <a href="{{ $cashDetailUrl }}" class="dashboard-saldo-section__link">Lihat Detail <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endcan
            </div>

            <p class="dashboard-saldo-section__description">Termasuk event. Saldo BS berkurang sesuai nominal yang dikonfirmasi Finance.</p>

            @can('akses.cash-flow')
            @if(!$cashSnapshot->opening_final)
                <p class="dashboard-saldo-section__notice">Saldo awal menunggu penutupan periode sebelumnya.</p>
            @endif
            @if($cashSnapshot->penyesuaian != 0)
                <p class="dashboard-saldo-section__notice">Penyesuaian BS: {{ $rpSigned($cashSnapshot->penyesuaian) }}</p>
            @endif

            <div class="dashboard-saldo-section__totals">
                <div class="dashboard-saldo-section__total">
                    <div class="dashboard-saldo-section__label"><i class="bi bi-cash-stack" aria-hidden="true"></i> Saldo Awal</div>
                    <div class="dashboard-saldo-section__amount {{ $cashSnapshot->saldo_awal < 0 ? 'is-outgoing' : '' }}">{{ $rpSigned($cashSnapshot->saldo_awal) }}</div>
                </div>
                <div class="dashboard-saldo-section__total">
                    <div class="dashboard-saldo-section__label"><i class="bi bi-box-arrow-in-down" aria-hidden="true"></i> Total Masuk</div>
                    <div class="dashboard-saldo-section__amount is-incoming">+Rp {{ number_format($cashSnapshot->total_masuk ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="dashboard-saldo-section__total">
                    <div class="dashboard-saldo-section__label"><i class="bi bi-box-arrow-up" aria-hidden="true"></i> Total Keluar</div>
                    <div class="dashboard-saldo-section__amount is-outgoing">-Rp {{ number_format($cashSnapshot->total_keluar ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
            @endcan
        </section>
    </div>

    @include('transaction-recap.panel', $bsUsageDashboard)
@endsection
