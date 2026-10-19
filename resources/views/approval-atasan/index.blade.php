<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Approval BS Atasan</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-primary); min-height: 100vh; }

        .page-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem; }
        .page-title { font-weight: 800; font-size: 1.6rem; letter-spacing: -0.02em; margin: 0; }
        .page-subtitle { color: var(--text-secondary); font-size: 0.9rem; margin: 0.3rem 0 0; }
        .btn-nav {
            background-color: white; border: 1px solid var(--border-color);
            color: var(--text-secondary); font-weight: 500; font-size: 0.875rem;
            padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; white-space: nowrap;
        }
        .btn-nav:hover { background-color: #f1f5f9; color: var(--text-primary); }

        .stat-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 14px; padding: 1rem 1.25rem;
            display: flex; align-items: center; gap: 0.9rem; height: 100%;
            transition: all 0.2s ease;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px -6px rgba(15,23,42,0.08); }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
        .stat-label { font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.15rem; }
        .stat-value { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; line-height: 1.2; }

        .toolbar { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 0.9rem 1.25rem; margin: 1rem 0; }
        .toolbar .form-select { border: 1.5px solid var(--border-color); border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; }
        .toolbar .form-select:focus { border-color: var(--text-primary); box-shadow: 0 0 0 3px rgba(15,23,42,0.08); }
        .current-badge { display: inline-block; background: #10b981; color: white; font-size: 0.65rem; font-weight: 700; padding: 0.25em 0.7em; border-radius: 999px; margin-left: 0.5rem; vertical-align: middle; }

        .card-custom { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .table-custom { border-collapse: separate; border-spacing: 0; width: 100%; }
        .table-custom th { background-color: #f8fafc; color: var(--text-secondary); font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid var(--border-color); padding: 0.9rem 1rem; white-space: nowrap; }
        .table-custom td { border-bottom: 1px solid var(--border-color); padding: 0.85rem 1rem; font-size: 0.92rem; vertical-align: middle; }
        .table-custom tbody tr { transition: background 0.15s ease; }
        .table-custom tbody tr:hover { background-color: #f8fafc; }
        .table-custom tbody tr:last-child td { border-bottom: none; }

        .money { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .avatar { width: 30px; height: 30px; border-radius: 50%; background: #eef2ff; color: #4f46e5; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

        .badge-status { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.38em 0.75em; border-radius: 999px; font-weight: 700; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .badge-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: 0.9; }

        /* 🎯 BARU: Badge Tipe Pengajuan */
        .badge-tipe {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.35em 0.7em; border-radius: 999px;
            font-weight: 700; font-size: 0.68rem;
            text-transform: uppercase; letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .badge-tipe-operasional { background: #eff6ff; color: #1d4ed8; }
        .badge-tipe-dinas       { background: #ecfdf5; color: #047857; }

        .btn-act { border: none; padding: 0.42rem 0.75rem; border-radius: 8px; font-weight: 600; font-size: 0.78rem; color: white; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem; white-space: nowrap; transition: all 0.15s ease; }
        .btn-act:hover { transform: translateY(-1px); filter: brightness(1.08); color: white; }
        .btn-approve { background-color: #10b981; }
        .btn-reject  { background-color: #ef4444; }
        .btn-view    { background-color: #3b82f6; }
        .selection-toolbar { position: sticky; top: 0.5rem; z-index: 10; display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1.25rem; background: #fff; border: 1px solid var(--border-color); border-radius: 14px; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(15,23,42,0.04); }
        .selection-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-left: auto; }
        .selection-cell { position: sticky; left: 0; z-index: 1; background: #fff; width: 52px; text-align: center; }
        .table-custom th.selection-cell { z-index: 2; }
        .table-custom tr.is-selected, .table-custom tr.is-selected .selection-cell { background: #eff6ff; }
        .table-custom tr[data-selectable] { cursor: pointer; }
        .approval-select { width: 1.15rem; height: 1.15rem; cursor: pointer; vertical-align: middle; }
        #decisionBatchList { max-height: 150px; overflow-y: auto; font-size: 0.85rem; padding-left: 1.25rem; }
        @media (max-width: 575px) {
            .selection-actions { width: 100%; margin-left: 0; }
            .selection-actions .btn { flex: 1; }
        }
    </style>
    @include('partials.mobile-assets')
</head>
<body class="py-4 py-md-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1300px;">

        <div class="page-header">
            <div>
                <h1 class="page-title">Approval BS Atasan</h1>
                <p class="page-subtitle">Setujui atau tolak pengajuan Bon Sementara dari tim Anda.</p>
            </div>
            <a href="{{ route('dashboard.transaksi') }}" class="btn-nav">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius: 12px;">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius: 12px;">❌ {{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning mb-3" role="status">{{ session('warning') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger mb-3" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @php
            $bulanId = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
            $formatPeriode = fn($p) => ($bulanId[(int)substr($p,5,2)] ?? '-') . ' ' . substr($p,0,4);
            $currentPeriod = \App\Support\AppTime::now()->format('Y-m');

            $menunggu      = $data->where('status', 'PENDING_ATASAN')->count();
            $disetujui     = $data->whereIn('status', ['APPROVED_ATASAN', 'DISBURSED', 'SETTLED'])->count();
            $ditolak       = $data->whereIn('status', ['REJECTED_ATASAN', 'REJECTED_FINANCE'])->count();
            $totalDiajukan = $data->sum('nominal_pengajuan');
        @endphp

        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fffbeb; color:#d97706;"><i class="bi bi-hourglass-split"></i></div>
                    <div><div class="stat-label">Menunggu</div><div class="stat-value" style="color:#d97706;">{{ $menunggu }}</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ecfdf5; color:#059669;"><i class="bi bi-check2-circle"></i></div>
                    <div><div class="stat-label">Disetujui</div><div class="stat-value" style="color:#059669;">{{ $disetujui }}</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-x-octagon"></i></div>
                    <div><div class="stat-label">Ditolak</div><div class="stat-value" style="color:#dc2626;">{{ $ditolak }}</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#eff6ff; color:#2563eb;"><i class="bi bi-cash-stack"></i></div>
                    <div><div class="stat-label">Total Diajukan</div><div class="stat-value money" style="font-size:1.1rem;">Rp {{ number_format($totalDiajukan, 0, ',', '.') }}</div></div>
                </div>
            </div>
        </div>

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

        <div class="selection-toolbar" aria-label="Pilihan pengajuan">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <input class="form-check-input approval-select m-0" type="checkbox" id="selectAllPending" @disabled($menunggu === 0)>
                    <label class="fw-semibold" for="selectAllPending">Select All ({{ $menunggu }} menunggu)</label>
                </div>
                <div class="small text-muted mt-1">Pilih pengajuan pada periode ini, maks. 100 per proses.</div>
            </div>
            <div class="small" aria-live="polite" aria-atomic="true">
                <strong><span id="batchCount">0</span> terpilih</strong>
                <span class="text-muted mx-1">·</span><span class="money" id="batchTotal">Rp 0,00</span>
                <div id="batchLimit" class="text-danger" hidden>Pilih maksimal 100 pengajuan untuk satu proses.</div>
            </div>
            <div class="selection-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection" disabled>Batal Pilih</button>
                <button type="button" class="btn btn-sm btn-success" data-batch-action data-mode="approve"
                        data-bs-toggle="modal" data-bs-target="#decisionModal"
                        data-action="{{ route('approval-atasan.batch-approve') }}" disabled><i class="bi bi-check2-all me-1"></i>Approve Terpilih</button>
                <button type="button" class="btn btn-sm btn-danger" data-batch-action data-mode="reject"
                        data-bs-toggle="modal" data-bs-target="#decisionModal"
                        data-action="{{ route('approval-atasan.batch-reject') }}" disabled><i class="bi bi-x-lg me-1"></i>Reject Terpilih</button>
            </div>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-custom mb-0">
                    <thead>
                        <tr>
                            <th class="selection-cell" scope="col">Pilih</th>
                            <th>Doc No</th>
                            <th>Tipe</th> {{-- 🎯 BARU --}}
                            <th>Tgl Pengajuan</th>
                            <th>Tgl Keputusan</th>
                            <th>User</th>
                            <th>Nominal</th>
                            <th>Sisa BS (Snap)</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $bs)
                        @php
                            // 🎯 BARU: Tentukan tipe (fallback 'operasional_sales' untuk data lama)
                            $tipe      = $bs->tipe_pengajuan ?? 'operasional_sales';
                            $isPerjalanan = ($tipe === 'perjalanan_dinas');
                        @endphp
                        <tr data-approval-row @if($bs->status === 'PENDING_ATASAN') data-selectable @endif>
                            <td class="selection-cell">
                                @if($bs->status === 'PENDING_ATASAN')
                                    <input type="checkbox" class="form-check-input approval-select m-0" data-approval-select
                                           value="{{ $bs->id }}" data-docno="{{ $bs->document_no }}"
                                           data-amount-minor="{{ \App\Support\Money::minor($bs->nominal_pengajuan) }}"
                                           aria-label="Pilih pengajuan {{ $bs->document_no }}"
                                           @checked(in_array((string) $bs->id, array_map('strval', (array) old('ids', [])), true))>
                                @else
                                    <span class="text-muted" aria-label="Pengajuan sudah diproses">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-nowrap">{{ $bs->document_no }}</td>

                            {{-- 🎯 BARU: Kolom Tipe Pengajuan --}}
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

                            <td class="text-nowrap">{{ \App\Support\AppTime::format($bs->created_at) }}</td>
                            <td class="text-nowrap">
                                @if($decision = $bs->logs->first())
                                    {{ \App\Support\AppTime::format($decision->created_at) }}
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
                            <td class="money text-muted">Rp {{ number_format($bs->nominal_sisa_bs_snapshot, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $colors = [
                                        'PENDING_ATASAN'   => 'bg-warning text-dark',
                                        'APPROVED_ATASAN'  => 'bg-info text-white',
                                        'REJECTED_ATASAN'  => 'bg-danger text-white',
                                        'DISBURSED'        => 'bg-success text-white',
                                        'REJECTED_FINANCE' => 'bg-danger text-white',
                                        'SETTLED'          => 'bg-secondary text-white',
                                    ];
                                @endphp
                                <span class="badge-status {{ $colors[$bs->status] ?? 'bg-secondary text-white' }}">
                                    {{ $bs->status_label }}
                                </span>
                                @if($bs->isRejectedBySystem())
                                    <small class="d-block text-muted mt-1">{{ $bs->rejection_reason }}</small>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-1 justify-content-end align-items-center flex-nowrap">
                                    <a href="{{ route('approval-atasan.detail', $bs->id) }}" class="btn-act btn-view" title="Lihat Detail Pengajuan">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @if($bs->status == 'PENDING_ATASAN')
                                        <button type="button" class="btn-act btn-approve"
                                            data-bs-toggle="modal" data-bs-target="#decisionModal"
                                            data-mode="approve"
                                            data-action="{{ route('approval-atasan.approve', $bs->id) }}"
                                            data-docno="{{ $bs->document_no }}">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button type="button" class="btn-act btn-reject"
                                            data-bs-toggle="modal" data-bs-target="#decisionModal"
                                            data-mode="reject"
                                            data-action="{{ route('approval-atasan.reject', $bs->id) }}"
                                            data-docno="{{ $bs->document_no }}">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
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
    </div>

    <div class="modal fade" id="decisionModal" tabindex="-1" aria-hidden="true" aria-labelledby="decisionTitle">
        <div class="modal-dialog modal-dialog-centered">
            <form id="decisionForm" method="POST" data-old-approval-message="{{ old('approval_message', '') }}" data-old-rejection-reason="{{ old('rejection_reason', '') }}">
                @csrf
                <input type="hidden" name="periode" value="{{ $selectedPeriod }}">
                <div id="batchHiddenInputs"></div>
                <div class="modal-content" style="border-radius: 16px; border: 1px solid var(--border-color);">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold" id="decisionTitle">-</h5>
                            <p class="text-secondary mb-0 mt-1" style="font-size: 0.85rem;">
                                Dokumen: <span id="decisionDocNo" class="fw-semibold text-primary">-</span>
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary mb-3" style="font-size: 0.9rem;" id="decisionSubtitle"></p>
                        <div id="decisionBatchSummary" hidden>
                            <p class="fw-semibold mb-2">Total nominal: <span id="decisionTotal"></span></p>
                            <ul id="decisionBatchList"></ul>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;" id="decisionLabel" for="decisionMessage">-</label>
                            <textarea class="form-control" id="decisionMessage" name="message"
                                      rows="4" maxlength="1000" placeholder=""></textarea>
                            <small class="text-muted"><span id="charCount">0</span> / 1000</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn" id="decisionSubmitBtn">Konfirmasi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/approval-atasan.js') }}?v={{ filemtime(public_path('js/approval-atasan.js')) }}" defer></script>
</body>
</html>
