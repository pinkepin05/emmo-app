@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])

@section('title', 'Panel Transaksi - EMMO')

@push('styles')
<style>
    .dashboard-stock{margin-top:1.5rem;padding:1.5rem;border-radius:18px;color:#fff;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);}
    .dashboard-stock__header{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;}
    .dashboard-stock__identity{display:flex;align-items:center;gap:1rem;min-width:0;}
    .dashboard-stock__icon{display:flex;align-items:center;justify-content:center;flex:0 0 48px;height:48px;border-radius:12px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.25);font-size:1.25rem;}
    .dashboard-stock__title{margin:0 0 .2rem;font-size:1.05rem;font-weight:800;}
    .dashboard-stock__period{margin:0;font-size:.85rem;color:rgba(255,255,255,.9);}
    .dashboard-stock__link{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.65rem 1rem;border-radius:10px;background:#fff;color:#5b21b6;text-decoration:none;font-size:.82rem;font-weight:700;white-space:nowrap;}
    .dashboard-stock__link:hover{background:#f5f3ff;color:#4c1d95;}
    .dashboard-stock__link:focus-visible{outline:3px solid #fff;outline-offset:3px;}
    .dashboard-stock__description{margin:0 0 1.2rem;font-size:.82rem;line-height:1.6;color:rgba(255,255,255,.9);max-width:85ch;}
    .dashboard-stock__notice{margin:0 0 1rem;padding:.65rem .85rem;border:1px solid rgba(255,255,255,.2);border-radius:9px;background:rgba(255,255,255,.1);font-size:.8rem;}
    .dashboard-stock__totals{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;}
    .dashboard-stock__totals.has-closing{grid-template-columns:repeat(3,minmax(0,1fr));}
    .dashboard-stock__total{min-width:0;padding:1rem 1.1rem;border:1px solid rgba(255,255,255,.23);border-radius:12px;background:rgba(255,255,255,.13);}
    .dashboard-stock__label{display:flex;align-items:center;gap:.45rem;margin-bottom:.45rem;font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:rgba(255,255,255,.9);}
    .dashboard-stock__amount{font-size:clamp(1.2rem,2vw,1.5rem);font-weight:800;line-height:1.3;font-variant-numeric:tabular-nums;overflow-wrap:anywhere;}
    .dashboard-stock__amount.is-incoming{color:#a7f3d0;}
    .dashboard-stock__amount.is-outgoing{color:#fecaca;}
    .dashboard-stock__breakdown{margin-top:1.25rem;padding-top:1.1rem;border-top:1px solid rgba(255,255,255,.2);}
    .dashboard-stock__breakdown-title{margin:0 0 .8rem;font-size:.8rem;font-weight:700;color:rgba(255,255,255,.92);}
    .dashboard-stock .bd-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem;}
    .dashboard-stock .bd-cell{display:flex;flex-direction:column;justify-content:space-between;gap:.5rem;min-width:0;padding:.85rem 1rem;border:1px solid rgba(255,255,255,.16);border-radius:10px;background:rgba(25,20,76,.12);}
    .dashboard-stock .bd-label{font-size:.74rem;font-weight:500;line-height:1.4;color:rgba(255,255,255,.88);}
    .dashboard-stock .bd-value{font-size:.98rem;font-weight:700;line-height:1.4;color:#fff;font-variant-numeric:tabular-nums;overflow-wrap:anywhere;}
    .dashboard-stock .bd-note{display:flex;align-items:flex-start;gap:.5rem;margin:.8rem 0 0;color:rgba(255,255,255,.85);font-size:.75rem;}
    @media(max-width:991.98px){
        .dashboard-stock .bd-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
    }
    @media(max-width:575.98px){
        .dashboard-stock{padding:1rem;}
        .dashboard-stock__header{gap:.9rem;}
        .dashboard-stock__identity{gap:.75rem;}
        .dashboard-stock__link{width:100%;}
        .dashboard-stock__totals,.dashboard-stock__totals.has-closing{grid-template-columns:minmax(0,1fr);gap:.75rem;}
        .dashboard-stock__total{padding:.85rem 1rem;}
        .dashboard-stock .bd-grid{grid-template-columns:minmax(0,1fr);gap:.5rem;}
        .dashboard-stock .bd-cell{flex-direction:row;align-items:center;gap:.75rem;padding:.75rem;}
        .dashboard-stock .bd-label{flex:1;min-width:0;}
        .dashboard-stock .bd-value{text-align:right;font-size:.85rem;flex:0 1 auto;max-width:58%;}
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

    @include('transaction-recap.panel', $bsUsageDashboard)

    @include('partials.bs-approval-link')
    {{-- Sisa Saldo BS --}}
    @php
        $bsService = app(\App\Services\BonSementaraService::class);
        $periodeAktif = $bsService->getActivePeriode();
        $sisaSaldoBs = $bsService->getRealtimeSisaSaldo(auth()->id(), $periodeAktif);
    @endphp
    <div class="d-flex align-items-center gap-3 p-3 mb-4" style="background:white;border:1px solid var(--border-color);border-radius:14px;">
        <div style="width:44px;height:44px;border-radius:10px;background:#fef2f2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
            <i class="bi bi-wallet2"></i>
        </div>
        <div>
            <div style="font-size:0.75rem;color:var(--text-secondary);font-weight:500;">Sisa Saldo BS Anda · Periode {{ $periodeAktif }}</div>
            <div style="font-size:1.25rem;font-weight:700;">Rp {{ number_format($sisaSaldoBs, 0, ',', '.') }}</div>
            <div style="font-size:0.72rem;color:var(--text-secondary);">Satu saldo untuk Operasional Sales dan Perjalanan Dinas.</div>
            @can('akses.cash-flow')
                <a href="{{ route('bs-balance.show', ['periode' => $periodeAktif]) }}" class="small">Rincian Saldo BS</a>
            @endcan
        </div>
    </div>

    {{-- Stok Kas gabungan BS dan event --}}
    @can('akses.cash-flow')
    @php
        Carbon\Carbon::setLocale('id');
        $currentPeriod = app(\App\Services\PeriodService::class)->current();
        $cashSnapshot = app(\App\Services\StockCashFlowService::class)
            ->recalculateSnapshot((int) auth()->id(), $currentPeriod);

        $saldoAkhir = $cashSnapshot->saldo_akhir;
        $showClosingBalance = $saldoAkhir !== null && \App\Models\Periode::where('periode', $currentPeriod)->where('status', 'CLOSED')->exists();
        $rpSigned = fn($v) => ($v < 0 ? '-' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
    @endphp

    <section class="dashboard-stock" aria-labelledby="dashboard-stock-title">
        <div class="dashboard-stock__header">
            <div class="dashboard-stock__identity">
                <span class="dashboard-stock__icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span>
                <div>
                    <h2 id="dashboard-stock-title" class="dashboard-stock__title">Stok Kas</h2>
                    <p class="dashboard-stock__period">{{ str_replace('-', '', $currentPeriod) }} &middot; {{ Carbon\Carbon::createFromFormat('!Y-m', $currentPeriod)->translatedFormat('F Y') }}</p>
                </div>
            </div>
            <a href="{{ route('cash-flow.index') }}" class="dashboard-stock__link">Lihat Detail <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>

        @if(!$cashSnapshot->opening_final)
            <p class="dashboard-stock__notice">Saldo awal menunggu penutupan periode sebelumnya.</p>
        @endif
        <p class="dashboard-stock__description">Gabungan BS dan event. Sisa Saldo BS dihitung terpisah.</p>
        @if($cashSnapshot->penyesuaian != 0)
            <p class="dashboard-stock__notice">Penyesuaian BS: {{ $rpSigned($cashSnapshot->penyesuaian) }}</p>
        @endif

        <div class="dashboard-stock__totals {{ $showClosingBalance ? 'has-closing' : '' }}">
            @if($showClosingBalance)
                <div class="dashboard-stock__total">
                    <div class="dashboard-stock__label"><i class="bi bi-cash-stack" aria-hidden="true"></i> Saldo Akhir</div>
                    <div class="dashboard-stock__amount {{ $saldoAkhir < 0 ? 'is-outgoing' : '' }}">{{ $rpSigned($saldoAkhir) }}</div>
                </div>
            @endif
            <div class="dashboard-stock__total">
                <div class="dashboard-stock__label"><i class="bi bi-box-arrow-in-down" aria-hidden="true"></i> Total Masuk</div>
                <div class="dashboard-stock__amount is-incoming">+Rp {{ number_format($cashSnapshot->total_masuk ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="dashboard-stock__total">
                <div class="dashboard-stock__label"><i class="bi bi-box-arrow-up" aria-hidden="true"></i> Total Keluar</div>
                <div class="dashboard-stock__amount is-outgoing">-Rp {{ number_format($cashSnapshot->total_keluar ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="dashboard-stock__breakdown">
            <h3 class="dashboard-stock__breakdown-title">Rincian Arus Kas</h3>
            @include('cash-flow.partials.expense-breakdown', ['snapshot' => $cashSnapshot])
        </div>
    </section>
    @endcan
@endsection
