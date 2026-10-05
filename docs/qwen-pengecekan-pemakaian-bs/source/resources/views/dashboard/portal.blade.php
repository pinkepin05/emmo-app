@extends('layouts.app-with-sidebar', ['panel' => 'portal'])

@section('title', 'EMMO APP - Portal Utama')

@push('styles')
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --accent-primary: #3b82f6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-purple: #8b5cf6;
            --accent-pink: #ec4899;
            --accent-indigo: #6366f1;
            --accent-slate: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.04) 0px, transparent 50%);
        }

        /* Welcome Hero Card */
        .welcome-card {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: 20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-card .greeting {
            font-size: 0.875rem;
            opacity: 0.85;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .welcome-card h2 {
            font-weight: 800;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .welcome-card p {
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
            max-width: 600px;
        }

        .welcome-card .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .welcome-card .user-chip .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #4ade80;
            box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.3);
        }

        /* Section Title */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .section-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--text-secondary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 16px;
            background: var(--accent-primary);
            border-radius: 2px;
        }

        /* Portal Cards (besar) */
        .portal-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: flex;
            flex-direction: column;
        }

        .portal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-accent);
            opacity: 0.85;
        }

        .portal-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
            border-color: transparent;
        }

        .portal-icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background-color: var(--icon-bg);
            color: var(--card-accent);
            transition: transform 0.3s ease;
            margin-bottom: 1.25rem;
        }

        .portal-card:hover .portal-icon-wrapper {
            transform: scale(1.08) rotate(-3deg);
        }

        .portal-card-title {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .portal-card-desc {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .btn-portal-action {
            background-color: var(--text-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-portal-action:hover {
            background-color: #334155;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }

        /* Card Variants */
        .card-master { --card-accent: var(--accent-success); --icon-bg: #ecfdf5; }
        .card-transaksi { --card-accent: var(--accent-primary); --icon-bg: #eff6ff; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        @media (max-width: 575.98px) {
            .welcome-card { padding: 1.75rem; }
            .welcome-card h2 { font-size: 1.375rem; }
        }
    </style>
@endpush

@section('content')
    @php
        $breadcrumbs = ['Dashboard Portal' => route('dashboard')];
    @endphp
        
    {{-- ========================================= --}}
    {{-- WELCOME HERO CARD                        --}}
    {{-- ========================================= --}}
    @php
        $hour = \App\Support\AppTime::now()->hour;
        if ($hour < 11) $greeting = 'Selamat Pagi';
        elseif ($hour < 15) $greeting = 'Selamat Siang';
        elseif ($hour < 18) $greeting = 'Selamat Sore';
        else $greeting = 'Selamat Malam';
    @endphp

    <div class="welcome-card">
        <div class="greeting">{{ $greeting }},</div>
        <h2>{{ Auth::user()->nama }} 👋</h2>
        <p>Selamat datang kembali di Portal EMMO. Pantau pengeluaran, ajukan bon sementara, dan kelola approval Anda dengan mudah.</p>
        <div class="user-chip">
            <span class="dot"></span>
            <span>{{ Auth::user()->role->nama ?? 'Pengguna' }}</span>
            <span style="opacity:0.6;">·</span>
            <span style="opacity:0.85;">{{ Auth::user()->company->description ?? Auth::user()->company->code ?? '-' }}</span>
        </div>
    </div>

    {{-- ========================================= --}}
    {{-- PANEL UTAMA                               --}}
    {{-- ========================================= --}}
    <div class="mb-5">
        <div class="section-header">
            <h3 class="section-title">Panel Utama</h3>
        </div>

        <div class="row g-3">
            {{-- Panel Transaksi (semua user) --}}
            <div class="col-md-6">
                <a href="{{ route('dashboard.transaksi') }}" class="portal-card card-transaksi">
                    <div class="portal-icon-wrapper">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h3 class="portal-card-title">Panel Transaksi</h3>
                    <p class="portal-card-desc">
                        Pencatatan, pemantauan, dan pelaporan arus kas keluar harian secara real-time.
                    </p>
                    <div class="btn-portal-action">
                        Masuk Panel Transaksi <i class="bi bi-arrow-right"></i>
                    </div>
                </a>
            </div>

            {{-- Panel Master Data --}}
            @can('akses.master-data')
            <div class="col-md-6">
                <a href="{{ route('dashboard.master_data') }}" class="portal-card card-master">
                    <div class="portal-icon-wrapper">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3 class="portal-card-title">Panel Master Data</h3>
                    <p class="portal-card-desc">
                        Konfigurasi fondasi aplikasi, manajemen pengguna, dan pengaturan plafon biaya.
                    </p>
                    <div class="btn-portal-action">
                        Masuk Panel Master <i class="bi bi-arrow-right"></i>
                    </div>
                </a>
            </div>
            @endcan
        </div>
    </div>

@endsection