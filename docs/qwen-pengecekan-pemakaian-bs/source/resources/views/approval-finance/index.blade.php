<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        }

        /* ===== HEADER ===== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .page-title { font-weight: 800; font-size: 1.6rem; letter-spacing: -0.02em; margin: 0; }
        .page-subtitle { color: var(--text-secondary); font-size: 0.9rem; margin: 0.3rem 0 0; }
        .btn-nav {
            background-color: white; border: 1px solid var(--border-color);
            color: var(--text-secondary); font-weight: 500; font-size: 0.875rem;
            padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; white-space: nowrap;
        }
        .btn-nav:hover { background-color: #f1f5f9; color: var(--text-primary); }
        .btn-nav-dark { background: var(--text-primary); color: #fff; border: none; }
        .btn-nav-dark:hover { background: #334155; color: #fff; }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            height: 100%;
            transition: all 0.2s ease;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px -6px rgba(15,23,42,0.08); }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; flex-shrink: 0;
        }
        .stat-label {
            font-size: 0.72rem; font-weight: 700; color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.15rem;
        }
        .stat-value {
            font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums; line-height: 1.2;
        }

        /* ===== TOOLBAR ===== */
        .toolbar {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 0.9rem 1.25rem;
            margin: 1rem 0;
        }
        .toolbar .form-select {
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .toolbar .form-select:focus {
            border-color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(15,23,42,0.08);
        }
        .current-badge {
            display: inline-block; background: #10b981; color: white;
            font-size: 0.65rem; font-weight: 700; padding: 0.25em 0.7em;
            border-radius: 999px; margin-left: 0.5rem; vertical-align: middle;
        }

        /* ===== TABLE ===== */
        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }
        .table-custom { border-collapse: separate; border-spacing: 0; width: 100%; }
        .table-custom th {
            background-color: #f8fafc; color: var(--text-secondary);
            font-weight: 700; font-size: 0.72rem; text-transform: uppercase;
            letter-spacing: 0.06em; border-bottom: 1px solid var(--border-color);
            padding: 0.9rem 1rem; white-space: nowrap;
        }
        .table-custom td {
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 1rem; font-size: 0.92rem; vertical-align: middle;
        }
        .table-custom tbody tr { transition: background 0.15s ease; }
        .table-custom tbody tr:hover { background-color: #f8fafc; }
        .table-custom tbody tr:last-child td { border-bottom: none; }

        .money { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .info-sub { font-size: 0.78rem; color: var(--text-secondary); line-height: 1.5; }

        .avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: #eef2ff; color: #4f46e5;
            font-weight: 700; font-size: 0.72rem;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* ===== BADGES ===== */
        .badge-status {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.38em 0.75em; border-radius: 999px;
            font-weight: 700; font-size: 0.68rem;
            text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;
        }
        .badge-status::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: currentColor; opacity: 0.9;
        }

                /* ===== 🎯 BADGE TIPE PENGAJUAN (paritas dengan Approval Atasan) ===== */
        .badge-tipe {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.35em 0.7em; border-radius: 999px;
            font-weight: 700; font-size: 0.68rem;
            text-transform: uppercase; letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .badge-tipe-operasional { background: #eff6ff; color: #1d4ed8; }
        .badge-tipe-dinas       { background: #ecfdf5; color: #047857; }

        /* ===== FORM PENCAIRAN ===== */
        .nominal-display {
            border-radius: 0 8px 8px 0 !important;
            border: 1px solid var(--border-color);
            padding: 0.4rem 0.6rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        .nominal-display:focus {
            border-color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
            outline: none;
        }
        .nominal-display.is-invalid { border-color: #ef4444 !important; background-color: #fef2f2; }
        .nominal-display.is-valid { border-color: #10b981 !important; }

        .btn-cairkan {
            background-color: var(--text-primary); color: white; border: none;
            padding: 0.45rem 0.9rem; border-radius: 8px; font-weight: 600;
            font-size: 0.82rem; white-space: nowrap; cursor: pointer; transition: all 0.15s ease;
        }
        .btn-cairkan:hover:not(:disabled) { background-color: #334155; transform: translateY(-1px); }
        .btn-cairkan:disabled { opacity: 0.45; cursor: not-allowed; }

        .btn-full {
            background: #fffbeb; color: #d97706; border: 1px solid #fde68a;
            width: 32px; height: 32px; border-radius: 8px; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.85rem; transition: all 0.15s ease; flex-shrink: 0;
        }
        .btn-full:hover { background: #fef3c7; transform: translateY(-1px); }

        .btn-tolak {
            background-color: #ef4444; color: white; border: none;
            padding: 0.45rem 0.75rem; border-radius: 8px; font-weight: 600;
            font-size: 0.82rem; white-space: nowrap; cursor: pointer; transition: all 0.15s ease;
        }
        .btn-tolak:hover { background-color: #dc2626; transform: translateY(-1px); }


        .btn-act { border:none; padding:0.42rem 0.75rem; border-radius:8px; font-weight:600; font-size:0.78rem; color:white; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; white-space:nowrap; transition:all 0.15s ease; }
.btn-act:hover { transform:translateY(-1px); filter:brightness(1.08); color:white; }
.btn-view { background-color:#3b82f6; }
    </style>
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
                <a href="{{ route('laporan-bs.index') }}" class="btn-nav btn-nav-dark">
                    <i class="bi bi-bar-chart-line me-1"></i> Laporan Pertanggungjawaban
                </a>
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

            $pending   = $data->where('status', 'APPROVED_ATASAN')->count();
            $disbursed = $data->where('status', 'DISBURSED')->count();
            $rejected  = $data->where('status', 'REJECTED_FINANCE')->count();
            $totalCair = $data->where('status', 'DISBURSED')->sum('nominal_approved');
        @endphp

        {{-- ================= STAT CARDS ================= --}}
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fffbeb; color:#d97706;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-label">Menunggu Cair</div>
                        <div class="stat-value" style="color:#d97706;">{{ $pending }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ecfdf5; color:#059669;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Sudah Cair</div>
                        <div class="stat-value" style="color:#059669;">{{ $disbursed }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef2f2; color:#dc2626;">
                        <i class="bi bi-x-octagon"></i>
                    </div>
                    <div>
                        <div class="stat-label">Ditolak</div>
                        <div class="stat-value" style="color:#dc2626;">{{ $rejected }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#eff6ff; color:#2563eb;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
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
            <div class="d-flex align-items-center gap-2">
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

                {{-- ================= TABEL ================= --}}
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Doc No</th>
                            <th>Tipe</th>               {{-- 🎯 BARU --}}
                            <th>Tgl Pengajuan</th>      {{-- 🎯 BARU --}}
                            <th>Tgl Keputusan</th>      {{-- 🎯 BARU --}}
                            <th>User</th>
                            <th>Nominal Pengajuan</th>
                            <th>Sisa BS (Snap)</th>     {{-- 🎯 BARU --}}
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $bs)
                            @php
                                $statusColors = [
                                    'APPROVED_ATASAN'  => 'bg-warning text-dark',
                                    'DISBURSED'        => 'bg-success text-white',
                                    'REJECTED_FINANCE' => 'bg-danger text-white',
                                ];
                                $cair = $bs->financeApprovals->first();

                                // 🎯 BARU: tipe pengajuan (fallback untuk data lama)
                                $tipe         = $bs->tipe_pengajuan ?? 'operasional_sales';
                                $isPerjalanan = ($tipe === 'perjalanan_dinas');
                            @endphp
                            <tr>
                                <td class="fw-semibold text-nowrap">{{ $bs->document_no }}</td>

                                {{-- 🎯 BARU: Kolom Tipe --}}
                                <td>
                                    @if($isPerjalanan)
                                        <span class="badge-tipe badge-tipe-dinas" title="Perjalanan Dinas dengan tim/grup">
                                            <i class="bi bi-airplane-fill"></i> Perjalanan Dinas
                                        </span>
                                    @else
                                        <span class="badge-tipe badge-tipe-operasional" title="Operasional Sales perorangan">
                                            <i class="bi bi-briefcase-fill"></i> Operasional Sales
                                        </span>
                                    @endif
                                </td>

                                {{-- 🎯 BARU: Kolom Tgl Pengajuan --}}
                                <td class="text-nowrap">{{ \App\Support\AppTime::format($bs->created_at) }}</td>

                                {{-- 🎯 BARU: Kolom Tgl Keputusan (keputusan Finance) --}}
                                <td class="text-nowrap">
                                    @if($bs->status === 'DISBURSED' && $cair)
                                        {{ \App\Support\AppTime::format($cair->created_at) }}
                                    @elseif($bs->status === 'REJECTED_FINANCE')
                                        {{ \App\Support\AppTime::format($bs->logs->firstWhere('activity_code', 'REJECTED_FINANCE')?->created_at) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ strtoupper(substr($bs->user->nama ?? '?', 0, 1)) }}</span>
                                        {{ $bs->user->nama ?? '-' }}
                                    </div>
                                </td>
                                <td class="money fw-bold text-primary">Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</td>

                                {{-- 🎯 BARU: Kolom Sisa BS (Snap) --}}
                                <td class="money text-muted">Rp {{ number_format($bs->nominal_sisa_bs_snapshot, 0, ',', '.') }}</td>

                                <td>
                                    <span class="badge-status {{ $statusColors[$bs->status] ?? 'bg-secondary text-white' }}">
                                        {{ str_replace('_', ' ', $bs->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($bs->status === 'APPROVED_ATASAN')
                                        <span class="info-sub"><i class="bi bi-hourglass-split me-1"></i> Menunggu pencairan Finance</span>
                                    @elseif($bs->status === 'DISBURSED' && $cair)
                                        <div class="money fw-bold" style="color:#059669;">
                                            Rp {{ number_format($cair->nominal_approved, 0, ',', '.') }}
                                        </div>
                                        <div class="info-sub">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            {{ \App\Support\AppTime::format($cair->created_at) }}
                                            <br>
                                            <i class="bi bi-person-badge me-1"></i> {{ $cair->financeUser->nama ?? '-' }}
                                            · No. {{ $cair->document_no }}
                                        </div>
                                    @elseif($bs->status === 'REJECTED_FINANCE')
                                        <div class="info-sub">
                                            <i class="bi bi-exclamation-triangle me-1 text-danger"></i>
                                            {{ $bs->rejection_reason ?? 'Tidak ada alasan.' }}
                                        </div>
                                    @else
                                        <span class="info-sub">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <div class="d-flex align-items-center gap-2 flex-nowrap">
                                            @if($bs->status === 'APPROVED_ATASAN')
                                                <form action="{{ route('approval-finance.disburse', $bs->id) }}" method="POST"
                                                      class="nominal-form d-inline-flex align-items-center gap-2"
                                                      data-max="{{ $bs->nominal_pengajuan }}">
                                                    @csrf
                                                    <div class="input-group input-group-sm" style="width: 165px;">
                                                        <span class="input-group-text" style="background: #f8fafc; border-color: var(--border-color); font-size: 0.82rem;">Rp</span>
                                                        <input type="text" class="form-control nominal-display"
                                                               placeholder="0" autocomplete="off" inputmode="numeric"
                                                               style="text-align: right; font-variant-numeric: tabular-nums;">
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
                                            <a href="{{ route('approval-finance.detail', $bs->id) }}" class="btn-act btn-view" title="Lihat Detail Pengajuan">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </div>
                                        @if($bs->status === 'APPROVED_ATASAN')
                                            <div class="info-sub">
                                                Maks: <strong class="money">Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                                                           </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Tidak ada pengajuan pada <strong>{{ $formatPeriode($selectedPeriod) }}</strong>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>   {{-- <-- hanya 1x penutup container --}}

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
        // 🎯 Dropdown: ganti bulan → redirect ke URL baru
        document.getElementById('periodSelect')?.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('periode', this.value);
            window.location.href = url.toString();
        });

        // 🎯 Modal reject logic
        document.addEventListener('DOMContentLoaded', function () {
            const rejectModal = document.getElementById('rejectModal');
            const rejectForm  = document.getElementById('rejectForm');
            const docNoEl     = document.getElementById('rejectDocNo');
            const reasonInput = document.getElementById('rejection_reason');
            const charCountEl = document.getElementById('charCount');

            if (rejectModal) {
                rejectModal.addEventListener('show.bs.modal', function (event) {
                    const button    = event.relatedTarget;
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
        });

        // 🎯 Auto-format nominal dengan pemisah ribuan (5000 → 5.000)
        document.querySelectorAll('.nominal-form').forEach(form => {
            const display = form.querySelector('.nominal-display');
            const hidden  = form.querySelector('.nominal-hidden');
            const btn     = form.querySelector('.btn-cairkan');
            const fullBtn = form.querySelector('.btn-full');
            const maxVal  = parseInt(form.getAttribute('data-max')) || 999999999;

            display.addEventListener('input', function () {
                let raw = this.value.replace(/[^\d]/g, '');

                if (raw === '') {
                    this.value = '';
                    hidden.value = '';
                    btn.disabled = true;
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

            // 🎯 Tombol ⚡ isi penuh sesuai nominal pengajuan
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
    </script>
</body>
</html>
