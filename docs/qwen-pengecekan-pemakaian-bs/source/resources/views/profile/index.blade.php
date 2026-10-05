@extends('layouts.app-with-sidebar', ['panel' => 'profile'])

@section('title', 'Profil Saya - EMMO App')

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
            --accent-purple: #8b5cf6;
            --accent-warning: #f59e0b;
            --accent-pink: #ec4899;
            --accent-indigo: #6366f1;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.04) 0px, transparent 50%);
        }

        /* Hero Profile Card */
        .profile-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: 20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .profile-hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .avatar {
            width: 88px;
            height: 88px;
            border-radius: 24px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            flex-shrink: 0;
            backdrop-filter: blur(10px);
        }

        .profile-hero-info h2 {
            font-weight: 800;
            font-size: 1.75rem;
            margin: 0 0 0.25rem 0;
            letter-spacing: -0.02em;
        }

        .profile-hero-info .username {
            opacity: 0.85;
            font-size: 0.9375rem;
            font-family: 'SF Mono', Monaco, monospace;
            margin-bottom: 0.75rem;
        }

        .profile-chips {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .chip {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Section Card */
        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            box-shadow: 0 8px 24px -8px rgba(0,0,0,0.06);
        }

        .info-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #fafbfc;
        }

        .info-card-header .icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .info-card-header .icon-box.bg-blue { background: #dbeafe; color: #2563eb; }
        .info-card-header .icon-box.bg-purple { background: #ede9fe; color: #7c3aed; }
        .info-card-header .icon-box.bg-green { background: #dcfce7; color: #16a34a; }
        .info-card-header .icon-box.bg-orange { background: #fed7aa; color: #ea580c; }

        .info-card-header h3 {
            font-size: 0.9375rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .info-card-header p {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .info-card-body {
            padding: 1.5rem;
        }

        /* Info Rows */
        .info-row {
            display: flex;
            align-items: flex-start;
            padding: 0.875rem 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .info-row:last-child { border-bottom: none; }

        .info-row .label {
            flex: 0 0 140px;
            font-size: 0.8125rem;
            color: var(--text-secondary);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-row .label i {
            font-size: 0.875rem;
            color: var(--text-secondary);
            opacity: 0.6;
        }

        .info-row .value {
            flex: 1;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .info-row .value.empty {
            color: var(--text-secondary);
            font-style: italic;
            font-weight: 400;
        }

        /* Badges */
        .badge-role {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .badge-active {
            background: #dcfce7;
            color: #16a34a;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Tunjangan Grid */
        .tunjangan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }

        .tunjangan-item {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s ease;
        }

        .tunjangan-item:hover {
            background: white;
            border-color: var(--accent-primary);
            transform: translateY(-2px);
        }

        .tunjangan-item .icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 1.125rem;
        }

        .tunjangan-item .label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .tunjangan-item .value {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .tunjangan-item .value.rupiah {
            color: var(--accent-success);
        }

        .tunjangan-item .value.empty {
            color: var(--text-secondary);
            font-weight: 400;
            font-style: italic;
            font-size: 0.8125rem;
        }

        /* Atasan Card */
        .atasan-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #fcd34d;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .atasan-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: white;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .atasan-card .info .label {
            font-size: 0.6875rem;
            color: #92400e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.125rem;
        }

        .atasan-card .info .name {
            font-size: 1rem;
            font-weight: 700;
            color: #78350f;
            margin: 0;
        }

        .atasan-empty {
            background: #f8fafc;
            border: 1px dashed var(--border-color);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Daftar bawahan */
        .profile-subordinates { scroll-margin-top: 5rem; }
        .profile-subordinates .info-card-header { flex-wrap: wrap; }
        .profile-subordinates__heading { flex: 1; min-width: 150px; }
        .profile-subordinates__count {
            padding: .35rem .7rem;
            border-radius: 8px;
            background: #ede9fe;
            color: #6d28d9;
            font-size: .75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .profile-subordinates__scroll { overflow-x: auto; }
        .profile-subordinates__table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
        .profile-subordinates__table th {
            padding: .85rem 1.25rem;
            background: #f8fafc;
            color: var(--text-secondary);
            font-size: .72rem;
            font-weight: 600;
            text-align: left;
        }
        .profile-subordinates__table td { padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); }
        .profile-subordinates__table td:first-child { font-weight: 600; }
        .profile-subordinates__table td:last-child { white-space: nowrap; }
        .profile-subordinates__table td span { overflow-wrap: anywhere; }
        .profile-subordinates__table tbody tr:hover { background: #fafbfc; }
        .profile-subordinates__link { display: inline-block; margin-top: .5rem; font-size: .75rem; font-weight: 600; }
        .profile-subordinates__empty { margin: 0; padding: 2rem 1.5rem; text-align: center; color: var(--text-secondary); font-size: .875rem; }
        @media (max-width: 767.98px) {
            .profile-subordinates__table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
            .profile-subordinates__table tbody, .profile-subordinates__table tr { display: block; }
            .profile-subordinates__table tr { padding: .75rem 1.25rem; border-top: 1px solid var(--border-color); }
            .profile-subordinates__table tr:first-child { border-top: 0; }
            .profile-subordinates__table td { display: flex; justify-content: space-between; gap: 1rem; padding: .4rem 0; border: 0; }
            .profile-subordinates__table td::before { content: attr(data-label); flex: 0 0 70px; color: var(--text-secondary); font-weight: 500; }
            .profile-subordinates__table td span { min-width: 0; text-align: right; }
        }

        /* Footer Action */
        .footer-action {
            text-align: center;
            padding: 1rem 0 2rem;
        }

        .btn-back {
            background: var(--text-primary);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9375rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #334155;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 575.98px) {
            .profile-hero { padding: 1.75rem; }
            .profile-hero-content { flex-direction: column; text-align: center; }
            .profile-chips { justify-content: center; }
            .info-row { flex-direction: column; gap: 0.25rem; }
            .info-row .label { flex: none; }
            .avatar { width: 72px; height: 72px; font-size: 1.75rem; }
        }
    </style>
@endpush

@section('content')
    @php
        $breadcrumbs = [
            (auth()->user()->canAccessPortal() ? 'Portal' : 'Transaksi') => route(auth()->user()->dashboardRouteName()),
            'Profil Saya' => route('profile.index'),
        ];
    @endphp

        {{-- ========================================= --}}
        {{-- HERO PROFILE CARD                         --}}
        {{-- ========================================= --}}
        @php
            $name = $user->nama ?? 'User';
            $parts = explode(' ', $name);
            $initials = mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1);
            $initials = strtoupper($initials);
            $isActive = (bool) $user->is_active;
        @endphp

        <div class="profile-hero">
            <div class="profile-hero-content">
                <div class="avatar">{{ $initials }}</div>
                <div class="profile-hero-info">
                    <h2>{{ $user->nama ?? 'Pengguna' }}</h2>
                    <div class="username">@ {{ $user->username ?? 'username' }}</div>
                    <div class="profile-chips">
                        <span class="chip">
                            <i class="bi bi-shield-check"></i>
                            {{ $user->role->nama ?? 'User' }}
                        </span>
                        @if($user->company)
                            <span class="chip">
                                <i class="bi bi-building"></i>
                                {{ $user->company->description ?? $user->company->code ?? '-' }}
                            </span>
                        @endif
                        @if($isActive)
                            <span class="chip" style="background: rgba(74,222,128,0.3);">
                                <i class="bi bi-check-circle-fill"></i>
                                Akun Aktif
                            </span>
                        @else
                            <span class="chip" style="background: rgba(239,68,68,0.3);">
                                <i class="bi bi-x-circle-fill"></i>
                                Nonaktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            {{-- ========================================= --}}
            {{-- KOLOM KIRI: Data Pribadi & Organisasi     --}}
            {{-- ========================================= --}}
            <div class="col-lg-7">
                
                {{-- Data Pribadi --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-blue">
                            <i class="bi bi-person-vcard"></i>
                        </div>
                        <div>
                            <h3>Data Pribadi</h3>
                            <p>Informasi identitas karyawan</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-credit-card-2-front"></i> NIK
                            </div>
                            <div class="value {{ !$user->NIK ? 'empty' : '' }}">
                                {{ $user->NIK ?? 'Belum diisi' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-person-fill"></i> Nama Lengkap
                            </div>
                            <div class="value">{{ $user->nama ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-at"></i> Username
                            </div>
                            <div class="value" style="font-family: 'SF Mono', Monaco, monospace;">
                                {{ $user->username ?? '-' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-toggle-on"></i> Status Akun
                            </div>
                            <div class="value">
                                @if($isActive)
                                    <span class="badge-active">Aktif</span>
                                @else
                                    <span class="badge-inactive">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Organisasi --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-purple">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <div>
                            <h3>Data Organisasi</h3>
                            <p>Posisi dalam struktur perusahaan</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-building"></i> Perusahaan
                            </div>
                            <div class="value {{ !$user->company ? 'empty' : '' }}">
                                {{ $user->company->description ?? $user->company->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-shield-check"></i> Hak Akses
                            </div>
                            <div class="value">
                                <span class="badge-role">{{ $user->role->nama ?? 'User Biasa' }}</span>
                                @if($user->role)
                                    <span class="text-secondary" style="font-size: 0.75rem; margin-left: 0.5rem;">
                                        ({{ $user->role->code }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-bar-chart-steps"></i> Level
                            </div>
                            <div class="value {{ !$user->level ? 'empty' : '' }}">
                                {{ $user->level->description ?? $user->level->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-briefcase"></i> Posisi
                            </div>
                            <div class="value {{ !$user->position ? 'empty' : '' }}">
                                {{ $user->position->description ?? $user->position->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-geo-alt"></i> Homebase
                            </div>
                            <div class="value {{ !$user->homebase ? 'empty' : '' }}">
                                {{ $user->homebase->nama ?? $user->homebase->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-calendar-week"></i> Hari Kerja
                            </div>
                            <div class="value {{ !$user->weekdayGroup ? 'empty' : '' }}">
                                {{ $user->weekdayGroup->description ?? $user->weekdayGroup->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================= --}}
            {{-- KOLOM KANAN: Atasan & Tunjangan           --}}
            {{-- ========================================= --}}
            <div class="col-lg-5">

                {{-- Atasan --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-orange">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div>
                            <h3>Atasan Langsung</h3>
                            <p>Penanggung jawab approval Anda</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        @if($user->atasan)
                            <div class="atasan-card">
                                <div class="icon">
                                    <i class="bi bi-person-check-fill"></i>
                                </div>
                                <div class="info">
                                    <div class="label">Atasan</div>
                                    <p class="name">{{ $user->atasan->nama }}</p>
                                    @if($user->atasan->position)
                                        <div style="font-size: 0.75rem; color: #92400e; margin-top: 0.125rem;">
                                            {{ $user->atasan->position->description ?? $user->atasan->position->code ?? '' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="atasan-empty">
                                <i class="bi bi-info-circle me-1"></i>
                                Belum ada atasan yang diatur
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tunjangan --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-green">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h3>Tunjangan Karyawan</h3>
                            <p>Komponen tunjangan yang Anda terima</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        <div class="tunjangan-grid">
                            <div class="tunjangan-item">
                                <div class="icon">
                                    <i class="bi bi-house-door"></i>
                                </div>
                                <div class="label">Tunjangan Kost</div>
                                @if($user->kost)
                                    <div class="value rupiah">
                                        Rp {{ number_format($user->kost, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div class="value empty">Tidak ada</div>
                                @endif
                            </div>
                            <div class="tunjangan-item">
                                <div class="icon" style="background: #fed7aa; color: #ea580c;">
                                    <i class="bi bi-fuel-pump"></i>
                                </div>
                                <div class="label">Tunjangan BBM</div>
                                @if($user->bbm)
                                    <div class="value rupiah">
                                        Rp {{ number_format($user->bbm, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div class="value empty">Tidak ada</div>
                                @endif
                            </div>
                            <div class="tunjangan-item">
                                <div class="icon" style="background: #fef3c7; color: #ca8a04;">
                                    <i class="bi bi-egg-fried"></i>
                                </div>
                                <div class="label">Uang Makan</div>
                                @if($user->uang_makan)
                                    <div class="value rupiah">
                                        Rp {{ number_format($user->uang_makan, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div class="value empty">Tidak ada</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-blue">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <h3>Aktivitas Saya</h3>
                            <p>Ringkasan aktivitas Anda</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        <div class="tunjangan-grid">
                            <div class="tunjangan-item">
                                <div class="icon">
                                    <i class="bi bi-file-earmark-plus"></i>
                                </div>
                                <div class="label">Pengajuan BS</div>
                                <div class="value">
                                    {{ $user->pengajuanBs()->count() }} data
                                </div>
                            </div>
                            <div class="tunjangan-item">
                                <div class="icon" style="background: #ede9fe; color: #7c3aed;">
                                    <i class="bi bi-person-check"></i>
                                </div>
                                <div class="label">Bawahan</div>
                                <div class="value">
                                    {{ $bawahan->count() }} orang
                                </div>
                                <a href="#daftar-bawahan" class="profile-subordinates__link">Lihat daftar bawahan <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <section class="info-card profile-subordinates" id="daftar-bawahan" aria-labelledby="bawahan-title">
            <div class="info-card-header">
                <div class="icon-box bg-purple"><i class="bi bi-people" aria-hidden="true"></i></div>
                <div class="profile-subordinates__heading">
                    <h3 id="bawahan-title">Bawahan Saya</h3>
                    <p>Pengguna yang terdaftar dengan Anda sebagai atasan langsung.</p>
                </div>
                <span class="profile-subordinates__count">{{ $bawahan->count() }} orang</span>
            </div>
            @if($bawahan->isEmpty())
                <p class="profile-subordinates__empty">Belum ada pengguna yang terdaftar sebagai bawahan Anda.</p>
            @else
                <div class="profile-subordinates__scroll">
                    <table class="profile-subordinates__table" aria-labelledby="bawahan-title">
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">NIK</th>
                                <th scope="col">Posisi</th>
                                <th scope="col">Homebase</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bawahan as $anggota)
                                <tr>
                                    <td data-label="Nama"><span>{{ $anggota->nama }}</span></td>
                                    <td data-label="NIK"><span>{{ $anggota->NIK ?: '-' }}</span></td>
                                    <td data-label="Posisi"><span>{{ $anggota->position->nama ?? $anggota->position->description ?? $anggota->position->code ?? 'Belum diatur' }}</span></td>
                                    <td data-label="Homebase"><span>{{ $anggota->homebase->nama ?? $anggota->homebase->code ?? 'Belum diatur' }}</span></td>
                                    <td data-label="Status"><span class="{{ $anggota->isActive() ? 'badge-active' : 'badge-inactive' }}">{{ $anggota->isActive() ? 'Aktif' : 'Nonaktif' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

@endsection
