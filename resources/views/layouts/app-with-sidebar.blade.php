<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
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
    @include('partials.mobile-assets')
</head>
<body class="app-shell">

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
    <script src="{{ asset('js/sidebar.js') }}?v={{ filemtime(public_path('js/sidebar.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>
