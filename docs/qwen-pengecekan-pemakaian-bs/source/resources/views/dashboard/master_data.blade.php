@extends('layouts.app-with-sidebar', ['panel' => 'master'])

@section('title', 'Panel Master Data - EMMO')

@section('content')
    @php
        $breadcrumbs = [
            (auth()->user()->canAccessPortal() ? 'Portal' : 'Transaksi') => route(auth()->user()->dashboardRouteName()),
            'Panel Master Data' => route('dashboard.master_data'),
        ];
    @endphp

    {{-- Welcome Banner --}}
    <div class="mb-4 p-4" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:18px;color:white;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-40%;right:-5%;width:280px;height:280px;background:radial-gradient(circle,rgba(255,255,255,0.15),transparent 70%);border-radius:50%;"></div>
        <div style="position:relative;z-index:1;">
            <h2 style="font-weight:800;margin-bottom:0.25rem;">Panel Master Data</h2>
            <p style="opacity:0.9;margin:0;">Pusat konfigurasi fondasi, entitas, dan aturan bisnis aplikasi EMMO.</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="row g-3 mb-4">
        @php
            $masterStats = [
                ['label' => 'Users', 'value' => $counts['users'] ?? 0, 'icon' => 'bi-people-fill', 'color' => '#3b82f6', 'bg' => '#eff6ff'],
                ['label' => 'Roles', 'value' => $counts['roles'] ?? 0, 'icon' => 'bi-shield-lock', 'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
                ['label' => 'Companies', 'value' => $counts['companies'] ?? 0, 'icon' => 'bi-building', 'color' => '#10b981', 'bg' => '#ecfdf5'],
                ['label' => 'Wilayah', 'value' => $counts['wilayah'] ?? 0, 'icon' => 'bi-map', 'color' => '#f59e0b', 'bg' => '#fffbeb'],
            ];
        @endphp
        @foreach($masterStats as $s)
            <div class="col-6 col-md-3">
                <div class="p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:{{ $s['bg'] }};color:{{ $s['color'] }};display:flex;align-items:center;justify-content:center;margin-bottom:0.5rem;">
                        <i class="bi {{ $s['icon'] }}"></i>
                    </div>
                    <div style="font-size:0.75rem;color:var(--text-secondary);">{{ $s['label'] }}</div>
                    <div style="font-size:1.5rem;font-weight:800;">{{ $s['value'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Kategori 1: Organisasi --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-building" style="color:#3b82f6;"></i>
        <h5 style="font-weight:700;margin:0;">Entitas Organisasi & Jabatan</h5>
    </div>
    <div class="row g-3 mb-4">
        @php
            $orgModules = [
                ['label' => 'Roles', 'route' => 'roles.index', 'icon' => 'bi-shield-lock', 'count' => $counts['roles'] ?? 0, 'color' => '#6366f1', 'bg' => '#eef2ff'],
                ['label' => 'Users', 'route' => 'users.index', 'icon' => 'bi-people-fill', 'count' => $counts['users'] ?? 0, 'color' => '#f43f5e', 'bg' => '#fff1f2'],
                ['label' => 'Companies', 'route' => 'companies.index', 'icon' => 'bi-building', 'count' => $counts['companies'] ?? 0, 'color' => '#3b82f6', 'bg' => '#eff6ff'],
                ['label' => 'Levels', 'route' => 'levels.index', 'icon' => 'bi-layers', 'count' => $counts['levels'] ?? 0, 'color' => '#6366f1', 'bg' => '#eef2ff'],
                ['label' => 'Positions', 'route' => 'positions.index', 'icon' => 'bi-person-workspace', 'count' => $counts['positions'] ?? 0, 'color' => '#6366f1', 'bg' => '#eef2ff'],
                ['label' => 'Cost Category', 'route' => 'cost_categories.index', 'icon' => 'bi-tags', 'count' => $counts['cost_categories'] ?? 0, 'color' => '#f59e0b', 'bg' => '#fffbeb'],
                ['label' => 'Weekday Group', 'route' => 'weekday_groups.index', 'icon' => 'bi-calendar-week', 'count' => $counts['week_day_groups'] ?? 0, 'color' => '#06b6d4', 'bg' => '#ecfeff'],
            ];
        @endphp
        @foreach($orgModules as $mod)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route($mod['route']) }}" class="d-flex align-items-center gap-3 p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;text-decoration:none;transition:all 0.2s ease;height:100%;"
                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 16px -4px rgba(0,0,0,0.08)';this.style.borderColor='transparent';"
                   onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border-color)';">
                    <div style="width:48px;height:48px;border-radius:12px;background:{{ $mod['bg'] }};color:{{ $mod['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                        <i class="bi {{ $mod['icon'] }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:0.9375rem;color:var(--text-primary);margin-bottom:0.125rem;">{{ $mod['label'] }}</div>
                        <div style="font-size:0.75rem;color:var(--text-secondary);">{{ $mod['count'] }} data</div>
                    </div>
                    <i class="bi bi-chevron-right" style="color:var(--text-secondary);"></i>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Kategori 2: Wilayah & Waktu --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-geo-alt" style="color:#10b981;"></i>
        <h5 style="font-weight:700;margin:0;">Wilayah & Waktu</h5>
    </div>
    <div class="row g-3 mb-4">
        @php
            $geoModules = [
                ['label' => 'Wilayah', 'route' => 'wilayah.index', 'icon' => 'bi-map', 'count' => $counts['wilayah'] ?? 0, 'color' => '#10b981', 'bg' => '#ecfdf5'],
                ['label' => 'Event Hour Group', 'route' => 'event_hour_groups.index', 'icon' => 'bi-clock-history', 'count' => $counts['event_hours'] ?? 0, 'color' => '#10b981', 'bg' => '#ecfdf5'],
            ];
        @endphp
        @foreach($geoModules as $mod)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route($mod['route']) }}" class="d-flex align-items-center gap-3 p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;text-decoration:none;transition:all 0.2s ease;height:100%;"
                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 16px -4px rgba(0,0,0,0.08)';this.style.borderColor='transparent';"
                   onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border-color)';">
                    <div style="width:48px;height:48px;border-radius:12px;background:{{ $mod['bg'] }};color:{{ $mod['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                        <i class="bi {{ $mod['icon'] }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:0.9375rem;color:var(--text-primary);margin-bottom:0.125rem;">{{ $mod['label'] }}</div>
                        <div style="font-size:0.75rem;color:var(--text-secondary);">{{ $mod['count'] }} data</div>
                    </div>
                    <i class="bi bi-chevron-right" style="color:var(--text-secondary);"></i>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Kategori 3: Plafon Biaya --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-wallet2" style="color:#f59e0b;"></i>
        <h5 style="font-weight:700;margin:0;">Konfigurasi Plafon Biaya</h5>
    </div>
    <div class="row g-3 mb-4">
        @php
            $plafonModules = [
                ['label' => 'Plafon Perjalanan Dinas', 'route' => 'setting_plafon_perjalanan_dinas.index', 'icon' => 'bi-airplane-engines', 'count' => $counts['plafon_dinas'] ?? 0, 'color' => '#f59e0b', 'bg' => '#fffbeb'],
                ['label' => 'Plafon Event Dalam Kota', 'route' => 'plafon-event-dalam-kota.index', 'icon' => 'bi-geo', 'count' => $counts['plafon_dalam'] ?? 0, 'color' => '#f59e0b', 'bg' => '#fffbeb'],
                ['label' => 'Plafon Event Luar Kota', 'route' => 'plafon-event-luar-kota.index', 'icon' => 'bi-globe2', 'count' => $counts['plafon_luar'] ?? 0, 'color' => '#f59e0b', 'bg' => '#fffbeb'],
            ];
        @endphp
        @foreach($plafonModules as $mod)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route($mod['route']) }}" class="d-flex align-items-center gap-3 p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;text-decoration:none;transition:all 0.2s ease;height:100%;"
                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 16px -4px rgba(0,0,0,0.08)';this.style.borderColor='transparent';"
                   onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border-color)';">
                    <div style="width:48px;height:48px;border-radius:12px;background:{{ $mod['bg'] }};color:{{ $mod['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                        <i class="bi {{ $mod['icon'] }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:0.9375rem;color:var(--text-primary);margin-bottom:0.125rem;">{{ $mod['label'] }}</div>
                        <div style="font-size:0.75rem;color:var(--text-secondary);">{{ $mod['count'] }} aturan</div>
                    </div>
                    <i class="bi bi-chevron-right" style="color:var(--text-secondary);"></i>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Kategori 4: Periode & Pembukuan --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-calendar-range" style="color:#06b6d4;"></i>
        <h5 style="font-weight:700;margin:0;">Periode & Pembukuan</h5>
    </div>
    <div class="p-4 mb-4" style="background:white;border:1px solid var(--border-color);border-radius:14px;">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:10px;background:#ecfeff;color:#06b6d4;display:flex;align-items:center;justify-content:center;font-size:1.25rem;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:0.9375rem;color:var(--text-primary);">Periode Pembukuan & Tutup Buku</div>
                    <div style="font-size:0.75rem;color:var(--text-secondary);">Kontrol jendela waktu pembukuan</div>
                </div>
            </div>
            <a href="{{ route('periode.index') }}" style="background:var(--text-primary);color:white;padding:0.625rem 1.25rem;border-radius:10px;text-decoration:none;font-weight:600;font-size:0.875rem;display:inline-flex;align-items:center;gap:0.5rem;">
                Kelola Periode <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="table-responsive" style="background:var(--bg-body);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:white;">
                    <tr>
                        <th class="ps-4 py-3 small text-uppercase text-muted">Periode</th>
                        <th class="small text-uppercase text-muted">Status</th>
                        <th class="small text-uppercase text-muted">Ditutup Pada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $p)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $p->periode }}</td>
                        <td>
                            <span class="badge {{ $p->status === 'OPEN' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ \App\Support\AppTime::format($p->closed_at) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Belum ada periode.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Access Management --}}
    @can('akses.access-management')
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-gear-wide-connected" style="color:#64748b;"></i>
        <h5 style="font-weight:700;margin:0;">Administrasi Sistem</h5>
    </div>
    <div class="p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;">
        <a href="{{ route('access-management.index') }}" class="d-flex align-items-center gap-3" style="text-decoration:none;">
            <div style="width:48px;height:48px;border-radius:12px;background:#f1f5f9;color:#64748b;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:0.9375rem;color:var(--text-primary);margin-bottom:0.125rem;">Access Management</div>
                <div style="font-size:0.75rem;color:var(--text-secondary);">Atur hak akses menu per User Right Group</div>
            </div>
            <i class="bi bi-chevron-right" style="color:var(--text-secondary);"></i>
        </a>
    </div>
    @endcan
@endsection
