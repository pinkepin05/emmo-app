# Kode existing: layout-menu-laporan-aset, bagian 1

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: public/js/application-time.js

SHA-256: `9415c2859cb62d90280a073ecd906b12d6ab6c201b68b02f0753d7807782ea4e`

~~~~javascript
(() => {
    'use strict';

    const clockSelector = '[data-application-live-time]';

    const startClock = (clock) => {
        const serverEpoch = Number(clock.dataset.serverEpoch);
        const timeZone = clock.dataset.timeZone;

        if (!Number.isFinite(serverEpoch) || !timeZone) {
            return;
        }

        const formatter = new Intl.DateTimeFormat('en-US', {
            timeZone,
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
        const startedAt = performance.now();

        const currentDate = () => new Date(serverEpoch + (performance.now() - startedAt));
        const dateElement = clock.querySelector('.application-live-time__date');
        const clockElement = clock.querySelector('.application-live-time__clock');

        const render = () => {
            const date = currentDate();
            const parts = {};

            formatter.formatToParts(date).forEach(({ type, value }) => {
                if (type !== 'literal') {
                    parts[type] = value;
                }
            });

            const day = Number(parts.day);
            const formattedDate = `${parts.month} - ${day} - ${parts.year}`;
            const formattedClock = `${parts.hour}:${parts.minute}:${parts.second} ${parts.dayPeriod.toLowerCase()}`;

            if (dateElement && clockElement) {
                dateElement.textContent = formattedDate;
                clockElement.textContent = formattedClock;
            } else {
                clock.textContent = `${formattedDate}, ${formattedClock}`;
            }

            clock.setAttribute('aria-label', `${formattedDate}, ${formattedClock}`);
            clock.dateTime = date.toISOString();
        };

        render();

        const millisecondsToNextSecond = 1000 - (currentDate().getTime() % 1000);
        window.setTimeout(() => {
            render();
            window.setInterval(render, 1000);
        }, millisecondsToNextSecond);
    };

    const initializeClocks = () => {
        document.querySelectorAll(clockSelector).forEach(startClock);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeClocks, { once: true });
    } else {
        initializeClocks();
    }
})();

~~~~

## FILE: public/js/bs-balance.js

SHA-256: `e37906eac6e781b8d42227acf64e1c0c0d8da70c621d1ea673687a4e94c44dae`

~~~~javascript
document.addEventListener('DOMContentLoaded', function () {
    const fields = document.getElementById('funding-bs-fields');
    if (!fields) return;
    const form = fields.closest('form');
    const date = form.querySelector('[name="tanggal"]');
    const owner = document.getElementById('bs_owner_id_select');
    const info = document.getElementById('bs-saldo-info');
    const display = document.getElementById('bs-balance-display');
    const opening = document.getElementById('bs-opening-info');
    let generation = 0;
    function changed() { document.dispatchEvent(new CustomEvent('bs-balance-updated')); }
    async function refreshBalance() {
        const label = form.querySelector('[data-bs-confirm-label]');
        if (label) label.textContent = owner && owner.value !== fields.dataset.inputter ? 'Simpan & Ajukan Pemakaian' : 'Simpan & Konfirmasi';
        const current = ++generation;
        info.dataset.balance = '';
        display.textContent = 'Memuat...';
        changed();
        const params = new URLSearchParams({module: fields.dataset.module, periode: (date?.value || fields.dataset.period).slice(0, 7)});
        if (fields.dataset.transaction) params.set('transaction_id', fields.dataset.transaction);
        try {
            const response = await fetch(fields.dataset.balanceUrl + '?' + params, {headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error('Saldo tidak dapat dimuat');
            const data = await response.json();
            if (current !== generation) return;
            const selected = String(owner?.value || fields.dataset.inputter);
            const balance = data.owners.find(item => String(item.id) === selected);
            if (!balance) throw new Error('Akses saldo tidak tersedia');
            info.dataset.balance = balance.balance;
            display.textContent = 'Rp ' + new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(balance.balance);
            opening.hidden = balance.opening_final;
        } catch (error) {
            if (current !== generation) return;
            display.textContent = 'Tidak tersedia. Saldo akan diperiksa saat Konfirmasi.';
        }
        changed();
    }
    date?.addEventListener('change', refreshBalance);
    owner?.addEventListener('change', refreshBalance);
    refreshBalance();
});

~~~~

## FILE: public/js/sidebar.js

SHA-256: `ae7abab6f7a624ccf78802df9faa586ebffe0f256c8020da9f48d59d90671510`

~~~~javascript
(() => {
    const sidebar = document.getElementById('appSidebar');
    const toggle = document.getElementById('menuToggle');
    const closeButton = document.getElementById('sidebarClose');
    const overlay = document.getElementById('sidebarOverlay');
    const main = document.querySelector('.main-content');
    const mobile = window.matchMedia('(max-width: 991.98px)');
    const desktopCollapsedClass = 'sidebar-desktop-collapsed';
    const storageKey = 'emmo.sidebar.collapsed';

    if (!sidebar || !toggle || !main) return;

    function updateToggle(expanded) {
        const label = expanded ? 'Tutup menu navigasi' : 'Buka menu navigasi';

        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
    }

    function isDesktopCollapsed() {
        return document.documentElement.classList.contains(desktopCollapsedClass);
    }

    function saveDesktopState(collapsed) {
        try {
            localStorage.setItem(storageKey, collapsed ? '1' : '0');
        } catch (_) {}
    }

    function setDesktopCollapsed(collapsed, persist = true) {
        document.documentElement.classList.toggle(desktopCollapsedClass, collapsed);
        sidebar.inert = collapsed;
        updateToggle(!collapsed);

        if (persist) saveDesktopState(collapsed);
    }

    function setMobileOpen(open, restoreFocus = true) {
        const isOpen = Boolean(open);

        sidebar.classList.toggle('is-open', isOpen);
        document.body.classList.toggle('sidebar-open', isOpen);
        updateToggle(isOpen);
        sidebar.inert = !isOpen;
        main.inert = isOpen;

        if (isOpen) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
            closeButton?.focus();
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');

            if (restoreFocus) toggle.focus();
        }
    }

    toggle.addEventListener('click', () => {
        if (mobile.matches) {
            setMobileOpen(!sidebar.classList.contains('is-open'));
            return;
        }

        setDesktopCollapsed(!isDesktopCollapsed());
    });
    closeButton?.addEventListener('click', () => setMobileOpen(false));
    overlay?.addEventListener('click', () => setMobileOpen(false));

    sidebar.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', () => {
            if (mobile.matches) setMobileOpen(false);
        });
    });

    document.addEventListener('keydown', event => {
        if (!mobile.matches || !sidebar.classList.contains('is-open')) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            setMobileOpen(false);
        }

        if (event.key === 'Tab') {
            const items = [...sidebar.querySelectorAll('a[href], button:not([disabled]), summary')]
                .filter(element => element.getClientRects().length > 0
                    && (element.matches('summary') || !element.closest('details:not([open])')));
            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });

    mobile.addEventListener('change', () => {
        const hadSidebarFocus = sidebar.contains(document.activeElement);
        sidebar.classList.remove('is-open');
        document.body.classList.remove('sidebar-open');
        main.inert = false;

        if (mobile.matches) {
            sidebar.inert = true;
            updateToggle(false);
            if (hadSidebarFocus) toggle.focus();
        } else {
            setDesktopCollapsed(isDesktopCollapsed(), false);
        }
    });

    window.addEventListener('pageshow', () => {
        if (mobile.matches) {
            setMobileOpen(false, false);
        } else {
            setDesktopCollapsed(isDesktopCollapsed(), false);
        }
    });

    if (mobile.matches) {
        setMobileOpen(false, false);
    } else {
        setDesktopCollapsed(isDesktopCollapsed(), false);
    }
})();

~~~~

## FILE: resources/views/admin/access-management.blade.php

SHA-256: `02c6fb6324d05c04dbb8bd73de4b9eb1097e70c4e0d4bc938bf2d56488dffad1`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Management - EMMO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            --accent-slate: #64748b;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            background-image: 
                radial-gradient(at 0% 0%, rgba(139, 92, 246, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.04) 0px, transparent 50%);
        }

        /* Navbar */
        .navbar-custom {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--text-primary) !important;
            letter-spacing: -0.025em;
            font-size: 1.25rem;
        }

        .navbar-brand i { color: var(--accent-primary); }

        .btn-nav {
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-nav-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            text-decoration: none;
        }

        .btn-nav-outline:hover {
            background-color: #f1f5f9;
            color: var(--text-primary);
            border-color: #cbd5e1;
        }

        /* Hero Header */
        .hero-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .hero-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-header .subtitle {
            font-size: 0.875rem;
            opacity: 0.85;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .hero-header h2 {
            font-weight: 800;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .hero-header p {
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
            max-width: 700px;
            font-size: 0.9375rem;
        }

        /* Stats */
        .stats-row {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
        }

        .stat-chip {
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(10px);
            padding: 0.625rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.2);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-chip .stat-value {
            background: rgba(255,255,255,0.95);
            color: var(--text-primary);
            padding: 0.125rem 0.5rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        /* Info Bar */
        .info-bar {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 0.875rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .info-bar i {
            color: var(--accent-primary);
            font-size: 1.125rem;
        }

        /* Role Card */
        .role-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .role-card:hover {
            box-shadow: 0 12px 32px -12px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }

        .role-card-header {
            padding: 1.25rem 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .role-card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .role-card-title {
            font-weight: 700;
            font-size: 1.125rem;
            margin: 0 0 0.125rem 0;
            position: relative;
            z-index: 1;
        }

        .role-card-code {
            font-size: 0.75rem;
            opacity: 0.85;
            font-weight: 500;
            font-family: 'SF Mono', Monaco, monospace;
            position: relative;
            z-index: 1;
        }

        .role-meta {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .role-meta-item {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.625rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(239, 68, 68, 0.25);
            color: white;
            padding: 0.25rem 0.625rem;
            border-radius: 8px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 2;
        }

        .status-badge.active {
            background: rgba(74, 222, 128, 0.25);
        }

        .role-card-body {
            padding: 1.25rem 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Select All Row */
        .select-all-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 0.875rem;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .select-all-row label {
            margin: 0;
            font-weight: 600;
            font-size: 0.8125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .select-all-row .counter {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Permission Group */
        .perm-group {
            margin-bottom: 1rem;
        }

        .perm-group-title {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            padding-left: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .perm-group-title i {
            color: var(--group-accent, var(--accent-primary));
            font-size: 0.75rem;
        }

        .perm-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.625rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: background 0.15s ease;
            cursor: pointer;
        }

        .perm-item:hover {
            background: #f8fafc;
        }

        .perm-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
            accent-color: var(--group-accent, var(--accent-primary));
        }

        .perm-item label {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-primary);
            cursor: pointer;
            flex: 1;
            font-weight: 500;
        }

        .perm-item.critical label::after {
            content: 'CRITICAL';
            font-size: 0.625rem;
            font-weight: 700;
            color: var(--accent-pink);
            margin-left: 0.5rem;
            padding: 0.125rem 0.375rem;
            background: #fdf2f8;
            border-radius: 4px;
            letter-spacing: 0.05em;
        }

        /* Group Accent Colors */
        .group-transaksi { --group-accent: var(--accent-primary); }
        .group-approval { --group-accent: var(--accent-purple); }
        .group-laporan { --group-accent: var(--accent-pink); }
        .group-admin { --group-accent: var(--accent-slate); }

        /* Save Button */
        .role-card-footer {
            padding: 1rem 1.5rem 1.5rem;
            border-top: 1px solid var(--border-color);
            background: #fafbfc;
        }

        .btn-save {
            width: 100%;
            background: var(--text-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-save:hover {
            background: #334155;
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-card);
            border: 1px dashed var(--border-color);
            border-radius: 18px;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
        }

        .custom-toast {
            background: var(--text-primary);
            color: white;
            padding: 0.875rem 1.25rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
            font-size: 0.875rem;
            font-weight: 500;
            min-width: 280px;
            animation: slideIn 0.3s ease;
        }

        .custom-toast i {
            color: var(--accent-success);
            font-size: 1.25rem;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Form Check Custom */
        .form-check-input {
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--group-accent, var(--accent-primary));
            border-color: var(--group-accent, var(--accent-primary));
        }

        @media (max-width: 575.98px) {
            .hero-header { padding: 1.75rem; }
            .hero-header h2 { font-size: 1.375rem; }
            .stats-row { gap: 0.5rem; }
            .stat-chip { padding: 0.5rem 0.75rem; font-size: 0.8125rem; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/dashboard') }}">
                <i class="bi bi-hexagon-fill me-2"></i> EMMO APP
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ url('/dashboard') }}" class="btn-nav btn-nav-outline">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">

        {{-- Hero Header --}}
        <div class="hero-header">
            <div class="subtitle"><i class="bi bi-shield-lock"></i> ADMINISTRATOR PANEL</div>
            <h2>Manajemen Hak Akses Fitur</h2>
            <p>
                Atur hak akses menu per <strong>User Right Group (Roles)</strong> sesuai struktur database EMMO.
                Setiap perubahan akan langsung berlaku untuk seluruh user yang tergabung dalam role tersebut.
            </p>
            <div class="stats-row">
                <div class="stat-chip">
                    <i class="bi bi-people-fill"></i>
                    Total Role
                    <span class="stat-value">{{ $roles->count() }}</span>
                </div>
                <div class="stat-chip">
                    <i class="bi bi-key-fill"></i>
                    Total Permission
                    <span class="stat-value">{{ $permissions->count() }}</span>
                </div>
                <div class="stat-chip">
                    <i class="bi bi-person-check-fill"></i>
                    Role Aktif
                    <span class="stat-value">{{ $roles->filter(fn($r) => ($masterActive[$r->name] ?? 1))->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Info Bar --}}
        <div class="info-bar">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>Tips:</strong> Perubahan hak akses akan langsung berlaku untuk user. Permission bertanda <span style="color: var(--accent-pink); font-weight: 700;">CRITICAL</span> sebaiknya hanya diberikan ke role Administrator.
            </div>
        </div>

        {{-- Success Toast --}}
        @if(session('success'))
        <div class="toast-container">
            <div class="custom-toast">
                <i class="bi bi-check-circle-fill"></i>
                <div>{!! session('success') !!}</div>
            </div>
        </div>
        @endif

        {{-- Grid Role Cards --}}
        @if($roles->isEmpty())
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>Belum ada role</h5>
                <p class="text-muted mb-0">Tambahkan role terlebih dahulu di Master Data → Roles.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($roles as $role)
                    @php
                        $isActive = $masterActive[$role->name] ?? 1;
                        $userCount = $userCounts[$role->name] ?? 0;
                        $permCount = $role->permissions->count();
                        $totalPerm = $permissions->count();
                    @endphp

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="role-card">
                            {{-- Header --}}
                            <div class="role-card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                @if($isActive)
                                    <span class="status-badge active">Aktif</span>
                                @else
                                    <span class="status-badge">Nonaktif</span>
                                @endif

                                <h3 class="role-card-title">
                                    {{ $masterNames[$role->name] ?? $role->name }}
                                </h3>
                                <div class="role-card-code">{{ $role->name }}</div>

                                <div class="role-meta">
                                    <div class="role-meta-item">
                                        <i class="bi bi-people-fill"></i>
                                        {{ $userCount }} user
                                    </div>
                                    <div class="role-meta-item">
                                        <i class="bi bi-key-fill"></i>
                                        {{ $permCount }}/{{ $totalPerm }}
                                    </div>
                                </div>
                            </div>

                            {{-- Body --}}
                            <form action="{{ route('access-management.update', $role->id) }}" method="POST" class="role-card-form">
                                @csrf
                                @method('PUT')

                                <div class="role-card-body">

                                    {{-- Select All Row --}}
                                    <div class="select-all-row">
                                        <label for="selectAll_{{ $role->id }}">
                                            <input class="form-check-input" type="checkbox"
                                                   id="selectAll_{{ $role->id }}"
                                                   onchange="toggleAll(this, '{{ $role->id }}')">
                                            Pilih Semua
                                        </label>
                                        <span class="counter" id="counter_{{ $role->id }}">
                                            {{ $permCount }}/{{ $totalPerm }}
                                        </span>
                                    </div>

                                    {{-- GROUP: Transaksi --}}
                                    @php
                                        $groups = [
                                            'transaksi' => [
                                                'title' => 'Transaksi',
                                                'icon' => 'bi-graph-up-arrow',
                                                'class' => 'group-transaksi',
                                                'keywords' => ['pengajuan', 'transaksi', 'event'],
                                            ],
                                            'approval' => [
                                                'title' => 'Approval',
                                                'icon' => 'bi-person-check',
                                                'class' => 'group-approval',
                                                'keywords' => ['approval'],
                                            ],
                                            'laporan' => [
                                                'title' => 'Laporan & Cash Flow',
                                                'icon' => 'bi-file-earmark-text',
                                                'class' => 'group-laporan',
                                                'keywords' => ['laporan', 'cash'],
                                            ],
                                            'admin' => [
                                                'title' => 'Administrasi',
                                                'icon' => 'bi-gear',
                                                'class' => 'group-admin',
                                                'keywords' => ['master', 'access'],
                                            ],
                                        ];
                                    @endphp

                                    @foreach($groups as $groupKey => $group)
                                        @php
                                            $groupPerms = $permissions->filter(function($p) use ($group) {
                                                foreach ($group['keywords'] as $keyword) {
                                                    if (str_contains($p->name, $keyword)) return true;
                                                }
                                                return false;
                                            });
                                        @endphp

                                        @if($groupPerms->isNotEmpty())
                                        <div class="perm-group {{ $group['class'] }}">
                                            <div class="perm-group-title">
                                                <i class="bi {{ $group['icon'] }}"></i>
                                                {{ $group['title'] }}
                                            </div>

                                            @foreach($groupPerms as $permission)
                                                @php
                                                    $label = str_replace(['akses.', '-', '_', '.'], ['', ' ', ' ', ' '], $permission->name);
                                                    $label = ucwords($label);
                                                    $isCritical = str_contains($permission->name, 'access-management') || str_contains($permission->name, 'master-data');
                                                @endphp
                                                <div class="perm-item {{ $isCritical ? 'critical' : '' }}">
                                                    <input class="form-check-input permission-checkbox"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $permission->name }}"
                                                           id="perm_{{ $role->id }}_{{ $permission->id }}"
                                                           data-role="{{ $role->id }}"
                                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                    <label for="perm_{{ $role->id }}_{{ $permission->id }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    @endforeach

                                </div>

                                <div class="role-card-footer">
                                    <button type="submit" class="btn-save">
                                        <i class="bi bi-check2-circle"></i>
                                        Simpan Hak Akses
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleAll(checkbox, roleId) {
            document.querySelectorAll('.permission-checkbox[data-role="' + roleId + '"]').forEach(function(cb) {
                cb.checked = checkbox.checked;
            });
            updateCounter(roleId);
        }

        function updateCounter(roleId) {
            var all = document.querySelectorAll('.permission-checkbox[data-role="' + roleId + '"]');
            var checked = document.querySelectorAll('.permission-checkbox[data-role="' + roleId + '"]:checked');
            var counter = document.getElementById('counter_' + roleId);
            var selectAll = document.getElementById('selectAll_' + roleId);

            if (counter) counter.textContent = checked.length + '/' + all.length;
            if (selectAll) {
                selectAll.checked = checked.length === all.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
            }
        }

        document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateCounter(this.dataset.role);
            });
        });

        // Init counters on page load
        document.querySelectorAll('.permission-checkbox').forEach(function(cb) {
            var roleId = cb.dataset.role;
            if (!document.querySelector('[data-init="' + roleId + '"]')) {
                updateCounter(roleId);
                var marker = document.createElement('span');
                marker.dataset.init = roleId;
                document.body.appendChild(marker);
            }
        });

        // Auto-hide toast after 4s
        setTimeout(function() {
            var toast = document.querySelector('.custom-toast');
            if (toast) {
                toast.style.transition = 'opacity 0.3s ease';
                toast.style.opacity = '0';
                setTimeout(function() { toast.remove(); }, 300);
            }
        }, 4000);
    </script>
</body>
</html>

~~~~

## FILE: resources/views/bs-balance/show.blade.php

SHA-256: `00d14b516844c68367569fd456efd6b098cbb358a45ed6d55a4a3c25ea1f865e`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Saldo BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @include('partials.application-time')
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
            <h1 class="h3 mb-0">Rincian Saldo BS</h1>
            <a href="{{ route('dashboard.transaksi') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
        <p>Seluruh pencairan menambah satu saldo BS. Realisasi Operasional Sales dan Perjalanan Dinas, termasuk pemakaian oleh anggota grup, mengurangi saldo pemilik dana.</p>
        @include('partials.bs-approval-link')
        <form method="GET" class="d-flex gap-2 mb-4">
            <label for="periode" class="col-form-label">Periode</label>
            <select name="periode" id="periode" class="form-select w-auto">
                @foreach($periodOptions as $option)<option value="{{ $option }}" @selected($option === $periode)>{{ $option }}</option>@endforeach
            </select>
            <button class="btn btn-primary">Tampilkan</button>
        </form>
        @if(!$snapshot->opening_final)<div class="alert alert-warning">Saldo awal menunggu penutupan periode sebelumnya.</div>@endif
        <div class="card border-0 shadow-sm mb-4"><div class="card-body row g-3">
            @foreach(['Saldo Awal' => $snapshot->saldo_awal, 'Total Pencairan' => $snapshot->total_masuk, 'Total Pemakaian' => $snapshot->total_keluar, 'Sisa Saldo BS' => $available] as $label => $amount)
                <div class="col-sm-6 col-lg-3"><div class="text-muted">{{ $label }}</div><strong>Rp {{ number_format($amount, 2, ',', '.') }}</strong></div>
            @endforeach
        </div></div>
        <div class="card border-0 shadow-sm"><div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Tanggal</th><th>Aktivitas</th><th>Dokumen</th><th class="text-end">Masuk</th><th class="text-end">Keluar</th><th class="text-end">Saldo</th></tr></thead>
                <tbody>
                    <tr><td colspan="5">Saldo awal</td><td class="text-end text-nowrap">Rp {{ number_format($snapshot->saldo_awal, 2, ',', '.') }}</td></tr>
                    @forelse($rows as $row)
                        <tr>
                            <td class="text-nowrap">{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d/m/Y') : 'Tanggal tidak tercatat' }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td><a href="{{ $row['url'] }}">{{ $row['description'] }}</a></td>
                            @foreach(['incoming', 'outgoing', 'balance'] as $column)<td class="text-end text-nowrap">Rp {{ number_format($row[$column], 2, ',', '.') }}</td>@endforeach
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada mutasi saldo BS pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
        <p class="text-muted small mt-3">Draft dan permintaan yang menunggu persetujuan belum mengurangi saldo. Nomor pengajuan menunjukkan asal pencairan; nomor transaksi menunjukkan pemakaian.</p>
    </main>
</body>
</html>

~~~~

## FILE: resources/views/components/sidebar-module.blade.php

SHA-256: `647e3064fb6b726151ce11d60a9de162170e7b043023fef52fc5907facb56176`

~~~~php
@php
    $moduleActive = request()->routeIs($routePrefix . '.*');
    $creating = request()->routeIs($routePrefix . '.create');
    $listing = request()->routeIs($routePrefix . '.index');
@endphp

<details class="sidebar-module" @if($moduleActive) open @endif>
    <summary class="sidebar-link sidebar-module-toggle {{ $moduleActive ? 'is-current' : '' }}">
        <i class="bi {{ $icon }}" aria-hidden="true"></i>
        <span>{{ $label }}</span>
        <i class="bi bi-chevron-down sidebar-module-chevron" aria-hidden="true"></i>
    </summary>
    <div class="sidebar-submenu">
        <a href="{{ route($routePrefix . '.create') }}"
           class="sidebar-link {{ $creating ? 'active' : '' }}"
           @if($creating) aria-current="page" @endif>
            <i class="bi bi-plus-circle" aria-hidden="true"></i>
            <span>{{ $createLabel ?? 'Tambah Transaksi' }}<span class="visually-hidden"> — {{ $label }}</span></span>
        </a>
        <a href="{{ route($routePrefix . '.index') }}"
           class="sidebar-link {{ $moduleActive && !$creating ? 'active' : '' }}"
           @if($listing) aria-current="page" @endif>
            <i class="bi bi-list-ul" aria-hidden="true"></i>
            <span>{{ $listLabel ?? 'Daftar Transaksi' }}<span class="visually-hidden"> — {{ $label }}</span></span>
        </a>
    </div>
</details>

~~~~

## FILE: resources/views/components/sidebar.blade.php

SHA-256: `9315d763dbcafebe737ce3b4cc0a0b4b6cc3eb039c382649ab12d4a4b71de04c`

~~~~php
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
               class="sidebar-link {{ request()->routeIs('dashboard.transaksi') ? 'active' : '' }}"
               @if(request()->routeIs('dashboard.transaksi')) aria-current="page" @endif>
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

            {{-- SECTION: Laporan --}}
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


~~~~

## FILE: resources/views/dashboard/master_data.blade.php

SHA-256: `2e712bd77dc8343c2f31c6815f274b173e214f738ffc647241d1189c4e252d66`

~~~~php
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

~~~~

## FILE: resources/views/dashboard/portal.blade.php

SHA-256: `6eb89a2778ce0375a5a1ea4037a4f657f9317420498c403f0baef8eee8ad0f6d`

~~~~php
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
~~~~

## FILE: resources/views/dashboard/transaksi.blade.php

SHA-256: `2091baf0f0fedc0726a72bdeab9467424bba9fcbde5d84fc6c0ebe6162002b41`

~~~~php
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

    {{-- Statistics Overview --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#eff6ff;color:#3b82f6;display:flex;align-items:center;justify-content:center;font-size:1.125rem;margin-bottom:0.75rem;">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div style="font-size:0.75rem;color:var(--text-secondary);font-weight:500;margin-bottom:0.25rem;">Total Transaksi</div>
                <div style="font-size:1.25rem;font-weight:700;color:var(--text-primary);">{{ $totalTransaksi ?? '0' }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;font-size:1.125rem;margin-bottom:0.75rem;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div style="font-size:0.75rem;color:var(--text-secondary);font-weight:500;margin-bottom:0.25rem;">Total Pengeluaran</div>
                <div style="font-size:1.125rem;font-weight:700;color:var(--text-primary);">Rp {{ number_format($totalNominal ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#ecfeff;color:#06b6d4;display:flex;align-items:center;justify-content:center;font-size:1.125rem;margin-bottom:0.75rem;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div style="font-size:0.75rem;color:var(--text-secondary);font-weight:500;margin-bottom:0.25rem;">Transaksi Bulan Ini</div>
                <div style="font-size:1.25rem;font-weight:700;color:var(--text-primary);">{{ $transaksiBulanIni ?? '0' }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-3" style="background:white;border:1px solid var(--border-color);border-radius:14px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#fef2f2;color:#ef4444;display:flex;align-items:center;justify-content:center;font-size:1.125rem;margin-bottom:0.75rem;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div style="font-size:0.75rem;color:var(--text-secondary);font-weight:500;margin-bottom:0.25rem;">Over Plafon</div>
                <div style="font-size:1.25rem;font-weight:700;color:var(--text-primary);">{{ $overPlafon ?? 0 }}</div>
            </div>
        </div>
    </div>

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

~~~~

## FILE: resources/views/laporan-bs/index.blade.php

SHA-256: `14ea8672465c7f085b8d317e615d823081c3df99cbe97519d058f68497ec972e`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengajuan BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body:#f8fafc; --border-color:#e2e8f0; --text-primary:#0f172a; --text-secondary:#64748b; }
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg-body);color:var(--text-primary);}
        .card-custom{background:#fff;border:1px solid var(--border-color);border-radius:16px;overflow:hidden;}
        .table-custom th{background:#f8fafc;color:var(--text-secondary);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;padding:.9rem 1rem;border-bottom:1px solid var(--border-color);}
        .table-custom td{padding:.9rem 1rem;border-bottom:1px solid var(--border-color);font-size:.92rem;vertical-align:middle;}
        .btn-nav{background:#fff;border:1px solid var(--border-color);color:var(--text-secondary);padding:.5rem 1rem;border-radius:8px;text-decoration:none;font-size:.875rem;}
        .badge-status{padding:.4em .8em;border-radius:6px;font-weight:600;font-size:.72rem;}
        .btn-settle{background:#0f172a;color:#fff;border:none;border-radius:8px;padding:.45rem .9rem;font-weight:600;font-size:.8rem;}
        .btn-settle:hover{background:#334155;color:#fff;}
        .angka-positif{color:#dc2626;font-weight:700;}
        .angka-negatif{color:#2563eb;font-weight:700;}
        .angka-nol{color:#059669;font-weight:700;}
    </style>
</head>
<body class="py-5">
    @include('partials.application-time')
<div class="container" style="max-width:1250px;">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="fw-bold mb-0">📊 Laporan Pengajuan BS</h3>
        <a href="{{ route('approval-finance.index') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    @if(session('success'))<div class="alert alert-success border-0 shadow-sm" style="border-radius:12px;">✅ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger border-0 shadow-sm" style="border-radius:12px;"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="alert alert-info">
            Semua pencairan masuk ke saldo BS gabungan. Pemakaian dicatat pada Rincian Saldo BS. Riwayat penyelesaian lama tetap ditampilkan.
        </div>

    {{-- Filter + Export --}}
    <div class="card-custom p-3 mb-4">
        <form method="GET" action="{{ route('laporan-bs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Periode</label>
                <select name="periode" class="form-select form-select-sm">
                    <option value="">Semua Periode</option>
                    @foreach(\App\Models\Periode::orderBy('periode','desc')->get() as $p)
                        <option value="{{ $p->periode }}" {{ request('periode') == $p->periode ? 'selected' : '' }}>{{ $p->periode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(['PENDING_ATASAN', 'APPROVED_ATASAN', 'REJECTED_ATASAN', 'REJECTED_FINANCE'] as $option)
                        <option value="{{ $option }}" @selected(request('status') === $option)>{{ str_replace('_', ' ', $option) }}</option>
                    @endforeach
                    <option value="DISBURSED" {{ request('status') == 'DISBURSED' ? 'selected' : '' }}>DISBURSED (Dicairkan)</option>
                    <option value="SETTLED" {{ request('status') == 'SETTLED' ? 'selected' : '' }}>SETTLED (Riwayat)</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-sm btn-dark fw-semibold" style="border-radius:8px;"><i class="bi bi-funnel me-1"></i> Terapkan</button>
                <a href="{{ route('laporan-bs.export', request()->query()) }}" class="btn btn-sm btn-success fw-semibold" style="border-radius:8px;"><i class="bi bi-download me-1"></i> Export</a>
            </div>
        </form>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom mb-0 w-100">
                <thead><tr>
                    <th>Doc No</th><th>User</th><th>Periode</th><th>Jenis Pengajuan</th>
                    <th class="text-end">Nominal Pengajuan</th><th class="text-end">Nominal Cair</th>
                    <th>Riwayat Penyelesaian</th><th>Status</th>
                </tr></thead>
                <tbody>
                @forelse($data as $bs)
                    <tr>
                        <td class="fw-semibold"><a href="{{ route('pengajuan-bs.show', $bs) }}">{{ $bs->document_no }}</a></td>
                        <td>{{ $bs->user->nama ?? '-' }}</td><td>{{ $bs->periode }}</td><td>{{ $bs->tipe_label }}</td>
                        <td class="text-end">Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($bs->nominal_approved, 0, ',', '.') }}</td>
                        <td>
                            @if($bs->status === 'SETTLED' && $bs->nominal_pengembalian > 0)
                                User setor <strong>Rp {{ number_format($bs->nominal_pengembalian, 0, ',', '.') }}</strong>
                            @elseif($bs->status === 'SETTLED' && $bs->nominal_reimburse > 0)
                                Reimburse <strong>Rp {{ number_format($bs->nominal_reimburse, 0, ',', '.') }}</strong>
                            @elseif($bs->status === 'SETTLED')
                                Lunas tanpa selisih
                            @else
                                {{ $bs->status === 'DISBURSED' ? 'Dana masuk ke saldo BS gabungan' : '-' }}
                            @endif
                        </td>
                        <td>{{ str_replace('_', ' ', $bs->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada pengajuan BS.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>

~~~~

## FILE: resources/views/layouts/app-with-sidebar.blade.php

SHA-256: `09e43506029a187040566c89a722df2e2fd1d1ea961d34bf151dd2c165a2eea9`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EMMO App')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (() => {
            try {
                if (localStorage.getItem('emmo.sidebar.collapsed') === '1') {
                    document.documentElement.classList.add('sidebar-desktop-collapsed');
                }
            } catch (_) {}
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 272px;
            --topbar-height: 64px;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-sidebar: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --accent-primary: #3b82f6;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            margin: 0;
            min-height: 100vh;
        }

        /* ================= LAYOUT GRID ================= */
        .app-layout {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            min-height: 100vh;
            transition: grid-template-columns 0.25s ease;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            z-index: 100;
            min-width: 0;
            opacity: 1;
            transform: translateX(0);
            transition: transform 0.25s ease, opacity 0.2s ease, visibility 0.25s ease;
        }

        .sidebar-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-weight: 800;
            font-size: 1.125rem;
            color: var(--text-primary);
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .sidebar-brand i { color: var(--accent-primary); font-size: 1.5rem; }

        .sidebar-close {
            display: none;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-secondary);
            font-size: 1.125rem;
            cursor: pointer;
            padding: 0.45rem;
            line-height: 1;
            transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }

        .sidebar-close:hover {
            color: var(--accent-primary);
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .sidebar-nav {
            padding: 1rem 0.75rem;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .sidebar-section { margin-bottom: 1.25rem; }

        .sidebar-section-title {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-secondary);
            padding: 0.5rem 0.75rem 0.5rem;
            margin: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 2px;
            transition: all 0.15s ease;
            position: relative;
        }

        .sidebar-link i {
            font-size: 1.125rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-link:hover {
            background: #f1f5f9;
            color: var(--text-primary);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, #eff6ff 0%, #eef2ff 100%);
            color: var(--text-primary);
            font-weight: 700;
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: -0.75rem;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: var(--accent-primary);
            border-radius: 0 3px 3px 0;
        }

        .sidebar-link.active i { color: var(--accent-primary); }

        .sidebar-module { margin-bottom: 2px; }
        .sidebar-module-toggle { cursor: pointer; list-style: none; }
        .sidebar-module-toggle::-webkit-details-marker { display: none; }
        .sidebar-module-toggle > span { flex: 1; min-width: 0; }
        .sidebar-module-toggle.is-current { color: var(--accent-primary); font-weight: 700; }
        .sidebar-module-toggle .sidebar-module-chevron { width: 14px; font-size: .75rem; transition: transform .15s ease; }
        .sidebar-module[open] > .sidebar-module-toggle .sidebar-module-chevron { transform: rotate(180deg); }
        .sidebar-submenu { margin: .15rem 0 .6rem 1.35rem; padding-left: .6rem; border-left: 1px solid var(--border-color); }
        .sidebar-submenu .sidebar-link { padding: .6rem .65rem; font-size: .8rem; gap: .55rem; min-height: 40px; }
        .sidebar-submenu .sidebar-link i { width: 16px; font-size: .95rem; }
        .sidebar-submenu .sidebar-link.active::before { left: -.65rem; }

        .sidebar a:focus-visible,
        .sidebar button:focus-visible,
        .sidebar summary:focus-visible,
        .topbar a:focus-visible,
        .topbar button:focus-visible {
            outline: 2px solid var(--accent-primary);
            outline-offset: 3px;
        }

        .sidebar-badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.125rem 0.5rem;
            border-radius: 50px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .sidebar-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name {
            font-weight: 700;
            font-size: 0.875rem;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
        }
        .sidebar-user-role {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

                /* Link profil di footer sidebar */
        .sidebar-user-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            min-width: 0;
            text-decoration: none;
            border-radius: 12px;
            padding: 0.375rem;
            margin: -0.375rem;
            transition: background 0.15s ease;
        }

        .sidebar-user-link:hover {
            background: #f1f5f9;
        }

        .sidebar-user-link:hover .sidebar-avatar {
            transform: scale(1.06);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }

        .sidebar-user-link:hover .sidebar-user-name {
            color: var(--accent-primary);
        }

        .sidebar-avatar {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sidebar-user-name {
            transition: color 0.15s ease;
        }

        /* Ikon panah kecil penanda "bisa diklik" */
        .sidebar-user-link .link-hint {
            margin-left: auto;
            color: var(--text-secondary);
            font-size: 0.875rem;
            opacity: 0;
            transform: translateX(-4px);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .sidebar-user-link:hover .link-hint {
            opacity: 1;
            transform: translateX(0);
        }

        /* ================= MAIN CONTENT ================= */
        .main-content {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            height: var(--topbar-height);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .topbar-menu-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.5rem;
            color: var(--text-primary);
            font-size: 1.125rem;
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }

        .topbar-menu-toggle:hover {
            color: var(--accent-primary);
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .topbar-breadcrumb {
            font-size: 0.875rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
        }

        .topbar-breadcrumb .current {
            color: var(--text-primary);
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .topbar-actions .notif-wrap {
            position: relative;
            top: auto;
            right: auto;
            z-index: auto;
        }

        .topbar-actions .notif-panel {
            width: min(350px, calc(100vw - 2rem));
        }

        .topbar-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .topbar-btn:hover {
            background: #f1f5f9;
            color: var(--text-primary);
            border-color: #cbd5e1;
        }

        .content-wrapper {
            padding: 2rem;
            flex: 1;
        }

        /* Overlay mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 99;
            backdrop-filter: blur(2px);
        }

        /* ================= RESPONSIVE ================= */
        @media (min-width: 992px) {
            html.sidebar-desktop-collapsed .app-layout {
                grid-template-columns: 0 1fr;
            }

            html.sidebar-desktop-collapsed .sidebar {
                visibility: hidden;
                opacity: 0;
                transform: translateX(-100%);
                pointer-events: none;
            }
        }

        @media (max-width: 991.98px) {
            body.sidebar-open { overflow: hidden; }

            .app-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: var(--sidebar-width);
                max-width: calc(100vw - 2rem);
                visibility: hidden;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }

            .sidebar.is-open {
                visibility: visible;
                transform: translateX(0);
            }

            .sidebar.is-open ~ .sidebar-overlay { display: block; }

            .sidebar-close { display: inline-flex; }

            .topbar { padding: 0 1rem; }
            .content-wrapper { padding: 1.25rem; }
        }

        @media (max-width: 575.98px) {
            .topbar-breadcrumb { font-size: 0.75rem; }
            .topbar-btn span { display: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .app-layout, .sidebar, .sidebar-link { transition: none; }
        }

        /* Custom scrollbar */
        .sidebar-nav::-webkit-scrollbar { width: 6px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 3px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>

    @stack('styles')
</head>
<body>

    <div class="app-layout">

        {{-- ================== SIDEBAR ================== --}}
        @include('components.sidebar', ['panel' => $panel ?? 'transaksi'])

        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

        {{-- ================== MAIN ================== --}}
        <div class="main-content">

            {{-- TOPBAR --}}
            <div class="topbar">
                <div class="topbar-left">
                    <button type="button" class="topbar-menu-toggle" id="menuToggle" aria-label="Buka menu navigasi" aria-controls="appSidebar" aria-expanded="false" title="Buka menu navigasi">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>

                    @if(isset($breadcrumbs))
                        <nav class="topbar-breadcrumb" aria-label="Jejak navigasi">
                            @foreach($breadcrumbs as $label => $url)
                                @if($loop->last)
                                    <span class="current" aria-current="page">{{ $label }}</span>
                                @else
                                    <a href="{{ $url }}" style="color:inherit;text-decoration:none;">{{ $label }}</a>
                                    <i class="bi bi-chevron-right" style="font-size:0.625rem;"></i>
                                @endif
                            @endforeach
                        </nav>
                    @endif
                </div>

                <div class="topbar-actions">
                    @include('partials.application-time', ['mode' => 'inline'])
                    @include('partials.notification-bell')
                </div>
            </div>

            {{-- CONTENT --}}
            <main class="content-wrapper" id="mainContent">
                @yield('content')
            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/sidebar.js') }}" defer></script>
    @stack('scripts')
</body>
</html>

~~~~

## FILE: resources/views/partials/application-time.blade.php

SHA-256: `9ddb5aa6110406408b945e3e62c4ecee0859ee200cae006af92ce9fd05fdb257`

~~~~php
@php
    $applicationNow = \App\Support\AppTime::now();
    $applicationTimeMode = $mode ?? 'bar';
@endphp

@if($applicationTimeMode === 'bar')
    <header class="application-time-bar" aria-label="Tanggal dan waktu aplikasi">
@endif

<time
    class="application-live-time application-live-time--{{ $applicationTimeMode }}"
    data-application-live-time
    data-server-epoch="{{ $applicationNow->getTimestamp() * 1000 }}"
    data-time-zone="{{ \App\Support\AppTime::timezone() }}"
    datetime="{{ $applicationNow->toIso8601String() }}"
    role="timer"
    aria-live="off"
    aria-label="{{ $applicationNow->format('F - j - Y, g:i:s a') }}"
>
    <span class="application-live-time__date">{{ $applicationNow->format('F - j - Y') }}</span>
    <span class="application-live-time__divider" aria-hidden="true"></span>
    <span class="application-live-time__clock">{{ $applicationNow->format('g:i:s a') }}</span>
</time>

@if($applicationTimeMode === 'bar')
    @auth
        @include('partials.notification-bell')
    @endauth
    </header>
@endif

@once
    <style>
        .application-time-bar {
            position: relative;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            min-height: 56px;
            padding: 0.625rem 1.5rem;
            box-sizing: border-box;
        }

        .application-time-bar .notif-wrap {
            position: relative;
            top: auto;
            right: auto;
            flex: 0 0 auto;
        }

        .application-live-time {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            min-height: 38px;
            padding: 0.5rem 0.75rem;
            color: var(--text-secondary, #64748b);
            font-family: inherit;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
            line-height: 1;
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 11px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 6px 18px rgba(15, 23, 42, 0.05);
            box-sizing: border-box;
        }

        .application-live-time__date {
            color: var(--text-secondary, #64748b);
            font-size: 0.775rem;
            font-weight: 600;
        }

        .application-live-time__divider {
            width: 1px;
            height: 18px;
            background: var(--border-color, #e2e8f0);
            border-radius: 999px;
            flex: 0 0 1px;
        }

        .application-live-time__clock {
            min-width: 76px;
            color: var(--text-primary, #0f172a);
            font-size: 0.825rem;
            font-weight: 700;
            text-align: center;
        }

        .application-live-time--inline {
            align-self: stretch;
            margin-right: 0.375rem;
            background: rgba(248, 250, 252, 0.92);
            box-shadow: none;
        }

        .application-live-time--plain {
            padding: 0;
            background: transparent;
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }

        @media (max-width: 767.98px) {
            .topbar {
                height: auto;
                min-height: var(--topbar-height, 64px);
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }

            .topbar-actions {
                flex-wrap: wrap;
                justify-content: flex-end;
                max-width: min(260px, calc(100vw - 5rem));
            }

            .application-live-time--inline {
                order: 10;
                flex-basis: 100%;
                justify-content: flex-end;
                align-self: auto;
                margin: 0;
                min-height: 32px;
                padding: 0.375rem 0.5rem;
            }

            .application-time-bar {
                min-height: 48px;
                padding: 0.5rem 0.75rem;
                gap: 0.625rem;
            }

            .application-time-bar .application-live-time {
                min-width: 0;
                flex: 0 1 auto;
                flex-wrap: wrap;
                justify-content: center;
                row-gap: 0.375rem;
            }

            .application-time-bar .application-live-time__divider {
                display: none;
            }

            .application-live-time__date {
                font-size: 0.7rem;
            }

            .application-live-time__clock {
                min-width: 68px;
                font-size: 0.75rem;
            }
        }

        .login-header .application-live-time {
            justify-content: center;
            margin-top: 1rem;
        }

        .login-header .application-live-time__date,
        .login-header .application-live-time__clock {
            color: rgba(255, 255, 255, 0.92);
        }

        .login-header .application-live-time__divider {
            background: rgba(255, 255, 255, 0.35);
        }
    </style>
    <script src="{{ asset('js/application-time.js') }}" defer></script>
@endonce

~~~~

## FILE: resources/views/partials/bs-approval-link.blade.php

SHA-256: `0c18f9d686643297836e548c6eb8121663a58c9a751906a976100a9d99fa4250`

~~~~php
@canany(['akses.pengajuan-bs', 'akses.transaksi-perjalanan-dinas', 'akses.cash-flow'])
    @php($pendingBsUsage = \App\Models\TransaksiPerjalananDinasHeader::forBsOwner((int) auth()->id())->where('user_id', '<>', auth()->id())->where('status', 'PENDING_OWNER')->count())
    <div class="my-2"><a href="{{ route('bs-usage-approvals.index') }}" class="btn btn-sm btn-outline-primary">Persetujuan Pemakaian BS <span class="badge bg-primary">{{ $pendingBsUsage }} menunggu</span></a></div>
@endcanany

~~~~

## FILE: resources/views/partials/notification-bell.blade.php

SHA-256: `a6258af641b33c59da94fef9a9ab7cd15d186a6655c8598b42aa88ca421084d0`

~~~~php
{{-- 🎯 LONCENG NOTIFIKASI GLOBAL (floating, auto-inject via middleware) --}}
<style>
    .notif-wrap{position:fixed;top:14px;right:18px;z-index:9999;}
    .notif-bell{background:#ffffff;border:1px solid #e2e8f0;font-size:1.25rem;color:#64748b;cursor:pointer;position:relative;padding:8px 12px;border-radius:12px;transition:all .2s;box-shadow:0 4px 10px -2px rgba(0,0,0,.08);}
    .notif-bell:hover{background:#f1f5f9;color:#0f172a;}
    .notif-badge{position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:999px;font-size:.65rem;font-weight:700;padding:3px 6px;line-height:1;border:2px solid #fff;}
    .notif-panel{position:absolute;right:0;top:calc(100% + 10px);width:350px;max-width:calc(100vw - 2rem);background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 20px 40px -12px rgba(0,0,0,.18);display:none;overflow:hidden;}
    .notif-panel.show{display:block;}
    .notif-head{display:flex;justify-content:space-between;align-items:center;padding:.7rem 1rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:.85rem;}
    .notif-item{display:block;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;text-decoration:none;color:#0f172a;transition:background .15s;}
    .notif-item:hover{background:#f8fafc;}
    .notif-title{font-weight:700;font-size:.85rem;margin-bottom:2px;}
    .notif-msg{font-size:.8rem;color:#64748b;line-height:1.45;}
    .notif-time{font-size:.7rem;color:#94a3b8;margin-top:3px;}
</style>

<div class="notif-wrap">
    <button type="button" class="notif-bell" id="notifBell" title="Notifikasi">
        <i class="bi bi-bell-fill"></i>
        <span class="notif-badge d-none" id="notifBadge">0</span>
    </button>
    <div class="notif-panel" id="notifPanel">
        <div class="notif-head">
            <span class="fw-bold">Notifikasi</span>
            <a href="#" id="notifReadAll" class="text-primary text-decoration-none small">Tandai semua dibaca</a>
        </div>
        <div id="notifList" style="max-height:340px;overflow-y:auto;">
            <div class="text-center text-muted small py-4">Memuat…</div>
        </div>
    </div>
</div>

<script>
(function(){
    const bell  = document.getElementById('notifBell');
    const panel = document.getElementById('notifPanel');
    const badge = document.getElementById('notifBadge');
    const list  = document.getElementById('notifList');
    const token = '{{ csrf_token() }}';

    bell.addEventListener('click', function(e){
        e.stopPropagation();
        panel.classList.toggle('show');
        if (panel.classList.contains('show')) load();
    });
    document.addEventListener('click', function(e){
        if (!panel.contains(e.target) && !bell.contains(e.target)) panel.classList.remove('show');
    });

    function load(){
        fetch('{{ route("notifications.unread") }}', {headers:{'Accept':'application/json'}})
            .then(r => r.json())
            .then(d => {
                badge.textContent = d.count;
                badge.classList.toggle('d-none', d.count === 0);

                if (!d.items.length){
                    list.innerHTML = '<div class="text-center text-muted small py-4">Tidak ada notifikasi baru ✅</div>';
                    return;
                }
                list.innerHTML = d.items.map(n => `
                    <a href="${n.url}" class="notif-item" data-id="${n.id}">
                        <div class="notif-title">${n.title}</div>
                        <div class="notif-msg">${n.message}</div>
                        <div class="notif-time">${n.time}</div>
                    </a>`).join('');

                list.querySelectorAll('.notif-item').forEach(a => {
                    a.addEventListener('click', () => {
                        fetch('/notifications/' + a.dataset.id + '/read', {
                            method:'POST',
                            headers:{'X-CSRF-TOKEN': token, 'Accept':'application/json'}
                        });
                    });
                });
            });
    }

    document.getElementById('notifReadAll').addEventListener('click', function(e){
        e.preventDefault();
        fetch('{{ route("notifications.read_all") }}', {
            method:'POST',
            headers:{'X-CSRF-TOKEN': token, 'Accept':'application/json'}
        }).then(() => { badge.classList.add('d-none'); load(); });
    });

    load();
    setInterval(load, 15000); // polling real-time
})();
</script>

~~~~

## FILE: resources/views/partials/transaction-funding.blade.php

SHA-256: `35671c9c0eaffc0841c8ff05cca3bb6fc28f9669da49cbb2f9cd79c74a5a143a`

~~~~php
@php
    $fundingDocument = $fundingDocument ?? null;
    $fundingInputter = (int) ($fundingDocument?->user_id ?? auth()->id());
    $fundingOwner = (int) old('bs_owner_id', $fundingDocument?->bsCashOwnerId() ?? $fundingInputter);
    $fundingDate = old('tanggal', $fundingDocument?->tanggal ?? \App\Support\AppTime::now()->toDateString());
    $fundingPeriod = is_string($fundingDate) && preg_match('/^\d{4}-(0[1-9]|1[0-2])-/', $fundingDate)
        ? substr($fundingDate, 0, 7) : app(\App\Services\PeriodService::class)->current();
    $fundingOwners = app(\App\Services\TransactionFundingService::class)->owners($fundingInputter, $fundingType === 'perjalanan_dinas');
    $fundingCash = app(\App\Services\BsCashFlowService::class);
    $fundingAllowed = $fundingOwners->contains('id', $fundingOwner);
    $fundingBalance = $fundingAllowed ? $fundingCash->calculate($fundingOwner, $fundingPeriod) : null;
    $fundingAvailable = $fundingBalance ? $fundingCash->availableBalance($fundingBalance) : null;
@endphp
<div id="funding-bs-fields" data-balance-url="{{ route('bs-balance.balances') }}" data-module="{{ $fundingType }}" data-transaction="{{ $fundingDocument?->id }}" data-inputter="{{ $fundingInputter }}" data-period="{{ $fundingPeriod }}">
    @if($fundingType === 'perjalanan_dinas')
        <label for="bs_owner_id_select" class="form-label fw-semibold">Pemilik Saldo BS</label>
        <select name="bs_owner_id" id="bs_owner_id_select" class="form-select" required>
            @foreach($fundingOwners as $owner)
                <option value="{{ $owner->id }}" @selected($fundingOwner === (int) $owner->id)>
                    {{ (int) $owner->id === $fundingInputter ? 'Saldo sendiri' : $owner->nama.' (Grup Dinas)' }}
                </option>
            @endforeach
        </select>
        <p class="small text-muted mt-2">Anggota grup dapat mengajukan pemakaian saldo gabungan pemohon untuk Perjalanan Dinas. Pemohon harus menyetujui rincian sebelum saldo berkurang.</p>
    @endif
    <div id="bs-saldo-info" class="mt-2" data-balance="{{ $fundingAvailable }}">
        Sisa Saldo BS: <strong id="bs-balance-display">{{ $fundingAvailable === null ? 'Tidak tersedia' : 'Rp '.number_format($fundingAvailable, 2, ',', '.') }}</strong>
    </div>
    <p id="bs-opening-info" class="small text-warning mt-2" @if($fundingBalance && $fundingBalance['opening_final']) hidden @endif>Saldo bulan sebelumnya tersedia setelah tutup buku.</p>
    <p class="small text-muted mt-2">Operasional Sales dan Perjalanan Dinas memakai satu saldo BS. {{ $fundingType === 'perjalanan_dinas' ? 'Pemakaian saldo sendiri dibukukan setelah Konfirmasi; pemakaian saldo pemohon dibukukan setelah disetujui pemohon.' : 'Saldo berkurang setelah Konfirmasi.' }}</p>
</div>
<script src="{{ asset('js/bs-balance.js') }}"></script>

~~~~

## FILE: resources/views/partials/travel-confirmation.blade.php

SHA-256: `ffe9506c22aa93ad08e314adc8166284035ea3d854c0f2650c1c3886d0a071a2`

~~~~php
@php
    $reviewOpen = app(\App\Services\PeriodService::class)->isOpen($transaksi->periode);
    $isInputter = (int) auth()->id() === (int) $transaksi->user_id;
@endphp
<div class="alert {{ $transaksi->status === 'VALIDATED' ? 'alert-success' : ($transaksi->status === 'REJECTED_OWNER' ? 'alert-danger' : 'alert-warning') }}">
    <strong>{{ $transaksi->status_label }}</strong>
    @if($transaksi->status === 'DRAFT')
        <p class="mb-0">Draft belum mengurangi saldo BS. {{ $transaksi->needsOwnerApproval() ? 'Konfirmasi anggota akan mengirim permintaan kepada pemohon. Saldo berkurang setelah pemohon menyetujui.' : 'Periksa rincian lalu Konfirmasi untuk membukukan pemakaian.' }}</p>
        @if($canEdit ?? false)
            <form class="mt-3" method="POST" action="{{ route('transaksi_perjalanan_dinas.validate', $transaksi->transaksi_id) }}" onsubmit="return confirm('{{ $transaksi->needsOwnerApproval() ? 'Kirim rincian ini kepada pemohon untuk persetujuan pemakaian BS?' : 'Konfirmasi transaksi ini? Pemakaian BS akan dibukukan dan transaksi tidak dapat diubah.' }}');">
                @csrf
                <button class="btn btn-success">{{ $transaksi->needsOwnerApproval() ? 'Konfirmasi & Ajukan Pemakaian BS' : 'Konfirmasi' }}</button>
            </form>
        @endif
    @elseif($transaksi->status === 'PENDING_OWNER')
        <p class="mb-1">Diajukan kepada {{ $transaksi->bsOwner?->nama ?? $transaksi->bonSementara?->user?->nama ?? 'pemilik saldo' }} pada {{ \App\Support\AppTime::format($transaksi->submitted_at) }}.</p>
        <p class="mb-0">Saldo belum berkurang. Rincian terkunci selama menunggu persetujuan. Permintaan tidak disetujui otomatis.</p>
        @if($reviewOpen && $isInputter)
            <form class="mt-3" method="POST" action="{{ route('transaksi_perjalanan_dinas.withdraw', $transaksi->transaksi_id) }}" onsubmit="return confirm('Tarik permintaan dan kembalikan transaksi menjadi draft?');">
                @csrf
                <input type="hidden" name="approval_version" value="{{ $transaksi->approval_version }}">
                <button class="btn btn-outline-secondary">Tarik Permintaan ke Draft</button>
            </form>
        @endif
    @elseif($transaksi->status === 'REJECTED_OWNER')
        <p class="mb-1">Alasan: {{ $transaksi->owner_rejection_reason }}</p>
        <p class="mb-0">Saldo tidak berkurang. Anggota dapat memperbaiki dan mengajukan ulang.</p>
        @if($reviewOpen && $isInputter)
            <form class="mt-3" method="POST" action="{{ route('transaksi_perjalanan_dinas.revise', $transaksi->transaksi_id) }}">
                @csrf
                <input type="hidden" name="approval_version" value="{{ $transaksi->approval_version }}">
                <button class="btn btn-outline-secondary">Buka Draft untuk Perbaikan</button>
            </form>
        @endif
    @elseif($transaksi->status === 'VALIDATED')
        <p class="mb-0">Transaksi sudah final dan tidak dapat diubah.</p>
        <div class="mt-2">Waktu Konfirmasi: <strong>{{ \App\Support\AppTime::format($transaksi->validated_at) }}</strong></div>
    @endif
</div>

~~~~
