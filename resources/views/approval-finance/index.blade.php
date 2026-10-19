<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pencairan BS Finance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            padding-bottom: 6rem; /* ruang untuk floating bar */
        }

        /* ===== HEADER ===== */
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
        .page-title { font-weight: 800; font-size: 1.6rem; letter-spacing: -0.02em; margin: 0; }
        .page-subtitle { color: var(--text-secondary); font-size: 0.9rem; margin: 0.3rem 0 0; }
        .btn-nav { background-color: white; border: 1px solid var(--border-color); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; white-space: nowrap; }
        .btn-nav:hover { background-color: #f1f5f9; color: var(--text-primary); }
        .btn-nav-dark { background: var(--text-primary); color: #fff; border: none; }
        .btn-nav-dark:hover { background: #334155; color: #fff; }

        /* ===== STAT CARDS ===== */
        .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.9rem; height: 100%; transition: all 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px -6px rgba(15,23,42,0.08); }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
        .stat-label { font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.15rem; }
        .stat-value { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; line-height: 1.2; }

        /* ===== TOOLBAR ===== */
        .toolbar { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 0.9rem 1.25rem; margin: 1rem 0; }
        .toolbar .form-select { border: 1.5px solid var(--border-color); border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; }
        .toolbar .form-select:focus { border-color: var(--text-primary); box-shadow: 0 0 0 3px rgba(15,23,42,0.08); }
        .current-badge { display: inline-block; background: #10b981; color: white; font-size: 0.65rem; font-weight: 700; padding: 0.25em 0.7em; border-radius: 999px; margin-left: 0.5rem; vertical-align: middle; }

        /* ===== FILTER CHIPS ===== */
        .chip-filter { border: 1px solid var(--border-color); background: #fff; border-radius: 999px; padding: 0.38rem 0.9rem; font-size: 0.78rem; font-weight: 700; color: var(--text-secondary); cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; gap: 0.4rem; }
        .chip-filter:hover { background: #f1f5f9; color: var(--text-primary); }
        .chip-filter.active { background: var(--text-primary); border-color: var(--text-primary); color: #fff; }
        .chip-count { background: rgba(100,116,139,0.12); border-radius: 999px; padding: 0.05em 0.55em; font-size: 0.7rem; font-weight: 800; }
        .chip-filter.active .chip-count { background: rgba(255,255,255,0.22); }

        .density-btn.active { background: var(--text-primary); color: #fff; border-color: var(--text-primary); }

        /* ===== 🎯 CARD LIST ===== */
        .bs-card { background: var(--bg-card); border: 1px solid var(--border-color); border-left: 4px solid var(--border-color); border-radius: 16px; display: flex; flex-direction: column; height: 100%; overflow: hidden; transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease; position: relative; }
        .bs-card:hover { box-shadow: 0 12px 24px -12px rgba(15,23,42,0.14); transform: translateY(-2px); }
        .bs-card.status-approved_atasan  { border-left-color: #f59e0b; }
        .bs-card.status-disbursed        { border-left-color: #10b981; }
        .bs-card.status-rejected_finance { border-left-color: #ef4444; }

        /* 🎯 SELECTED STATE */
        .bs-card.selected {
            border-color: #2563eb !important;
            border-left-color: #1d4ed8 !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15), 0 8px 20px -8px rgba(37,99,235,0.3);
            background: linear-gradient(to right, #eff6ff 0%, #ffffff 40%);
        }
        .bs-card.selected::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 36px; height: 36px;
            background: #2563eb;
            clip-path: polygon(100% 0, 0 0, 100% 100%);
            z-index: 2;
        }
        .bs-card.selected::before {
            content: '\F26B'; /* bootstrap check icon via bootstrap-icons */
            font-family: 'bootstrap-icons';
            position: absolute;
            top: 4px; right: 4px;
            color: white;
            font-size: 0.75rem;
            z-index: 3;
        }

        .bs-select-check {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 6px;
            border: 2px solid var(--border-color);
            background: white; cursor: pointer;
            flex-shrink: 0; transition: all 0.15s ease;
            position: relative;
        }
        .bs-select-check:hover { border-color: #2563eb; background: #eff6ff; }
        .bs-select-check.checked {
            background: #2563eb; border-color: #2563eb;
        }
        .bs-select-check.checked::after {
            content: '\F26B';
            font-family: 'bootstrap-icons';
            color: white;
            font-size: 0.85rem;
            font-weight: 900;
        }
        .bs-select-check.indeterminate {
            background: #2563eb; border-color: #2563eb;
        }
        .bs-select-check.indeterminate::after {
            content: '';
            width: 10px; height: 2px; background: white; border-radius: 1px;
        }

        .bs-card-head { padding: 1rem 1.25rem 0.85rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; border-bottom: 1px dashed var(--border-color); }
        .head-left { display: flex; align-items: flex-start; gap: 0.7rem; flex: 1; min-width: 0; }
        .head-content { flex: 1; min-width: 0; }
        .doc-no { font-weight: 800; font-size: 1rem; letter-spacing: -0.01em; }
        .head-nominal { text-align: right; flex-shrink: 0; }
        .head-nominal .amount { font-size: 1.08rem; font-weight: 800; color: #2563eb; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .head-nominal .meta-label { font-size: 0.66rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 0.18rem; }

        .bs-card-body { padding: 0.95rem 1.25rem; display: flex; flex-direction: column; gap: 0.7rem; flex: 1; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem 1rem; }
        @media (max-width: 420px) { .meta-grid { grid-template-columns: 1fr; } }
        .meta-label { font-size: 0.66rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 0.18rem; }
        .meta-value { font-size: 0.85rem; font-weight: 600; }
        .meta-value.muted { font-weight: 500; color: var(--text-secondary); }

        .ket-box { border-radius: 10px; padding: 0.55rem 0.8rem; font-size: 0.82rem; line-height: 1.55; }
        .ket-pending { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .ket-ok      { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .ket-reject  { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .bs-card-foot { padding: 0.85rem 1.25rem; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }

        .avatar { width: 30px; height: 30px; border-radius: 50%; background: #eef2ff; color: #4f46e5; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

        .badge-status { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.38em 0.75em; border-radius: 999px; font-weight: 700; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .badge-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: 0.9; }
        .badge-tipe { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35em 0.7em; border-radius: 999px; font-weight: 700; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .badge-tipe-operasional { background: #eff6ff; color: #1d4ed8; }
        .badge-tipe-dinas       { background: #ecfdf5; color: #047857; }

        .nominal-display { border-radius: 0 8px 8px 0 !important; border: 1px solid var(--border-color); padding: 0.4rem 0.6rem; font-size: 0.9rem; font-weight: 600; color: var(--text-primary); transition: all 0.2s; }
        .nominal-display:focus { border-color: var(--text-primary); box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08); outline: none; }
        .nominal-display.is-invalid { border-color: #ef4444 !important; background-color: #fef2f2; }
        .nominal-display.is-valid { border-color: #10b981 !important; }

        .btn-cairkan { background-color: var(--text-primary); color: white; border: none; padding: 0.45rem 0.9rem; border-radius: 8px; font-weight: 600; font-size: 0.82rem; white-space: nowrap; cursor: pointer; transition: all 0.15s ease; }
        .btn-cairkan:hover:not(:disabled) { background-color: #334155; transform: translateY(-1px); }
        .btn-cairkan:disabled { opacity: 0.45; cursor: not-allowed; }

        .btn-full { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; transition: all 0.15s ease; flex-shrink: 0; }
        .btn-full:hover { background: #fef3c7; transform: translateY(-1px); }

        .btn-tolak { background-color: #ef4444; color: white; border: none; padding: 0.45rem 0.75rem; border-radius: 8px; font-weight: 600; font-size: 0.82rem; white-space: nowrap; cursor: pointer; transition: all 0.15s ease; }
        .btn-tolak:hover { background-color: #dc2626; transform: translateY(-1px); }

        .btn-act { border:none; padding:0.42rem 0.75rem; border-radius:8px; font-weight:600; font-size:0.78rem; color:white; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; white-space:nowrap; transition:all 0.15s ease; }
        .btn-act:hover { transform:translateY(-1px); filter:brightness(1.08); color:white; }
        .btn-view { background-color:#3b82f6; }

        .empty-card { background: var(--bg-card); border: 1px dashed var(--border-color); border-radius: 16px; padding: 3rem 1rem; }

        /* ===== 🎯 MODE KOMPAK ===== */
        #bsList.compact { --bs-gutter-y: .5rem; }
        #bsList.compact > .bs-item { flex: 0 0 100%; max-width: 100%; width: 100%; }
        #bsList.compact .bs-card { flex-direction: row; align-items: center; flex-wrap: wrap; gap: .4rem 1.5rem; padding: .55rem 1rem; border-left-width: 3px; border-radius: 12px; }
        #bsList.compact .bs-card:hover { transform: none; }
        #bsList.compact .bs-card-head { border-bottom: none; padding: 0; align-items: center; flex: 1 1 300px; min-width: 250px; }
        #bsList.compact .bs-card-head > div:first-child { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; min-width: 0; }
        #bsList.compact .bs-card-head .mt-2 { margin-top: 0 !important; }
        #bsList.compact .doc-no { font-size: .88rem; }
        #bsList.compact .head-nominal { text-align: right; }
        #bsList.compact .head-nominal .meta-label { display: none; }
        #bsList.compact .head-nominal .amount { font-size: .95rem; }
        #bsList.compact .head-nominal .meta-value.muted { font-size: .68rem; }
        #bsList.compact .bs-card-body { padding: 0; gap: .25rem; flex: 1 1 320px; min-width: 240px; }
        #bsList.compact .meta-grid { display: flex; flex-wrap: wrap; align-items: center; }
        #bsList.compact .meta-grid > div { display: flex; align-items: center; gap: .4rem; padding: 0 .75rem; border-left: 1px solid var(--border-color); }
        #bsList.compact .meta-grid > div:first-child { border-left: none; padding-left: 0; }
        #bsList.compact .meta-grid > div:last-child { display: none; }
        #bsList.compact .meta-label { display: none; }
        #bsList.compact .meta-value { font-size: .78rem; }
        #bsList.compact .avatar { width: 22px; height: 22px; font-size: .6rem; }
        #bsList.compact .ket-box { padding: .12rem .6rem; font-size: .72rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        #bsList.compact .ket-box br { display: none; }
        #bsList.compact .bs-card-foot { padding: 0; background: transparent; border-top: none; margin-left: auto; flex: 0 0 auto; }

        @media (max-width: 991.98px) {
            #bsList.compact .bs-card { flex-direction: column; align-items: stretch; gap: .6rem; }
            #bsList.compact .bs-card-head { border-bottom: 1px dashed var(--border-color); padding-bottom: .55rem; }
            #bsList.compact .bs-card-foot { margin-left: 0; padding-top: .55rem; border-top: 1px solid var(--border-color); }
            #bsList.compact .ket-box { white-space: normal; }
        }

        /* ===== 🎯 BATCH SELECTION BAR (Floating) ===== */
        .batch-select-bar {
            display: flex; align-items: center; gap: 0.6rem;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 2px 8px -2px rgba(15,23,42,0.06);
        }
        .batch-select-bar .form-check { margin: 0; display: flex; align-items: center; gap: 0.5rem; }
        .batch-select-bar .form-check-input { width: 18px; height: 18px; cursor: pointer; }
        .batch-select-bar .form-check-input:checked { background-color: #2563eb; border-color: #2563eb; }
        .batch-select-bar .form-check-input:focus { box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .batch-select-bar .form-check-label { font-weight: 600; font-size: 0.88rem; cursor: pointer; user-select: none; }
        .batch-counter { font-size: 0.82rem; color: var(--text-secondary); font-weight: 500; }
        .batch-counter strong { color: #2563eb; }

        /* ===== 🎯 FLOATING ACTION BAR ===== */
        .floating-action-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: white;
            border-top: 1px solid var(--border-color);
            box-shadow: 0 -8px 24px -8px rgba(15,23,42,0.15);
            padding: 0.9rem 1.25rem;
            z-index: 1030;
            transform: translateY(110%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .floating-action-bar.visible { transform: translateY(0); }
        .floating-action-inner {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .fab-info { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
        .fab-count {
            background: #2563eb; color: white;
            width: 36px; height: 36px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.95rem;
        }
        .fab-text { font-size: 0.88rem; color: var(--text-secondary); }
        .fab-text strong { color: var(--text-primary); font-size: 1rem; }
        .fab-total {
            padding: 0.5rem 1rem;
            background: #eff6ff;
            border-radius: 10px;
            font-weight: 800;
            color: #1d4ed8;
            font-size: 1.05rem;
            font-variant-numeric: tabular-nums;
        }
        .fab-actions { display: flex; gap: 0.5rem; }
        .btn-fab-clear {
            background: transparent; border: 1px solid var(--border-color);
            color: var(--text-secondary); padding: 0.6rem 1.1rem; border-radius: 10px;
            font-weight: 600; cursor: pointer; font-size: 0.88rem;
        }
        .btn-fab-clear:hover { background: #f1f5f9; color: var(--text-primary); }
        .btn-fab-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white; border: none;
            padding: 0.65rem 1.5rem; border-radius: 10px;
            font-weight: 700; cursor: pointer; font-size: 0.9rem;
            box-shadow: 0 4px 12px -2px rgba(16,185,129,0.4);
            display: inline-flex; align-items: center; gap: 0.5rem;
            transition: all 0.15s ease;
        }
        .btn-fab-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 16px -2px rgba(16,185,129,0.5); }
        .btn-fab-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        @media (max-width: 576px) {
            .floating-action-inner { flex-direction: column; align-items: stretch; text-align: center; }
            .fab-info { justify-content: center; }
            .fab-actions { justify-content: center; width: 100%; }
            .btn-fab-clear, .btn-fab-submit { flex: 1; }
        }
    </style>
    @include('partials.mobile-assets')
</head>
<body class="py-4 py-md-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1300px;">

        {{-- ================= HEADER ================= --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">Pencairan BS Finance</h1>
                <p class="page-subtitle">Cairkan pengajuan BS yang telah disetujui atasan.</p>
            </div>
            <div class="d-flex gap-2">
                {{-- Tombol laporan disembunyikan sementara.
                <a href="{{ route('laporan-bs.index') }}" class="btn-nav btn-nav-dark">
                    <i class="bi bi-bar-chart-line me-1"></i> Laporan Pertanggungjawaban
                </a>
                --}}
                <a href="{{ route('dashboard.transaksi') }}" class="btn-nav">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius: 12px;">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 12px;">❌ {{ session('error') }}</div>
        @endif

        @php
            $bulanId = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
            $formatPeriode = fn($p) => ($bulanId[(int)substr($p,5,2)] ?? '-') . ' ' . substr($p,0,4);
            $currentPeriod = \App\Support\AppTime::now()->format('Y-m');

            $shortDate = function ($dt) {
                if (!$dt) return '—';
                try {
                    return \Illuminate\Support\Carbon::parse($dt)
                        ->timezone('Asia/Jakarta')
                        ->format('d M Y, H:i');
                } catch (\Throwable $e) {
                    return '—';
                }
            };

            $pending   = $data->where('status', 'APPROVED_ATASAN')->count();
            $disbursed = $data->where('status', 'DISBURSED')->count();
            $rejected  = $data->where('status', 'REJECTED_FINANCE')->count();
            $totalCair = $data->where('status', 'DISBURSED')->sum('nominal_approved');
        @endphp

        {{-- ================= STAT CARDS ================= --}}
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fffbeb; color:#d97706;"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="stat-label">Menunggu Cair</div>
                        <div class="stat-value" style="color:#d97706;">{{ $pending }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <div class="stat-label">Sudah Cair</div>
                        <div class="stat-value" style="color:#059669;">{{ $disbursed }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-x-octagon"></i></div>
                    <div>
                        <div class="stat-label">Ditolak</div>
                        <div class="stat-value" style="color:#dc2626;">{{ $rejected }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#eff6ff; color:#2563eb;"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="stat-label">Total Cair</div>
                        <div class="stat-value money" style="font-size:1.1rem; color:#2563eb;">Rp {{ number_format($totalCair, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TOOLBAR PERIODE ================= --}}
        <div class="toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-0" style="font-size:1.1rem;">
                    {{ $formatPeriode($selectedPeriod) }}
                    @if($selectedPeriod === $currentPeriod)
                        <span class="current-badge">BULAN INI</span>
                    @endif
                </h4>
                <p class="text-muted small mb-0">
                    Menampilkan <strong>{{ $data->count() }}</strong> pengajuan pada periode ini.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="btn-group btn-group-sm me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary density-btn active" data-density="comfortable" title="Kartu nyaman (default)">
                        <i class="bi bi-grid-1x2-fill"></i><span class="d-none d-md-inline ms-1">Nyaman</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary density-btn" data-density="compact" title="Baris padat">
                        <i class="bi bi-list-ul"></i><span class="d-none d-md-inline ms-1">Kompak</span>
                    </button>
                </div>
                <label for="periodSelect" class="small fw-semibold text-muted mb-0 text-nowrap">
                    <i class="bi bi-calendar3 me-1"></i>Periode:
                </label>
                <select id="periodSelect" class="form-select form-select-sm" style="min-width:210px;">
                    @foreach($availablePeriods as $p)
                        <option value="{{ $p }}" {{ $selectedPeriod === $p ? 'selected' : '' }}>
                            {{ $formatPeriode($p) }}{{ $p === $currentPeriod ? ' (Bulan ini)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- 🎯 BATCH SELECT BAR (Select All + Counter) --}}
        @if($pending > 0)
            <div class="batch-select-bar">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAllPending">
                    <label class="form-check-label" for="selectAllPending">
                        Pilih semua yang menunggu cair
                    </label>
                </div>
                <span class="batch-counter ms-auto">
                    Terpilih: <strong><span id="batchCount">0</span> dokumen</strong>
                </span>
            </div>
        @endif

        {{-- ================= FILTER CHIPS STATUS ================= --}}
        @if($data->isNotEmpty())
            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                <span class="small fw-semibold text-muted me-1"><i class="bi bi-funnel me-1"></i>Filter:</span>
                <button type="button" class="chip-filter active" data-filter="all">
                    Semua <span class="chip-count">{{ $data->count() }}</span>
                </button>
                <button type="button" class="chip-filter" data-filter="APPROVED_ATASAN">
                    Menunggu Cair <span class="chip-count">{{ $pending }}</span>
                </button>
                <button type="button" class="chip-filter" data-filter="DISBURSED">
                    Sudah Cair <span class="chip-count">{{ $disbursed }}</span>
                </button>
                <button type="button" class="chip-filter" data-filter="REJECTED_FINANCE">
                    Ditolak <span class="chip-count">{{ $rejected }}</span>
                </button>
            </div>
        @endif

        {{-- ================= 🎯 CARD LIST ================= --}}
        <div class="row g-3" id="bsList">
            @forelse($data as $bs)
                @php
                    $statusColors = [
                        'APPROVED_ATASAN'  => 'bg-warning text-dark',
                        'DISBURSED'        => 'bg-success text-white',
                        'REJECTED_FINANCE' => 'bg-danger text-white',
                    ];
                    $cair         = $bs->financeApprovals->first();
                    $tipe         = $bs->tipe_pengajuan ?? 'operasional_sales';
                    $isPerjalanan = ($tipe === 'perjalanan_dinas');
                    $dateCreated  = $shortDate($bs->created_at);
                    $dateDecision = $bs->status === 'DISBURSED' && $cair
                        ? $shortDate($cair->created_at)
                        : ($bs->status === 'REJECTED_FINANCE'
                            ? $shortDate($bs->logs->firstWhere('activity_code', 'REJECTED_FINANCE')?->created_at)
                            : '—');
                    $canSelect    = $bs->status === 'APPROVED_ATASAN';
                @endphp
                <div class="col-12 col-lg-6 bs-item" data-status="{{ $bs->status }}"
                     data-id="{{ $bs->id }}"
                     data-nominal="{{ $bs->nominal_pengajuan }}"
                     data-selectable="{{ $canSelect ? '1' : '0' }}">
                    <div class="bs-card status-{{ strtolower($bs->status) }}">

                        {{-- HEAD --}}
                        <div class="bs-card-head">
                            <div class="head-left">
                                {{-- 🎯 CHECKBOX SELECT --}}
                                @if($canSelect)
                                    <div class="bs-select-check" role="checkbox" aria-checked="false" tabindex="0"
                                         data-bs-id="{{ $bs->id }}"
                                         data-bs-nominal="{{ $bs->nominal_pengajuan }}"
                                         title="Pilih untuk dicairkan penuh"></div>
                                @endif
                                <div class="head-content min-w-0">
                                    <div class="doc-no text-break">{{ $bs->document_no }}</div>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @if($isPerjalanan)
                                            <span class="badge-tipe badge-tipe-dinas" title="Perjalanan Dinas dengan tim/grup">
                                                <i class="bi bi-airplane-fill"></i> Perjalanan Dinas
                                            </span>
                                        @else
                                            <span class="badge-tipe badge-tipe-operasional" title="Operasional Sales perorangan">
                                                <i class="bi bi-briefcase-fill"></i> Operasional Sales
                                            </span>
                                        @endif
                                        <span class="badge-status {{ $statusColors[$bs->status] ?? 'bg-secondary text-white' }}">
                                            {{ str_replace('_', ' ', $bs->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="head-nominal">
                                <div class="meta-label">Nominal Pengajuan</div>
                                <div class="amount">Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</div>
                                <div class="meta-value muted" style="font-size:0.75rem;">
                                    Sisa BS: Rp {{ number_format($bs->nominal_sisa_bs_snapshot, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- BODY --}}
                        <div class="bs-card-body">
                            <div class="meta-grid">
                                <div>
                                    <div class="meta-label">User</div>
                                    <div class="meta-value d-flex align-items-center gap-2">
                                        <span class="avatar">{{ strtoupper(substr($bs->user->nama ?? '?', 0, 1)) }}</span>
                                        <span class="text-truncate">{{ $bs->user->nama ?? '-' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="meta-label">Tgl Pengajuan</div>
                                    <div class="meta-value muted" title="{{ \App\Support\AppTime::format($bs->created_at) }}">
                                        {{ $dateCreated }}
                                    </div>
                                </div>
                                <div>
                                    <div class="meta-label">Tgl Keputusan</div>
                                    <div class="meta-value muted">
                                        @if($bs->status === 'DISBURSED' && $cair)
                                            <span title="{{ \App\Support\AppTime::format($cair->created_at) }}">{{ $dateDecision }}</span>
                                        @elseif($bs->status === 'REJECTED_FINANCE')
                                            <span title="{{ \App\Support\AppTime::format($bs->logs->firstWhere('activity_code', 'REJECTED_FINANCE')?->created_at) }}">{{ $dateDecision }}</span>
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="meta-label">ID Dokumen</div>
                                    <div class="meta-value muted">#{{ $bs->id }}</div>
                                </div>
                            </div>

                            @if($bs->status === 'APPROVED_ATASAN')
                                <div class="ket-box ket-pending">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    Menunggu pencairan oleh Finance.
                                    Maks cair: <strong>Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</strong>
                                </div>
                            @elseif($bs->status === 'DISBURSED' && $cair)
                                <div class="ket-box ket-ok" title="Cair Rp {{ number_format($cair->nominal_approved, 0, ',', '.') }}">
                                    <div class="fw-bold" style="font-size:0.9rem;">
                                        <i class="bi bi-cash-coin me-1"></i>
                                        Cair Rp {{ number_format($cair->nominal_approved, 0, ',', '.') }}
                                    </div>
                                    <div class="mt-1">
                                        <i class="bi bi-calendar-check me-1"></i>{{ $dateDecision }}
                                        <br>
                                        <i class="bi bi-person-badge me-1"></i>{{ $cair->financeUser->nama ?? '-' }}
                                        · No. {{ $cair->document_no }}
                                    </div>
                                </div>
                            @elseif($bs->status === 'REJECTED_FINANCE')
                                <div class="ket-box ket-reject" title="{{ $bs->rejection_reason ?? 'Tidak ada alasan.' }}">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    {{ $bs->rejection_reason ?? 'Tidak ada alasan.' }}
                                </div>
                            @endif
                        </div>

                        {{-- FOOT --}}
                        <div class="bs-card-foot">
                            @if($bs->status === 'APPROVED_ATASAN')
                                <form action="{{ route('approval-finance.disburse', $bs->id) }}" method="POST"
                                      class="nominal-form d-flex align-items-center gap-2 flex-wrap"
                                      data-max="{{ $bs->nominal_pengajuan }}">
                                    @csrf
                                    <div class="input-group input-group-sm" style="width: 175px;">
                                        <span class="input-group-text" style="background:#f8fafc; border-color:var(--border-color); font-size:0.82rem;">Rp</span>
                                        <input type="text" class="form-control nominal-display"
                                               placeholder="0" autocomplete="off" inputmode="numeric"
                                               style="text-align:right; font-variant-numeric:tabular-nums;">
                                        <input type="hidden" name="nominal_approved" class="nominal-hidden">
                                    </div>
                                    <button type="button" class="btn-full" title="Isi penuh sesuai nominal pengajuan">
                                        <i class="bi bi-lightning-charge-fill"></i>
                                    </button>
                                    <button type="submit" class="btn-cairkan" disabled>
                                        <i class="bi bi-cash-coin me-1"></i> Cairkan
                                    </button>
                                </form>
                                <button type="button" class="btn-tolak"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal"
                                        data-action="{{ route('approval-finance.reject', $bs->id) }}"
                                        data-docno="{{ $bs->document_no }}" title="Tolak pencairan">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            @endif
                            <a href="{{ route('approval-finance.detail', $bs->id) }}" class="btn-act btn-view ms-auto" title="Lihat Detail">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </div>

                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-card text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Tidak ada pengajuan pada <strong>{{ $formatPeriode($selectedPeriod) }}</strong>.
                    </div>
                </div>
            @endforelse
        </div>

        <div id="filterEmpty" class="d-none text-center text-muted py-4 small">
            <i class="bi bi-funnel fs-4 d-block mb-2"></i>
            Tidak ada dokumen dengan status tersebut pada periode ini.
        </div>
    </div>

    {{-- 🎯 FLOATING ACTION BAR --}}
    <div class="floating-action-bar" id="fabBar">
        <div class="floating-action-inner">
            <div class="fab-info">
                <span class="fab-count" id="fabCount">0</span>
                <div class="fab-text">
                    <div><strong id="fabCountText">0</strong> pengajuan terpilih</div>
                    <div style="font-size: 0.78rem;">Akan dicairkan dengan <b>nominal penuh</b></div>
                </div>
                <div class="fab-total" id="fabTotal">Rp 0</div>
            </div>
            <div class="fab-actions">
                <button type="button" class="btn-fab-clear" id="fabClear">
                    <i class="bi bi-x-circle"></i> Batal Pilih
                </button>
                <form id="batchDisburseForm" action="{{ route('approval-finance.batch-disburse') }}" method="POST" style="display: contents;">
                    @csrf
                    <div id="batchHiddenInputs"></div>
                    <button type="submit" class="btn-fab-submit">
                        <i class="bi bi-check2-all"></i> Cairkan Semua Terpilih
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL ALASAN PENOLAKAN ================= --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-content" style="border-radius: 16px; border: 1px solid var(--border-color);">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold">Tolak Pencairan BS</h5>
                            <p class="text-secondary mb-0 mt-1" style="font-size: 0.85rem;">
                                Dokumen: <span id="rejectDocNo" class="fw-semibold text-primary">-</span>
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary mb-3" style="font-size: 0.9rem;">
                            Mohon berikan alasan mengapa pencairan BS ini ditolak.
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason"
                                      rows="4" required maxlength="1000"
                                      placeholder="Contoh: Dokumen pendukung belum lengkap..."></textarea>
                            <small class="text-muted" id="charCount">0 / 1000</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i> Ya, Tolak Pencairan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ===== Dropdown periode =====
            const periodSelect = document.getElementById('periodSelect');
            if (periodSelect) {
                periodSelect.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('periode', this.value);
                    window.location.href = url.toString();
                });
            }

            // ===== Toggle densitas =====
            const bsList      = document.getElementById('bsList');
            const densityBtns = document.querySelectorAll('.density-btn');
            const applyDensity = (mode) => {
                if (!bsList) return;
                bsList.classList.toggle('compact', mode === 'compact');
                densityBtns.forEach(b => b.classList.toggle('active', b.dataset.density === mode));
                try { localStorage.setItem('bsListDensity', mode); } catch (e) {}
            };
            densityBtns.forEach(b => b.addEventListener('click', () => applyDensity(b.dataset.density)));
            let savedDensity = 'comfortable';
            try { savedDensity = localStorage.getItem('bsListDensity') === 'compact' ? 'compact' : 'comfortable'; } catch (e) {}
            applyDensity(savedDensity);

            // ===== Filter chip status =====
            const chips = document.querySelectorAll('.chip-filter');
            const items = document.querySelectorAll('.bs-item');
            const filterEmpty = document.getElementById('filterEmpty');
            chips.forEach(chip => {
                chip.addEventListener('click', function () {
                    chips.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    const f = this.dataset.filter;
                    let visible = 0;
                    items.forEach(it => {
                        const show = (f === 'all') || (it.dataset.status === f);
                        it.classList.toggle('d-none', !show);
                        if (show) visible++;
                    });
                    if (filterEmpty) filterEmpty.classList.toggle('d-none', visible > 0);
                });
            });

            // ===== Modal reject logic =====
            const rejectModal = document.getElementById('rejectModal');
            const rejectForm  = document.getElementById('rejectForm');
            const docNoEl     = document.getElementById('rejectDocNo');
            const reasonInput = document.getElementById('rejection_reason');
            const charCountEl = document.getElementById('charCount');
            if (rejectModal) {
                rejectModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    rejectForm.action       = button.getAttribute('data-action');
                    docNoEl.textContent     = button.getAttribute('data-docno');
                    reasonInput.value       = '';
                    charCountEl.textContent = '0 / 1000';
                });
            }
            if (reasonInput) {
                reasonInput.addEventListener('input', function () {
                    charCountEl.textContent = this.value.length + ' / 1000';
                });
            }

            // ===== Auto-format nominal per card =====
            document.querySelectorAll('.nominal-form').forEach(form => {
                const display = form.querySelector('.nominal-display');
                const hidden  = form.querySelector('.nominal-hidden');
                const btn     = form.querySelector('.btn-cairkan');
                const fullBtn = form.querySelector('.btn-full');
                const maxVal  = parseInt(form.getAttribute('data-max')) || 999999999;

                display.addEventListener('input', function () {
                    let raw = this.value.replace(/[^\d]/g, '');
                    if (raw === '') {
                        this.value = ''; hidden.value = ''; btn.disabled = true;
                        this.classList.remove('is-valid', 'is-invalid');
                        return;
                    }
                    let num = parseInt(raw, 10);
                    if (num > maxVal) {
                        num = maxVal;
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                        btn.disabled = true;
                    } else {
                        this.classList.remove('is-invalid');
                        if (num > 0) this.classList.add('is-valid');
                        btn.disabled = false;
                    }
                    this.value = num.toLocaleString('id-ID');
                    hidden.value = num;
                });

                display.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    this.value = paste.replace(/[^\d]/g, '');
                    this.dispatchEvent(new Event('input'));
                });

                if (fullBtn) {
                    fullBtn.addEventListener('click', function () {
                        display.value = String(maxVal);
                        display.dispatchEvent(new Event('input'));
                        display.focus();
                    });
                }

                form.addEventListener('submit', function (e) {
                    if (!hidden.value || parseInt(hidden.value) < 1) {
                        e.preventDefault();
                        display.classList.add('is-invalid');
                        display.focus();
                    }
                });
            });

            // ===========================================================
            // 🎯 BATCH SELECTION LOGIC
            // ===========================================================
            const selectAllCheckbox = document.getElementById('selectAllPending');
            const fabBar = document.getElementById('fabBar');
            const fabCount = document.getElementById('fabCount');
            const fabCountText = document.getElementById('fabCountText');
            const fabTotal = document.getElementById('fabTotal');
            const fabClear = document.getElementById('fabClear');
            const batchForm = document.getElementById('batchDisburseForm');
            const batchHiddenInputs = document.getElementById('batchHiddenInputs');
            const batchCountEl = document.getElementById('batchCount');

            const selectableChecks = document.querySelectorAll('.bs-select-check');
            const selected = new Set();

            const formatRupiah = (num) => 'Rp ' + num.toLocaleString('id-ID');

            function updateSelectionUI() {
                const count = selected.size;
                let total = 0;
                selected.forEach(id => {
                    const check = document.querySelector(`.bs-select-check[data-bs-id="${id}"]`);
                    if (check) total += parseInt(check.dataset.bsNominal) || 0;
                });

                // Update FAB bar
                if (count > 0) {
                    fabBar.classList.add('visible');
                    fabCount.textContent = count;
                    fabCountText.textContent = count;
                    fabTotal.textContent = formatRupiah(total);
                } else {
                    fabBar.classList.remove('visible');
                }

                if (batchCountEl) batchCountEl.textContent = count;

                // Update "select all" state
                if (selectAllCheckbox) {
                    const totalSelectable = selectableChecks.length;
                    if (count === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else if (count === totalSelectable) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                }
            }

            function toggleSelect(check, forceState = null) {
                const bsId = parseInt(check.dataset.bsId);
                const card = check.closest('.bs-card');
                const shouldSelect = forceState !== null ? forceState : !selected.has(bsId);

                if (shouldSelect) {
                    selected.add(bsId);
                    check.classList.add('checked');
                    check.setAttribute('aria-checked', 'true');
                    if (card) card.classList.add('selected');
                } else {
                    selected.delete(bsId);
                    check.classList.remove('checked');
                    check.setAttribute('aria-checked', 'false');
                    if (card) card.classList.remove('selected');
                }
                updateSelectionUI();
            }

            function clearSelection() {
                selectableChecks.forEach(check => toggleSelect(check, false));
            }

            // Event: click pada checkbox
            selectableChecks.forEach(check => {
                check.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleSelect(check);
                });
                // Keyboard accessibility (Space / Enter)
                check.addEventListener('keydown', (e) => {
                    if (e.key === ' ' || e.key === 'Enter') {
                        e.preventDefault();
                        toggleSelect(check);
                    }
                });
            });

            // Event: klik card juga toggle (kecuali link / button)
            document.querySelectorAll('.bs-card').forEach(card => {
                card.addEventListener('click', (e) => {
                    // Jangan toggle jika klik link, button, atau form input
                    if (e.target.closest('a, button, input, textarea, label, .btn-act, .btn-tolak, .btn-cairkan, .btn-full, .nominal-display, .nominal-form')) return;
                    const check = card.querySelector('.bs-select-check');
                    if (check) toggleSelect(check);
                });
            });

            // Event: Select All
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    const shouldSelect = this.checked;
                    selectableChecks.forEach(check => toggleSelect(check, shouldSelect));
                });
            }

            // Event: Clear button
            if (fabClear) {
                fabClear.addEventListener('click', clearSelection);
            }

            // Event: Submit batch
            if (batchForm) {
                batchForm.addEventListener('submit', function (e) {
                    if (selected.size === 0) {
                        e.preventDefault();
                        return;
                    }

                    const message = `Anda akan mencairkan ${selected.size} pengajuan BS dengan nominal penuh.\n\n` +
                                    `Total nominal: ${fabTotal.textContent}\n\n` +
                                    `Tindakan ini tidak dapat dibatalkan. Lanjutkan?`;
                    if (!confirm(message)) {
                        e.preventDefault();
                        return;
                    }

                    // Generate hidden inputs: ids[]
                    batchHiddenInputs.innerHTML = '';
                    selected.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        batchHiddenInputs.appendChild(input);
                    });
                });
            }

            // Inisialisasi
            updateSelectionUI();
        });
    </script>
</body>
</html>
