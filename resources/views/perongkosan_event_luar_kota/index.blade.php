<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Daftar Event Luar Kota — EMMO APP</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --border-strong: #cbd5e1;
            --accent-primary: #3b82f6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --accent-info: #0ea5e9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            background-image:
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0px, transparent 50%);
        }

        /* ---------- Navbar Modern ---------- */
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
            text-decoration: none;
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
            text-decoration: none;
        }
        .btn-nav-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        .btn-nav-outline:hover {
            background-color: #f1f5f9;
            color: var(--text-primary);
            border-color: var(--border-strong);
        }
        .btn-nav-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #ef4444;
        }
        .btn-nav-danger:hover {
            background-color: #fee2e2;
            border-color: #fca5a5;
            color: #dc2626;
        }

        /* ---------- Page Header ---------- */
        .page-header {
            padding: 2.5rem 0 1.5rem;
        }
        .page-title {
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .page-title .count-tag {
            font-size: 0.8rem;
            font-weight: 700;
            background: #dcfce7;
            color: #166534;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            letter-spacing: 0;
        }
        .page-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 400;
            margin: 0;
        }
        .page-breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 50px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .page-breadcrumb a { text-decoration: none; color: var(--text-secondary); }
        .page-breadcrumb a:hover { color: var(--text-primary); }
        .page-breadcrumb .current { color: var(--accent-success); font-weight: 600; }

        /* ---------- Alerts ---------- */
        .alert-custom-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-custom-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }

        /* ---------- Stats Cards ---------- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
            border-color: transparent;
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .stat-icon.green  { background: #dcfce7; color: #16a34a; }
        .stat-icon.blue   { background: #dbeafe; color: #2563eb; }
        .stat-icon.amber  { background: #fef3c7; color: #d97706; }
        .stat-icon.red    { background: #fee2e2; color: #dc2626; }
        .stat-info .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-bottom: 0.125rem;
        }
        .stat-info .value {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }
        .stat-info .value.small-text {
            font-size: 1rem;
            font-weight: 600;
        }

        /* ---------- Table Card ---------- */
        .table-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px -12px rgba(0,0,0,0.06);
            position: relative;
        }
        .table-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-success), var(--accent-primary));
        }

        /* Action Bar */
        .action-bar {
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            background: white;
        }
        .action-bar-left {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }
        .action-icon-wrapper {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: #dcfce7;
            color: var(--accent-success);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .action-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.01em;
        }
        .action-subtitle {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin: 0;
        }

        .action-bar-right {
            display: flex;
            gap: 0.625rem;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Export form */
        .export-form {
            display: inline-flex;
            gap: 0.5rem;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.375rem 0.375rem 0.375rem 0.875rem;
        }
        .export-form .form-select {
            border: none;
            background: transparent;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            padding: 0.25rem 1.75rem 0.25rem 0;
            min-width: 140px;
        }
        .export-form .form-select:focus {
            box-shadow: none;
            outline: none;
        }

        /* Buttons */
        .btn-primary-action {
            background: var(--text-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.625rem 1.125rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
        }
        .btn-primary-action:hover {
            background: #1e293b;
            transform: translateY(-1px);
            color: white;
            box-shadow: 0 6px 15px -3px rgba(15, 23, 42, 0.3);
        }
        .btn-success-action {
            background: var(--accent-success);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .btn-success-action:hover {
            background: #059669;
            color: white;
        }
        .btn-back {
            background: white;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            border-radius: 10px;
            padding: 0.625rem 1.125rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn-back:hover {
            background: #f1f5f9;
            color: var(--text-primary);
            border-color: var(--border-strong);
        }

        /* ---------- Table ---------- */
        .table-wrapper {
            overflow-x: auto;
        }
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }
        .modern-table thead th {
            background: #f8fafc;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.25rem;
            border: none;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }
        .modern-table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 0.9rem;
            vertical-align: middle;
            background: white;
            transition: background 0.15s ease;
        }
        .modern-table tbody tr:hover td {
            background: #f8fafc;
        }
        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        .trx-id {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-primary);
            letter-spacing: 0.01em;
        }
        .date-cell {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            white-space: nowrap;
        }
        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        .user-name { font-weight: 600; color: var(--text-primary); font-size: 0.875rem; }
        .location-cell {
            display: flex; align-items: center; gap: 0.5rem;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
        }
        .location-cell i { color: var(--accent-success); }
        .desc-cell {
            color: var(--text-secondary);
            font-size: 0.875rem;
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .nominal-cell {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
            white-space: nowrap;
        }

        /* Over Plafon Badge */
        .badge-over {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
            margin-top: 0.25rem;
            border: 1px solid #fde68a;
        }
        .badge-over i { font-size: 0.65rem; }

        /* Photo Pills */
        .photo-pills {
            display: flex;
            gap: 0.35rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .photo-pill {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid;
        }
        .photo-pill.p1 { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .photo-pill.p1:hover { background: #dbeafe; transform: translateY(-2px); }
        .photo-pill.p2 { background: #ecfeff; border-color: #a5f3fc; color: #0e7490; }
        .photo-pill.p2:hover { background: #cffafe; transform: translateY(-2px); }
        .photo-pill.p3 { background: #f1f5f9; border-color: #cbd5e1; color: #475569; }
        .photo-pill.p3:hover { background: #e2e8f0; transform: translateY(-2px); }
        .no-photo {
            color: var(--text-muted);
            font-size: 0.8rem;
            font-style: italic;
        }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 0.35rem;
        }
        .btn-icon-action {
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s ease;
            background: white;
            cursor: pointer;
            padding: 0;
        }
        .btn-edit {
            border-color: #fde68a;
            color: #92400e;
            background: #fffbeb;
        }
        .btn-edit:hover {
            background: #fef3c7;
            color: #78350f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px -2px rgba(245, 158, 11, 0.2);
        }
        .btn-delete {
            border-color: #fecaca;
            color: #991b1b;
            background: #fef2f2;
        }
        .btn-delete:hover {
            background: #fee2e2;
            color: #7f1d1d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px -2px rgba(239, 68, 68, 0.2);
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }
        .empty-icon {
            width: 80px; height: 80px;
            border-radius: 20px;
            background: #f1f5f9;
            color: var(--text-muted);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }
        .empty-title {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 1.125rem;
        }
        .empty-subtitle {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        /* Pagination Wrapper */
        .pagination-wrapper {
            padding: 1.25rem 1.75rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafbfc;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .pagination-info {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .pagination-info strong { color: var(--text-primary); font-weight: 700; }
        .pagination .page-link {
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.4rem 0.85rem;
            border-radius: 8px !important;
            margin: 0 2px;
            background: white;
        }
        .pagination .page-link:hover {
            background: #f1f5f9;
            color: var(--text-primary);
            border-color: var(--border-strong);
        }
        .pagination .page-item.active .page-link {
            background: var(--text-primary);
            border-color: var(--text-primary);
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: var(--text-muted);
            background: #f8fafc;
            border-color: var(--border-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .action-bar { padding: 1rem 1.25rem; }
            .action-bar-right { width: 100%; justify-content: flex-start; }
            .export-form { width: 100%; }
            .export-form .form-select { flex-grow: 1; }
            .modern-table thead th,
            .modern-table tbody td { padding: 0.875rem 0.875rem; }
            .desc-cell { max-width: 150px; }
        }
    </style>
    @include('partials.mobile-assets')
    @include('partials.transaction-actions-assets')
    @include('partials.transaction-list-assets')
</head>
<body class="transaction-list-page">
    @include('partials.application-time')

    <!-- Modern Clean Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard.transaksi') }}">
                <i class="bi bi-hexagon-fill me-2"></i> EMMO APP
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a href="{{ route('profile.index') }}" class="btn btn-nav btn-nav-outline">
                            <i class="bi bi-person-circle"></i> Profil Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-nav btn-nav-danger">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 1280px;">

        <!-- Page Header -->
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="display-6 page-title">
                    Daftar Event Luar Kota
                    @if(isset($events) && method_exists($events, 'total'))
                        <span class="count-tag">{{ $events->total() }} Transaksi</span>
                    @elseif(isset($events))
                        <span class="count-tag">{{ $events->count() }} Transaksi</span>
                    @endif
                </h1>
                <p class="page-subtitle">Pantau dan kelola seluruh transaksi perongkosan event luar kota.</p>
            </div>
            <div class="page-breadcrumb">
                <i class="bi bi-house-door"></i>
                <a href="{{ route('dashboard.transaksi') }}">Transaksi</a>
                <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                <span class="current">Event Luar Kota</span>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert-custom-success mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-custom-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- 🎯 STATS OVERVIEW -->
        @if(isset($events) && (method_exists($events, 'count') ? $events->count() : count($events)) > 0)
        @php
            $collection = method_exists($events, 'getCollection') ? $events->getCollection() : $events;
            $totalNominal = $collection->sum('nominal');
            $overCount = $collection->where('is_over_plafon', true)->count();
        @endphp
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-receipt-cutoff"></i></div>
                <div class="stat-info">
                    <div class="label">Total Transaksi</div>
                    <div class="value">{{ method_exists($events, 'total') ? $events->total() : $events->count() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-info">
                    <div class="label">Nominal Halaman Ini</div>
                    <div class="value small-text">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <div class="label">Over Plafon</div>
                    <div class="value" style="color: {{ $overCount > 0 ? '#d97706' : 'var(--text-primary)' }}">{{ $overCount }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- 🎯 TABLE CARD -->
        <div class="table-card">

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="action-bar-left">
                    <div class="action-icon-wrapper">
                        <i class="bi bi-list-ul"></i>
                    </div>
                    <div>
                        <h5 class="action-title">Data Transaksi</h5>
                        <p class="action-subtitle">
                            @if(isset($events) && method_exists($events, 'count'))
                                Menampilkan {{ $events->count() }} dari {{ method_exists($events, 'total') ? $events->total() : $events->count() }} data
                            @else
                                Semua transaksi tercatat
                            @endif
                        </p>
                    </div>
                </div>
                <div class="action-bar-right">
                    {{-- Export Form --}}
                    <form method="GET" action="{{ route('perongkosan_event_luar_kota.index') }}" class="transaction-list-filter export-form flex-wrap">
                        @include('partials.transaction-reported-filter')
                        <i class="bi bi-file-earmark-spreadsheet" style="color: var(--accent-success);"></i>
                        <label class="transaction-list-period-label" for="transaction-list-period">Periode transaksi</label>
                        <select id="transaction-list-period" name="periode" class="form-select" aria-label="Periode transaksi">
                            <option value="">Semua Periode</option>
                            @foreach(\App\Models\Periode::orderBy('periode', 'desc')->get() as $p)
                                <option value="{{ $p->periode }}" @selected(($periode ?? '') === $p->periode)>{{ $p->periode }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                        <button type="submit" formaction="{{ route('perongkosan_event_luar_kota.export') }}" class="btn-success-action">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </form>
                    <a href="{{ route('perongkosan_event_luar_kota.create') }}" class="btn-primary-action">
                        <i class="bi bi-plus-lg"></i> Tambah Event
                    </a>
                    <a href="{{ route('dashboard.transaksi') }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Table Content -->
            <div class="table-wrapper" role="region" aria-label="Daftar transaksi event luar kota">
                <table class="modern-table" data-transaction-list="elk" role="table">
                    <thead role="rowgroup">
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Karyawan</th>
                            <th>Lokasi</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Total Nominal</th>
                            <th style="text-align: center;">Foto Bukti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody role="rowgroup">
                        @forelse($events as $event)
                        <tr role="row">
                            <td data-field="id" data-label="ID Transaksi" role="cell">
                                <span class="trx-id">{{ $event->transaksi_id }}</span>
                            </td>
                            <td data-field="date" data-label="Tanggal" role="cell">
                                <span class="date-cell">
                                    <i class="bi bi-calendar-event" style="color: var(--accent-primary); margin-right: 0.35rem;"></i>
                                    {{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}
                                </span>
                            </td>
                            <td data-field="user" data-label="Karyawan" role="cell">
                                <div class="user-cell">
                                    @php
                                        $userName = $event->user->name ?? $event->user->nama ?? 'Unknown';
                                        $initials = collect(explode(' ', $userName))
                                            ->take(2)
                                            ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                                            ->join('');
                                    @endphp
                                    <div class="user-avatar">{{ $initials ?: '??' }}</div>
                                    <span class="user-name">{{ $userName }}</span>
                                </div>
                            </td>
                            <td data-field="location" data-label="Lokasi" role="cell">
                                <div class="location-cell">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span>{{ $event->wilayah->nama ?? '-' }}</span>
                                </div>
                            </td>
                            <td data-field="description" data-label="Deskripsi" role="cell">
                                <div class="desc-cell" title="{{ $event->description }}">{{ $event->description ?: '-' }}</div>
                            </td>
                            <td data-field="total" data-label="Total nominal" role="cell" class="text-end">
                                <div class="transaction-total nominal-cell text-nowrap">Rp {{ number_format($event->nominal, 2, ',', '.') }}</div>
                                @if($event->is_over_plafon)
                                    <div class="badge-over">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        Over Plafon
                                    </div>
                                @endif
                            </td>
                            <td data-field="photos" data-label="Foto bukti" role="cell" style="text-align: center;">
                                @if($event->foto_event_1 || $event->foto_event_2 || $event->foto_event_3)
                                    <div class="photo-pills">
                                        @if($event->foto_event_1)
                                            <a href="{{ asset('storage/' . $event->foto_event_1) }}" target="_blank" class="photo-pill p1" title="Lihat Foto 1">
                                                <i class="bi bi-camera-fill"></i>
                                            </a>
                                        @endif
                                        @if($event->foto_event_2)
                                            <a href="{{ asset('storage/' . $event->foto_event_2) }}" target="_blank" class="photo-pill p2" title="Lihat Foto 2">
                                                <i class="bi bi-camera-fill"></i>
                                            </a>
                                        @endif
                                        @if($event->foto_event_3)
                                            <a href="{{ asset('storage/' . $event->foto_event_3) }}" target="_blank" class="photo-pill p3" title="Lihat Foto 3">
                                                <i class="bi bi-camera-fill"></i>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="no-photo">—</span>
                                @endif
                            </td>
                            <td data-field="actions" role="cell">
                                @include('partials.transaction-actions', [
                                    'transaction' => $event,
                                    'transactionType' => 'elk',
                                    'detailUrl' => route('perongkosan_event_luar_kota.show', $event->id),
                                    'deleteUrl' => route('perongkosan_event_luar_kota.destroy', $event->id),
                                ])
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="bi bi-inbox"></i>
                                    </div>
                                    <div class="empty-title">Belum Ada Transaksi</div>
                                    <div class="empty-subtitle">Mulai catat event luar kota pertama Anda untuk melihat data di sini.</div>
                                    <a href="{{ route('perongkosan_event_luar_kota.create') }}" class="btn-primary-action">
                                        <i class="bi bi-plus-lg"></i> Tambah Event Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($events) && method_exists($events, 'links') && $events->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Menampilkan <strong>{{ $events->firstItem() }}</strong> sampai <strong>{{ $events->lastItem() }}</strong> dari <strong>{{ $events->total() }}</strong> transaksi
                    </div>
                    <div>
                        {{ $events->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>

        <div style="height: 3rem;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
