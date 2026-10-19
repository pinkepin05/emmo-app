@php
    $panel = $panel ?? 'transaksi';
    $panelLabel = match ($panel) {
        'master' => 'Master Data',
        'profile' => 'Profil',
        'portal' => 'Portal',
        default => 'Transaksi',
    };
    $user = auth()->user();

    // Hitung badge dinamis
    $pendingAtasan = 0;
    $pendingFinance = 0;
    try {
        if ($panel !== 'master' && $user?->can('approval-atasan')) {
            $pendingAtasan = \App\Models\PengajuanBs::where('atasan_user_id', auth()->id())
                ->where('status', 'PENDING_ATASAN')->count();
        }
        if ($panel !== 'master' && $user?->can('akses.approval-finance')) {
            $pendingFinance = \App\Models\PengajuanBs::where('status', 'APPROVED_ATASAN')->count();
        }
    } catch (\Exception $e) {}

    // Nama user + inisial
    $nameParts = explode(' ', $user->nama ?? 'User');
    $initials = strtoupper(mb_substr($nameParts[0] ?? '', 0, 1) . mb_substr($nameParts[1] ?? '', 0, 1));
@endphp

<aside class="sidebar" id="appSidebar" aria-label="Menu navigasi EMMO" tabindex="-1">
    <div class="sidebar-header">
        <a href="{{ route($user->dashboardRouteName()) }}" class="sidebar-brand">
            <i class="bi bi-hexagon-fill"></i>
            EMMO
            <span style="font-weight:500;color:var(--text-secondary);font-size:0.8rem;">{{ $panelLabel }}</span>
        </a>
        <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Tutup menu navigasi" title="Tutup menu navigasi">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="sidebar-nav" aria-label="Navigasi utama">

        <div class="sidebar-section">
            <div class="sidebar-section-title">Menu Utama</div>
            @if($user->canAccessPortal())
            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
                <span>Dashboard Portal</span>
            </a>
            @endif
            <a href="{{ route('dashboard.transaksi') }}"
               class="sidebar-link {{ request()->routeIs('dashboard.transaksi', 'transaction-recap.*') ? 'active' : '' }}"
               @if(request()->routeIs('dashboard.transaksi', 'transaction-recap.*')) aria-current="page" @endif>
                <i class="bi bi-speedometer2" aria-hidden="true"></i>
                <span>Dashboard Transaksi</span>
            </a>
            @can('akses.master-data')
            <a href="{{ route('dashboard.master_data') }}"
               class="sidebar-link {{ request()->routeIs('dashboard.master_data') ? 'active' : '' }}"
               @if(request()->routeIs('dashboard.master_data')) aria-current="page" @endif>
                <i class="bi bi-database-gear" aria-hidden="true"></i>
                <span>Dashboard Master Data</span>
            </a>
            @endcan
            <a href="{{ route('profile.index') }}"
               class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
               @if(request()->routeIs('profile.*')) aria-current="page" @endif>
                <i class="bi bi-person-circle" aria-hidden="true"></i>
                <span>Profil Saya</span>
            </a>
        </div>

        {{-- ================== PANEL TRANSAKSI ================== --}}
        @if($panel !== 'master')

            {{-- SECTION: Bon Sementara --}}
            @canany(['akses.pengajuan-bs', 'approval-atasan', 'akses.approval-finance'])
            <div class="sidebar-section">
                <div class="sidebar-section-title">Bon Sementara</div>

                @can('akses.pengajuan-bs')
                    @include('components.sidebar-module', [
                        'label' => 'Pengajuan BS', 'icon' => 'bi-file-earmark-plus', 'routePrefix' => 'pengajuan-bs',
                        'createLabel' => 'Tambah Pengajuan', 'listLabel' => 'Daftar Pengajuan',
                    ])
                @endcan

                @can('approval-atasan')
                <a href="{{ route('approval-atasan.index') }}"
                   class="sidebar-link {{ request()->routeIs('approval-atasan.*') ? 'active' : '' }}">
                    <i class="bi bi-person-check"></i>
                    <span>Approval Atasan</span>
                    @if($pendingAtasan > 0)
                        <span class="sidebar-badge">{{ $pendingAtasan }}</span>
                    @endif
                </a>
                @endcan

                @can('akses.approval-finance')
                <a href="{{ route('approval-finance.index') }}"
                   class="sidebar-link {{ request()->routeIs('approval-finance.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>
                    <span>Approval Finance</span>
                    @if($pendingFinance > 0)
                        <span class="sidebar-badge">{{ $pendingFinance }}</span>
                    @endif
                </a>
                <a href="{{ route('bs-usage-checks.index') }}"
                   class="sidebar-link {{ request()->routeIs('bs-usage-checks.*') ? 'active' : '' }}" @if(request()->routeIs('bs-usage-checks.*')) aria-current="page" @endif>
                    <i class="bi bi-clipboard-check"></i><span>Pengecekan Pemakaian BS</span>
                </a>
                @endcan
            </div>
            @endcanany

            {{-- SECTION: Transaksi Operasional --}}
            @canany(['akses.transaksi-operasional', 'akses.transaksi-perjalanan-dinas', 'akses.event-dalam-kota', 'akses.event-luar-kota'])
            <div class="sidebar-section">
                <div class="sidebar-section-title">Transaksi</div>

                @can('akses.transaksi-operasional')
                    @include('components.sidebar-module', [
                        'label' => 'Operasional Sales', 'icon' => 'bi-truck', 'routePrefix' => 'transaksi_operasional',
                    ])
                @endcan

                @can('akses.transaksi-perjalanan-dinas')
                    @include('components.sidebar-module', [
                        'label' => 'Perjalanan Dinas', 'icon' => 'bi-airplane', 'routePrefix' => 'transaksi_perjalanan_dinas',
                    ])
                @endcan

                @can('akses.event-dalam-kota')
                    @include('components.sidebar-module', [
                        'label' => 'Event Dalam Kota', 'icon' => 'bi-geo-alt', 'routePrefix' => 'perongkosan_event_dalam_kota',
                    ])
                @endcan

                @can('akses.event-luar-kota')
                    @include('components.sidebar-module', [
                        'label' => 'Event Luar Kota', 'icon' => 'bi-globe2', 'routePrefix' => 'perongkosan_event_luar_kota',
                    ])
                @endcan
            </div>
            @endcanany

            @if(auth()->user()->can('akses.cash-flow') || (config('bs.report_menu_enabled', false) && auth()->user()->can('akses.laporan-bs')))
            <div class="sidebar-section">
                <div class="sidebar-section-title">Laporan</div>

                @if(config('bs.report_menu_enabled', false) && auth()->user()->can('akses.laporan-bs'))
                <a href="{{ route('laporan-bs.index') }}"
                   class="sidebar-link {{ request()->routeIs('laporan-bs.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Laporan BS</span>
                </a>
                @endif

                @can('akses.cash-flow')
                <a href="{{ route('cash-flow.index') }}"
                   class="sidebar-link {{ request()->routeIs('cash-flow.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Stok Kas</span>
                </a>
                @endcan

                @can('akses.approval-finance')
<a href="{{ route('monitoring-finance.index') }}"
   class="sidebar-link {{ request()->routeIs('monitoring-finance.*') ? 'active' : '' }}"
   @if(request()->routeIs('monitoring-finance.*')) aria-current="page" @endif>
    <i class="bi bi-building-check"></i>
    <span>Monitoring Finance</span>
</a>
@endcan
            </div>
            @endif

        @endif

        {{-- ================== PANEL MASTER DATA ================== --}}
        @if($panel === 'master')
            @can('akses.master-data')

            <div class="sidebar-section">
                <div class="sidebar-section-title">Organisasi</div>
                <a href="{{ route('roles.index') }}" class="sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i><span>Roles</span>
                </a>
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i><span>Users</span>
                </a>
                <a href="{{ route('companies.index') }}" class="sidebar-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i><span>Companies</span>
                </a>
                <a href="{{ route('levels.index') }}" class="sidebar-link {{ request()->routeIs('levels.*') ? 'active' : '' }}">
                    <i class="bi bi-layers"></i><span>Levels</span>
                </a>
                <a href="{{ route('positions.index') }}" class="sidebar-link {{ request()->routeIs('positions.*') ? 'active' : '' }}">
                    <i class="bi bi-person-workspace"></i><span>Positions</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Wilayah & Waktu</div>
                <a href="{{ route('wilayah.index') }}" class="sidebar-link {{ request()->routeIs('wilayah.*') ? 'active' : '' }}">
                    <i class="bi bi-map"></i><span>Wilayah</span>
                </a>
                <a href="{{ route('weekday_groups.index') }}" class="sidebar-link {{ request()->routeIs('weekday_groups.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-week"></i><span>Weekday Group</span>
                </a>
                <a href="{{ route('event_hour_groups.index') }}" class="sidebar-link {{ request()->routeIs('event_hour_groups.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i><span>Event Hour</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Biaya & Plafon</div>
                <a href="{{ route('cost_categories.index') }}" class="sidebar-link {{ request()->routeIs('cost_categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i><span>Cost Category</span>
                </a>
                <a href="{{ route('setting_plafon_perjalanan_dinas.index') }}" class="sidebar-link {{ request()->routeIs('setting_plafon_perjalanan_dinas.*') ? 'active' : '' }}">
                    <i class="bi bi-airplane-engines"></i><span>Plafon Perdin</span>
                </a>
                <a href="{{ route('plafon-event-dalam-kota.index') }}" class="sidebar-link {{ request()->routeIs('plafon-event-dalam-kota.*') ? 'active' : '' }}">
                    <i class="bi bi-geo"></i><span>Plafon Event Dalam</span>
                </a>
                <a href="{{ route('plafon-event-luar-kota.index') }}" class="sidebar-link {{ request()->routeIs('plafon-event-luar-kota.*') ? 'active' : '' }}">
                    <i class="bi bi-globe2"></i><span>Plafon Event Luar</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Pembukuan</div>
                <a href="{{ route('periode.index') }}" class="sidebar-link {{ request()->routeIs('periode.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-range"></i><span>Periode</span>
                </a>
                @can('akses.access-management')
                <a href="{{ route('access-management.index') }}" class="sidebar-link {{ request()->routeIs('access-management.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-wide-connected"></i><span>Access Mgmt</span>
                </a>
                @endcan
            </div>

            @endcan
        @endif

    </nav>

        {{-- FOOTER USER: avatar + nama = link ke halaman profil --}}
    <div class="sidebar-footer">
        <a href="{{ route('profile.index') }}" class="sidebar-user-link" title="Lihat Profil Saya">
            <div class="sidebar-avatar">{{ $initials }}</div>
            <div class="sidebar-user-info">
                <p class="sidebar-user-name">{{ $user->nama ?? 'User' }}</p>
                <p class="sidebar-user-role">{{ $user->role->nama ?? '-' }}</p>
            </div>
            <i class="bi bi-chevron-right link-hint"></i>
        </a>

        {{-- Tombol logout HARUS di luar <a> karena berupa form --}}
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="topbar-btn" style="padding:0.375rem;" title="Keluar" aria-label="Keluar dari akun">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
            </button>
        </form>
    </div>
</aside>

