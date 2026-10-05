# Kode existing: tampilan-transaksi-dan-finance, bagian 1

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: resources/views/approval-finance/detail.blade.php

SHA-256: `70cc79ed8ad47a3ff8dad6b7deda2c6a05c1397455da5f4bc50b8be40ed71a17`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan BS — Finance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body:#f8fafc; --bg-card:#ffffff; --text-primary:#0f172a; --text-secondary:#64748b; --border-color:#e2e8f0; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background-color:var(--bg-body); color:var(--text-primary); min-height:100vh; }
        .page-header { padding:2rem 0 1rem; }
        .card-custom { background:var(--bg-card); border:1px solid var(--border-color); border-radius:16px; padding:1.5rem; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02); }
        .btn-nav { background-color:white; border:1px solid var(--border-color); color:var(--text-secondary); font-weight:500; font-size:0.875rem; padding:0.5rem 1rem; border-radius:8px; text-decoration:none; }
        .btn-nav:hover { background-color:#f1f5f9; color:var(--text-primary); }
        .section-title { font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-secondary); margin-bottom:1rem; }
        .info-row { display:flex; justify-content:space-between; gap:1rem; padding:0.6rem 0; border-bottom:1px dashed var(--border-color); font-size:0.92rem; }
        .info-row:last-child { border-bottom:0; }
        .info-label { color:var(--text-secondary); font-weight:600; flex-shrink:0; }
        .info-value { font-weight:600; text-align:right; }
        .money { font-variant-numeric:tabular-nums; white-space:nowrap; }
        .nominal-big { font-size:1.5rem; font-weight:800; }
        .info-sub { font-size:0.78rem; color:var(--text-secondary); line-height:1.5; }
        .badge-status { padding:0.4em 0.8em; border-radius:6px; font-weight:600; font-size:0.75rem; text-transform:uppercase; }
        .member-chip { display:inline-flex; align-items:center; gap:0.4rem; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:999px; padding:0.35rem 0.8rem; font-size:0.8rem; font-weight:600; margin:0 0.4rem 0.4rem 0; }
        .badge-tipe { display:inline-flex; align-items:center; gap:0.35rem; padding:0.4em 0.85em; border-radius:6px; font-weight:700; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.04em; }
        .badge-tipe-operasional { background:#eff6ff; color:#1d4ed8; }
        .badge-tipe-dinas { background:#ecfdf5; color:#047857; }
        .group-info-box { border-radius:10px; padding:0.85rem 1rem; display:flex; align-items:center; gap:0.75rem; font-size:0.88rem; }
        .group-info-box.solo { background:#f1f5f9; color:#475569; border:1px dashed #cbd5e1; }
        .group-info-box.team { background:linear-gradient(135deg,#f0f4ff 0%,#e8efff 100%); color:#3730a3; border:1px solid #c7d2fe; }
        .group-info-box i.box-icon { font-size:1.2rem; }
        .nominal-display { border-radius:0 8px 8px 0 !important; border:1px solid var(--border-color); padding:0.55rem 0.75rem; font-size:1rem; font-weight:600; color:var(--text-primary); transition:all 0.2s; }
        .nominal-display:focus { border-color:var(--text-primary); box-shadow:0 0 0 3px rgba(15,23,42,0.08); outline:none; }
        .nominal-display.is-invalid { border-color:#ef4444 !important; background-color:#fef2f2; }
        .nominal-display.is-valid { border-color:#10b981 !important; }
        .btn-full { background:#fffbeb; color:#d97706; border:1px solid #fde68a; width:38px; height:38px; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:0.95rem; transition:all 0.15s ease; flex-shrink:0; }
        .btn-full:hover { background:#fef3c7; transform:translateY(-1px); }
        .btn-act { border:none; padding:0.6rem 1.1rem; border-radius:8px; font-weight:600; font-size:0.875rem; color:white; cursor: pointer; }
        .btn-cairkan { background-color:var(--text-primary); }
        .btn-cairkan:hover:not(:disabled) { background-color:#334155; }
        .btn-cairkan:disabled { opacity:0.45; cursor:not-allowed; }
        .btn-reject { background-color:#ef4444; }
        .btn-reject:hover { background-color:#dc2626; }
        .timeline { position:relative; padding-left:1.75rem; }
        .timeline::before { content:''; position:absolute; left:7px; top:6px; bottom:6px; width:2px; background:var(--border-color); }
        .timeline-item { position:relative; padding-bottom:1.25rem; }
        .timeline-item:last-child { padding-bottom:0; }
        .timeline-dot { position:absolute; left:-1.75rem; top:4px; width:16px; height:16px; border-radius:50%; background:#fff; border:3px solid #3b82f6; }
        .timeline-time { font-size:0.75rem; color:var(--text-secondary); }
        .timeline-title { font-weight:700; font-size:0.9rem; }
        .timeline-desc { font-size:0.85rem; color:var(--text-secondary); }
    </style>
</head>
<body class="py-4 py-md-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Detail Pengajuan BS <span class="text-muted fw-semibold" style="font-size:0.9rem;">(Finance)</span></h3>
            <a href="{{ route('approval-finance.index') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">❌ {{ session('error') }}</div>
        @endif

        @php
            $tipe = $bs->tipe_pengajuan ?? 'operasional_sales';
            $isPerjalanan = ($tipe === 'perjalanan_dinas');
            $tipeLabel = $isPerjalanan ? 'Perjalanan Dinas' : 'Operasional Sales';
            $cair = $bs->financeApprovals->first();
            $colors = [
                'PENDING_ATASAN'   => 'bg-warning text-dark',
                'APPROVED_ATASAN'  => 'bg-info text-white',
                'REJECTED_ATASAN'  => 'bg-danger text-white',
                'DISBURSED'        => 'bg-success text-white',
                'REJECTED_FINANCE' => 'bg-danger text-white',
                'SETTLED'          => 'bg-secondary text-white',
            ];
        @endphp

        {{-- ================= BANNER STATUS KONTEKSTUAL (versi Finance) ================= --}}
        @if($bs->status === 'APPROVED_ATASAN')
            <div class="alert alert-warning border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-hourglass-split me-2"></i> Menunggu keputusan Anda sebagai Finance: cairkan atau tolak pencairan.
            </div>
        @elseif($bs->status === 'DISBURSED')
            <div class="alert alert-success border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-cash-coin me-2"></i> Pengajuan ini telah dicairkan oleh Finance.
            </div>
        @elseif($bs->status === 'SETTLED')
            <div class="alert alert-secondary border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-lock-fill me-2"></i> Pengajuan ini sudah selesai dan periodenya telah ditutup (SETTLED).
            </div>
        @elseif($bs->status === 'REJECTED_FINANCE')
            <div class="alert alert-danger border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-x-circle me-2"></i> Pencairan ditolak oleh Finance. Alasan: {{ $bs->rejection_reason }}
            </div>
        @elseif($bs->status === 'REJECTED_ATASAN')
            <div class="alert alert-secondary border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-x-circle me-2"></i> Ditolak atasan — dokumen ini tidak pernah masuk proses Finance.
            </div>
        @else
            <div class="alert alert-info border-0 shadow-sm fw-semibold" style="border-radius: 12px;">
                <i class="bi bi-hourglass me-2"></i> Masih menunggu persetujuan atasan — belum menjadi ranah Finance.
            </div>
        @endif

        {{-- ================= INFORMASI PENGAJUAN ================= --}}
        <div class="card-custom mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div class="section-title mb-0">Informasi Pengajuan</div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-tipe {{ $isPerjalanan ? 'badge-tipe-dinas' : 'badge-tipe-operasional' }}">
                        <i class="bi {{ $isPerjalanan ? 'bi-airplane-fill' : 'bi-briefcase-fill' }}"></i> {{ $tipeLabel }}
                    </span>
                    <span class="badge-status {{ $colors[$bs->status] ?? 'bg-secondary text-white' }}">{{ str_replace('_', ' ', $bs->status) }}</span>
                </div>
            </div>
            <div class="info-row"><span class="info-label">No. Dokumen</span><span class="info-value">{{ $bs->document_no }}</span></div>
            <div class="info-row"><span class="info-label">Tanggal Pengajuan</span><span class="info-value">{{ \App\Support\AppTime::format($bs->created_at) }}</span></div>
            <div class="info-row"><span class="info-label">Periode</span><span class="info-value">{{ $bs->periode }}</span></div>
            <div class="info-row"><span class="info-label">Diajukan Oleh</span><span class="info-value">{{ $bs->user?->nama ?? '-' }} <span class="text-muted fw-normal">(NIK: {{ $bs->user?->NIK ?? '-' }})</span></span></div>
            <div class="info-row"><span class="info-label">Atasan Penyetuju</span><span class="info-value">{{ $bs->atasan?->nama ?? '-' }}</span></div>
            <div class="info-row"><span class="info-label">Sisa Saldo BS (Snapshot)</span><span class="info-value money">Rp {{ number_format($bs->nominal_sisa_bs_snapshot, 0, ',', '.') }}</span></div>
            <div class="info-row align-items-center"><span class="info-label">Nominal Pengajuan</span><span class="info-value money nominal-big">Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</span></div>
        </div>

        {{-- ================= DESKRIPSI ================= --}}
        <div class="card-custom mb-4">
            <div class="section-title">Deskripsi / Keperluan</div>
            <p class="mb-0" style="font-size: 0.92rem;">{{ $bs->description ?? 'Tidak ada deskripsi.' }}</p>
        </div>

        {{-- ================= ANGGOTA GRUP ================= --}}
        <div class="card-custom mb-4">
            <div class="section-title">{{ $isPerjalanan ? 'Anggota Grup Dinas' : 'Keanggotaan' }}</div>
            @if($isPerjalanan && $bs->members && $bs->members->count() > 0)
                <div class="group-info-box team mb-3">
                    <i class="bi bi-people-fill box-icon"></i>
                    <div>
                        <div class="fw-bold small">Perjalanan dinas bersama {{ $bs->members->count() }} anggota tim</div>
                        <div class="small opacity-75">Anggota yang tercantum berhak menggunakan BS ini untuk realisasi biaya di lapangan.</div>
                    </div>
                </div>
                <div>
                    @foreach($bs->members as $m)
                        <span class="member-chip"><i class="bi bi-person-check"></i> {{ $m->nama }}</span>
                    @endforeach
                </div>
            @elseif($isPerjalanan)
                <div class="group-info-box solo">
                    <i class="bi bi-person box-icon"></i>
                    <div><strong>Perjalanan Dinas — Perorangan</strong><div class="small opacity-75">Tidak ada anggota tim yang dicantumkan.</div></div>
                </div>
            @else
                <div class="group-info-box solo">
                    <i class="bi bi-person-workspace box-icon"></i>
                    <div><strong>Operasional Sales — Perorangan</strong><div class="small opacity-75">Tipe pengajuan ini hanya untuk pemohon sendiri.</div></div>
                </div>
            @endif
        </div>

        {{-- ================= 🎯 PROSES FINANCE (khas halaman ini) ================= --}}
        <div class="card-custom mb-4">
            <div class="section-title">Proses Finance</div>
            @if($bs->status === 'DISBURSED' && $cair)
                <div class="info-row"><span class="info-label">No. Dokumen Pencairan</span><span class="info-value">{{ $cair->document_no }}</span></div>
                <div class="info-row"><span class="info-label">Tanggal Cair</span><span class="info-value">{{ \App\Support\AppTime::format($cair->created_at) }}</span></div>
                <div class="info-row"><span class="info-label">Dicairkan Oleh</span><span class="info-value">{{ $cair->financeUser?->nama ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">Nominal Disetujui</span><span class="info-value money nominal-big" style="color:#059669;">Rp {{ number_format($cair->nominal_approved, 0, ',', '.') }}</span></div>
                <div class="info-row"><span class="info-label">Sisa BS Saat Cair</span><span class="info-value money">Rp {{ number_format($cair->nominal_sisa_bs, 0, ',', '.') }}</span></div>
            @elseif($bs->status === 'REJECTED_FINANCE')
                <div class="info-row"><span class="info-label">Alasan Penolakan</span><span class="info-value text-danger">{{ $bs->rejection_reason ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">Waktu Keputusan</span><span class="info-value">{{ \App\Support\AppTime::format($logs->firstWhere('activity_code', 'REJECTED_FINANCE')?->created_at) }}</span></div>
            @elseif($bs->status === 'APPROVED_ATASAN')
                <div class="alert alert-warning border-0 mb-0 small" style="border-radius:10px;">
                    <i class="bi bi-hourglass-split me-1"></i> Belum ada keputusan Finance. Gunakan panel <strong>Keputusan Finance</strong> di bawah.
                </div>
            @else
                <p class="text-muted small mb-0">Dokumen ini belum masuk ranah Finance.</p>
            @endif
        </div>

        {{-- ================= AUDIT TRAIL ================= --}}
        <div class="card-custom mb-4">
            <div class="section-title">Riwayat Proses</div>
            <div class="timeline">
                @forelse($logs as $log)
                    <div class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div class="timeline-time">{{ \App\Support\AppTime::format($log->created_at) }} — oleh {{ $log->actor?->nama ?? 'System' }}</div>
                        <div class="timeline-title">{{ $log->activity_name ?? '-' }}</div>
                        <div class="timeline-desc">{{ $log->description ?? '-' }}</div>
                    </div>
                @empty
                    <span class="text-muted small">Belum ada riwayat.</span>
                @endforelse
            </div>
        </div>

        {{-- ================= KEPUTUSAN FINANCE (hanya saat APPROVED_ATASAN) ================= --}}
        @if($bs->status === 'APPROVED_ATASAN')
            <div class="card-custom">
                <div class="section-title">Keputusan Finance</div>
                <form id="disburseForm" action="{{ route('approval-finance.disburse', $bs->id) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="form-label fw-semibold" style="font-size:0.85rem;">Nominal Pencairan <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="input-group" style="max-width: 260px;">
                            <span class="input-group-text" style="background:#f8fafc; border-color: var(--border-color);">Rp</span>
                            <input type="text" class="form-control nominal-display" placeholder="0" autocomplete="off" inputmode="numeric" style="text-align:right;">
                            <input type="hidden" name="nominal_approved" class="nominal-hidden">
                        </div>
                        <button type="button" class="btn-full" title="Isi penuh sesuai nominal pengajuan"><i class="bi bi-lightning-charge-fill"></i></button>
                        <button type="submit" class="btn-act btn-cairkan" disabled><i class="bi bi-cash-coin me-1"></i> Cairkan</button>
                    </div>
                    <div class="info-sub mt-2">Maksimal: <strong class="money">Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</strong></div>
                </form>
                <hr>
                <button type="button" class="btn-act btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-lg me-1"></i> Tolak Pencairan
                </button>
            </div>

            {{-- Modal alasan penolakan --}}
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('approval-finance.reject', $bs->id) }}" method="POST">
                        @csrf
                        <div class="modal-content" style="border-radius: 16px; border: 1px solid var(--border-color);">
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h5 class="modal-title fw-bold">Tolak Pencairan BS</h5>
                                    <p class="text-secondary mb-0 mt-1" style="font-size: 0.85rem;">Dokumen: <span class="fw-semibold text-primary">{{ $bs->document_no }}</span></p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Alasan Penolakan <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="rejection_reason" rows="4" required maxlength="1000" placeholder="Contoh: Dokumen pendukung belum lengkap..."></textarea>
                                    <small class="text-muted"><span id="charCount">0</span> / 1000</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Ya, Tolak Pencairan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-format nominal (sama seperti di index)
        const form = document.getElementById('disburseForm');
        if (form) {
            const display = form.querySelector('.nominal-display');
            const hidden  = form.querySelector('.nominal-hidden');
            const btn     = form.querySelector('.btn-cairkan');
            const fullBtn = form.querySelector('.btn-full');
            const maxVal  = {{ (int) $bs->nominal_pengajuan }};

            display.addEventListener('input', function () {
                let raw = this.value.replace(/[^\d]/g, '');
                if (raw === '') { this.value=''; hidden.value=''; btn.disabled=true; this.classList.remove('is-valid','is-invalid'); return; }
                let num = parseInt(raw, 10);
                if (num > maxVal) { num = maxVal; this.classList.add('is-invalid'); this.classList.remove('is-valid'); btn.disabled = true; }
                else { this.classList.remove('is-invalid'); if (num > 0) this.classList.add('is-valid'); btn.disabled = false; }
                this.value = num.toLocaleString('id-ID');
                hidden.value = num;
            });
            fullBtn.addEventListener('click', function () { display.value = String(maxVal); display.dispatchEvent(new Event('input')); display.focus(); });
            form.addEventListener('submit', function (e) {
                if (!hidden.value || parseInt(hidden.value) < 1) { e.preventDefault(); display.classList.add('is-invalid'); display.focus(); }
            });
        }
        // Char counter modal reject
        const reason = document.querySelector('#rejectModal textarea');
        reason?.addEventListener('input', function () { document.getElementById('charCount').textContent = this.value.length; });
    </script>
</body>
</html>

~~~~

## FILE: resources/views/approval-finance/index.blade.php

SHA-256: `f5356e900b8314fafeabde1c56f1891a59430e80624101888e2591d8e569e178`

~~~~php
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

~~~~

## FILE: resources/views/bs-usage-approvals/actions.blade.php

SHA-256: `0468e6df1358b89a3af74fb5d757791d2b3b39f7035c6d808fa4799479f7d820`

~~~~php
@if($transaksi->status === 'PENDING_OWNER' && $transaksi->bsCashOwnerId() === (int) auth()->id() && app(\App\Services\PeriodService::class)->isOpen($transaksi->periode))
    <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Keputusan Pemohon</h2>
        <p>Periksa seluruh rincian biaya. Persetujuan akan mengurangi saldo BS Anda dan mengunci transaksi sebagai realisasi final.</p>
        <form method="POST" action="{{ route('bs-usage-approvals.approve', $transaksi->id) }}" class="mb-3" onsubmit="return confirm('Setujui seluruh rincian dan konfirmasi pemakaian saldo BS Anda?');">
            @csrf
            <input type="hidden" name="approval_version" value="{{ $transaksi->approval_version }}">
            <button class="btn btn-success">Setujui &amp; Konfirmasi</button>
        </form>
        <form method="POST" action="{{ route('bs-usage-approvals.reject', $transaksi->id) }}">
            @csrf
            <input type="hidden" name="approval_version" value="{{ $transaksi->approval_version }}">
            <label for="rejection-reason" class="form-label">Alasan penolakan</label>
            <textarea id="rejection-reason" name="reason" class="form-control mb-2" required maxlength="1000" rows="2">{{ old('reason') }}</textarea>
            <button class="btn btn-outline-danger">Tolak Pemakaian</button>
        </form>
    </div></div>
@endif

~~~~

## FILE: resources/views/bs-usage-approvals/history.blade.php

SHA-256: `e9623babd84c500e9ab42322deb08014572a988d5701ca1af81cfd0242862d48`

~~~~php
@if($transaksi->approvalLogs->isNotEmpty())
    <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Riwayat Persetujuan Pemakaian BS</h2>
        <div class="table-responsive"><table class="table table-sm align-middle">
            <thead><tr><th>Waktu</th><th>Pengajuan ke-</th><th>Tindakan</th><th>Oleh</th><th class="text-end">Nominal</th></tr></thead>
            <tbody>
                @foreach($transaksi->approvalLogs as $log)
                    <tr>
                        <td class="text-nowrap">{{ \App\Support\AppTime::format($log->created_at) }}</td>
                        <td>{{ $log->approval_version }}</td>
                        <td>{{ $log->action_label }}@if($log->reason)<div class="small">Alasan: {{ $log->reason }}</div>@endif</td>
                        <td>{{ $log->actor?->nama ?? '-' }}</td>
                        <td class="text-end text-nowrap">Rp {{ number_format($log->nominal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div></div>
@endif

~~~~

## FILE: resources/views/bs-usage-approvals/index.blade.php

SHA-256: `1a46881f333e212eb7b5129a2021e7ef8828ca471aefd39099467ac0b3023ce6`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Pemakaian BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @include('partials.application-time')
    <main class="container py-4">
        <div class="d-flex justify-content-between gap-3 mb-3"><h1 class="h3">Persetujuan Pemakaian BS</h1><a href="{{ route('dashboard.transaksi') }}" class="btn btn-outline-secondary">Kembali</a></div>
        <p>Permintaan anggota grup dinas untuk memakai saldo BS Anda. Saldo berkurang setelah Anda menyetujui rincian biaya. Permintaan yang menunggu tidak menyisihkan saldo.</p>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <form class="d-flex gap-2 mb-3" method="GET">
            <label for="status" class="col-form-label">Status</label>
            <select name="status" id="status" class="form-select w-auto">
                @foreach(['PENDING_OWNER' => 'Menunggu Persetujuan', 'REJECTED_OWNER' => 'Ditolak', 'VALIDATED' => 'Disetujui', 'ALL' => 'Semua'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Tampilkan</button>
        </form>
        <div class="card mb-3"><div class="table-responsive"><table class="table mb-0 align-middle">
            <thead><tr><th>Anggota</th><th>Transaksi</th><th>Tanggal Dinas</th><th>Tujuan</th><th class="text-end">Total Biaya</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($requests as $item)
                    <tr><td>{{ $item->user?->nama }}</td><td>{{ $item->transaksi_id }}</td><td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td><td>{{ $item->tujuanDinas?->nama }}</td><td class="text-end text-nowrap">Rp {{ number_format($item->details_sum_nominal, 2, ',', '.') }}</td><td>{{ $item->status_label }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('bs-usage-approvals.show', $item->id) }}">Periksa Rincian</a></td></tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada permintaan pada status ini.</td></tr>
                @endforelse
            </tbody>
        </table></div></div>
        {{ $requests->links() }}
    </main>
</body>
</html>

~~~~

## FILE: resources/views/bs-usage-approvals/show.blade.php

SHA-256: `7cf0ce290c54166954f6961129818d0221e384a6208ebc6194d815c580d7a41d`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Pemakaian BS - {{ $transaksi->transaksi_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @include('partials.application-time')
    <main class="container py-4" style="max-width: 1000px">
        <div class="d-flex justify-content-between gap-3 mb-3"><h1 class="h3">Pemeriksaan Pemakaian BS</h1><a href="{{ route('bs-usage-approvals.index') }}" class="btn btn-outline-secondary">Daftar Permintaan</a></div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @include('partials.travel-confirmation', ['canEdit' => false])
        <div class="card mb-3"><div class="card-body">
            <h2 class="h5">{{ $transaksi->transaksi_id }}</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4">Anggota yang memakai</dt><dd class="col-sm-8">{{ $transaksi->user?->nama }}</dd>
                <dt class="col-sm-4">Tanggal / Tujuan Dinas</dt><dd class="col-sm-8">{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') }} / {{ $transaksi->tujuanDinas?->nama }}</dd>
                <dt class="col-sm-4">Keterangan</dt><dd class="col-sm-8">{{ $transaksi->description }}</dd>
                <dt class="col-sm-4">Jarak / Durasi</dt><dd class="col-sm-8">{{ $transaksi->jarak_km }} km / {{ $transaksi->durasi_jam }} jam</dd>
                <dt class="col-sm-4">Saldo BS tersedia ({{ $transaksi->periode }})</dt><dd class="col-sm-8">Rp {{ number_format(app(\App\Services\BsCashFlowService::class)->availableBalance($balance), 2, ',', '.') }}</dd>
            </dl>
            @if(!$balance['opening_final'])<p class="text-warning mb-0">Saldo awal masih menunggu tutup buku bulan sebelumnya.</p>@endif
            @if($transaksi->is_over_plafon)<p class="text-danger mt-2 mb-0">Biaya melebihi plafon Rp {{ number_format($transaksi->plafon_value, 2, ',', '.') }}. Selisih: Rp {{ number_format($transaksi->selisih_plafon, 2, ',', '.') }}.</p>@endif
        </div></div>
        <div class="card mb-3"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Jenis Biaya</th><th>Keterangan</th><th class="text-end">Nominal</th></tr></thead>
            <tbody>@foreach($transaksi->details as $detail)<tr><td>{{ $detail->costCategory?->nama ?? $detail->costCategory?->name ?? $detail->costCategory?->description ?? '-' }}</td><td>{{ $detail->remark }}</td><td class="text-end text-nowrap">Rp {{ number_format($detail->nominal, 2, ',', '.') }}</td></tr>@endforeach</tbody>
            <tfoot><tr><th colspan="2">Total Pemakaian yang Diajukan</th><th class="text-end text-nowrap">Rp {{ number_format($transaksi->details->sum('nominal'), 2, ',', '.') }}</th></tr></tfoot>
        </table></div></div>
        @include('bs-usage-approvals.actions')
        @include('bs-usage-approvals.history')
    </main>
</body>
</html>

~~~~

## FILE: resources/views/transaksi_operasional/create.blade.php

SHA-256: `80bdce4694953aec9aae39542e2c1b1fa11930fe6ada2266af654900351cf03e`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Transaksi Operasional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- 🎯 TAMBAHAN: Flatpickr CSS untuk Datepicker Modern -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            --primary-color: #667eea;
            --success-color: #11998e;
            --bg-color: #f0f4f8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            background-image: radial-gradient(#667eea 0.5px, transparent 0.5px), radial-gradient(#667eea 0.5px, var(--bg-color) 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            min-height: 100vh;
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }

        .page-header h1 { font-weight: 800; margin: 0; font-size: 2rem; }
        .page-header p { margin: 0.5rem 0 0 0; opacity: 0.9; }

        .card-modern {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: visible;
            margin-bottom: 2rem;
        }

        .card-header-modern {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 20px 20px 0 0;
        }

        .card-header-modern.success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            border-bottom-color: rgba(17, 153, 142, 0.2);
        }

        .card-header-modern.sticky-detail {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: linear-gradient(135deg, #e6f7f4 0%, #eafcf0 100%);
            transition: box-shadow 0.25s ease, border-radius 0.25s ease;
        }
        .card-header-modern.sticky-detail.is-stuck {
            box-shadow: 0 8px 24px rgba(17, 153, 142, 0.22);
            border-radius: 0;
        }

        .card-header-modern h5 {
            margin: 0;
            font-weight: 700;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header-modern h5 i {
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-header-modern.success h5 i {
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-label { font-weight: 600; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.9rem; }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-control:disabled { background: #f7fafc; border-color: #e2e8f0; }

        .input-group-text {
            background: white;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .input-with-icon .form-control { border-radius: 0 12px 12px 0; }

        .btn-modern {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            border-radius: 15px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .btn-primary-modern:hover,
        .btn-primary-modern:focus,
        .btn-primary-modern:active {
            background: var(--primary-gradient);
            color: white;
            transform: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
            transition: all 0.3s ease;
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.5);
            color: white;
        }

        .btn-danger-modern {
            background: var(--danger-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(238, 9, 121, 0.4);
            transition: all 0.3s ease;
        }

        .btn-danger-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 9, 121, 0.5);
            color: white;
        }

        .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }

        .table-modern thead th {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 2px solid #e2e8f0;
            padding: 1rem;
            font-weight: 700;
            color: #2d3748;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f0f4f8;
            vertical-align: middle;
        }

        .table-modern tbody tr:hover { background: rgba(102, 126, 234, 0.02); }

        .total-badge {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            border: 2px solid var(--success-color);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            text-align: right;
        }

        .total-badge .amount {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .info-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-left: 4px solid var(--primary-color);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-top: 1rem;
        }

        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.2), transparent);
            margin: 1.5rem 0;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-slide-in { animation: slideIn 0.5s ease; }

        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.5rem; }
            .card-header-modern { flex-direction: column; gap: 1rem; text-align: center; }
            .total-badge { text-align: center; margin-bottom: 1rem; width: 100%; }
            .total-badge .amount { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="bi bi-receipt me-3"></i>Transaksi Operasional</h1>
                    <p class="mb-0">Input dan kelola transaksi area sales dengan mudah</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('dashboard.transaksi') }}" class="btn btn-light btn-modern">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1100px; margin-bottom: 3rem;">

        @if(session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                    <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-custom alert-dismissible fade show mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-circle-fill me-3 fs-4"></i>
                    <div>
                        <strong>Gagal Menyimpan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('transaksi_operasional.store') }}" method="POST" id="form-operasional">
            @csrf

            <!-- Card 1: Data Header -->
            <div class="card-modern animate-slide-in">
                <div class="card-header-modern">
                    <h5><i class="bi bi-clipboard-data"></i> Data Header Transaksi</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary">Step 1 dari 2</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-calendar-event me-2 text-primary"></i>Tanggal Transaksi
                            </label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <!-- 🎯 UBAH: type="text" agar Flatpickr bisa mengambil alih -->
                                <input type="text" id="tanggal_picker" name="tanggal" class="form-control" value="{{ old('tanggal', \App\Support\AppTime::now()->toDateString()) }}" placeholder="Pilih tanggal..." required autocomplete="off">
                            </div>
                            <div id="periodWarning" class="alert alert-danger d-none mt-2" style="border-radius:12px;">
                                <i class="bi bi-lock-fill me-2"></i><span id="periodWarningText"></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-person-badge me-2 text-primary"></i>User (Karyawan)
                            </label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="text" class="form-control bg-light" value="{{ Auth::user()->nama ?? Auth::user()->name }} (NIK: {{ Auth::user()->NIK ?? '-' }})" readonly disabled>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Transaksi otomatis dicatat atas nama akun Anda</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-file-text me-2 text-primary"></i>Deskripsi (Tujuan) <span class="text-muted fw-normal small">(Opsional)</span>
                            </label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" name="description" class="form-control"
                                       placeholder="Contoh: Kunjungan Toko ABC"
                                       value="{{ old('description') }}">
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Kosongkan jika tidak perlu.</small>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                   <div class="mt-4">
    @include('partials.transaction-funding', ['fundingType' => 'operasional_sales'])
    <div id="bs-warning-info" class="alert alert-warning mt-3 d-none">Total rincian biaya melebihi saldo BS gabungan yang tersedia.</div>
</div>
</div>
</div>

            <!-- Card 2: Rincian Biaya (dengan sticky header) -->
            <div class="card-modern animate-slide-in" style="animation-delay: 0.1s">
                <div id="sticky-sentinel" style="height: 0;"></div>

                <div class="card-header-modern success sticky-detail" id="detail-header">
                    <h5><i class="bi bi-receipt-cutoff"></i> Rincian Biaya (Detail)</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="total-badge mb-0">
                            <small class="text-muted d-block">Total Rincian</small>
                            <span id="total-rincian" class="amount">Rp 0</span>
                        </div>
                        <button type="button" class="btn btn-success-modern btn-sm" id="btn-tambah-baris">
                            <i class="bi bi-plus-lg"></i> Tambah Baris
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern" id="tabel-detail">
                            <thead>
                                <tr>
                                    <th width="30%"><i class="bi bi-tag me-2"></i>Cost Category <span class="text-danger">*</span></th>
                                    <th width="35%"><i class="bi bi-chat-left-text me-2"></i>Remark (Keterangan)</th>
                                    <th width="25%"><i class="bi bi-currency-dollar me-2"></i>Nominal (Rp) <span class="text-danger">*</span></th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $oldDetails = array_values(old('details', [[]])); @endphp
                                @foreach($oldDetails as $idx => $oldDetail)
                                <tr id="baris-{{ $idx }}" class="animate-slide-in">
                                    <td>
                                        <select name="details[{{ $idx }}][cost_category_id]" class="form-select" required>
                                            <option value="">-- Pilih Cost Category --</option>
                                            @foreach($cost_categories as $cc)
                                                <option value="{{ $cc->id }}" @selected(($oldDetail['cost_category_id'] ?? '') == $cc->id)>{{ $cc->name ?? $cc->nama ?? $cc->code }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $idx }}][remark]" class="form-control" placeholder="Misal: Bensin Motor" value="{{ $oldDetail['remark'] ?? '' }}" required>
                                    </td>
                                    <td>
                                        @php $oldNominal = $oldDetail['nominal'] ?? ''; @endphp
                                        <div class="input-group input-with-icon">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" inputmode="numeric" autocomplete="off"
                                                   class="form-control nominal-display" placeholder="0" value="{{ $oldNominal !== '' ? number_format((float) $oldNominal, 0, ',', '.') : '' }}" required>
                                            <input type="hidden" name="details[{{ $idx }}][nominal]" class="nominal-hidden" value="{{ $oldNominal }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus" data-id="{{ $idx }}" title="Hapus baris">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <p class="text-muted small">Draft dapat diedit dan belum mengurangi saldo BS. Konfirmasi membukukan pemakaian BS dan mengunci transaksi.</p>
            <div class="d-grid d-sm-flex gap-2 animate-slide-in" style="animation-delay: 0.2s">
                <button type="submit" name="save_action" value="draft" class="btn btn-outline-secondary btn-lg py-3 flex-fill">
                    <i class="bi bi-save me-2 fs-5"></i> Simpan Draft
                </button>
                <button type="submit" name="save_action" value="confirm" class="btn btn-primary-modern btn-lg py-3 flex-fill">
                    <i class="bi bi-check-circle me-2 fs-5"></i> Simpan &amp; Konfirmasi
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- 🎯 TAMBAHAN: Flatpickr JS & Locale Indonesia -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

    <script>
        $(document).ready(function() {

            // ============================================================
            // 🎯 1. STICKY HEADER DETECTION
            // ============================================================
            const sentinel     = document.getElementById('sticky-sentinel');
            const detailHeader = document.getElementById('detail-header');
            if (sentinel && detailHeader && 'IntersectionObserver' in window) {
                new IntersectionObserver((entries) => {
                    detailHeader.classList.toggle('is-stuck', !entries[0].isIntersecting);
                }, { threshold: 1 }).observe(sentinel);
            }

            // ============================================================
            // 🎯 2. FORMAT & LIMIT NOMINAL
            // ============================================================
            const MAX_DIGITS   = 9;
            const formatRibuan = d => d.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const bersih       = v => String(v).replace(/\D/g, '').replace(/^0+/, '').slice(0, MAX_DIGITS);

            function syncNominal(el) {
                const caret       = el.selectionStart || 0;
                const digitBefore = el.value.slice(0, caret).replace(/\D/g, '').length;
                const digits      = bersih(el.value);

                el.value = digits ? formatRibuan(digits) : '';

                let count = 0, pos = 0;
                for (let i = 0; i < el.value.length; i++) {
                    if (count >= digitBefore) break;
                    if (/\d/.test(el.value[i])) count++;
                    pos = i + 1;
                }
                el.setSelectionRange(pos, pos);

                const hidden = el.closest('td').querySelector('.nominal-hidden');
                if (hidden) hidden.value = digits;
            }

            $(document).on('input', '.nominal-display', function() {
                syncNominal(this);
                hitungTotal();
            });

            // ============================================================
            // 🎯 3. LOGIC TOTAL & WARNING BS
            // ============================================================
            function getSisaSaldoBs() {
    const value = document.getElementById('bs-saldo-info')?.dataset.balance;
    return value === undefined || value === '' ? null : Number(value);
}

            function hitungTotal() {
                let total = 0;
                $('.nominal-hidden').each(function() {
                    total += parseFloat(this.value) || 0;
                });
                $('#total-rincian').html('Rp ' + new Intl.NumberFormat('id-ID').format(total));

                const sisa = getSisaSaldoBs();
                if (sisa !== null && total > sisa) {
                    $('#bs-warning-info').removeClass('d-none');
                    $('#total-rincian').css('background', 'linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)')
                                       .css('-webkit-background-clip', 'text')
                                       .css('-webkit-text-fill-color', 'transparent');
                } else {
                    $('#bs-warning-info').addClass('d-none');
                    $('#total-rincian').css('background', 'var(--success-gradient)')
                                       .css('-webkit-background-clip', 'text')
                                       .css('-webkit-text-fill-color', 'transparent');
                }
                return total;
            }

            document.addEventListener('bs-balance-updated', hitungTotal);

            // ============================================================
            // 🎯 4. DYNAMIC ROWS
            // ============================================================
            let rowIndex = {{ count($oldDetails) }};

            const costCategoryOptions = `
                <option value="">-- Pilih Cost Category --</option>
                @foreach($cost_categories as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->name ?? $cc->nama ?? $cc->code }}</option>
                @endforeach
            `;

            function reindexRows() {
                $('#tabel-detail tbody tr').each(function(i) {
                    $(this).attr('id', 'baris-' + i);
                    $(this).find('select').attr('name', 'details[' + i + '][cost_category_id]');
                    $(this).find('input[type="text"][name^="details"]').attr('name', 'details[' + i + '][remark]');
                    $(this).find('.nominal-hidden').attr('name', 'details[' + i + '][nominal]');
                    $(this).find('.btn-hapus').attr('data-id', i);
                });
                rowIndex = $('#tabel-detail tbody tr').length;
            }

            $('#btn-tambah-baris').click(function() {
                const current = rowIndex;
                let barisBaru = `
                    <tr id="baris-${current}" class="animate-slide-in">
                        <td>
                            <select name="details[${current}][cost_category_id]" class="form-select" required>
                                ${costCategoryOptions}
                            </select>
                        </td>
                        <td>
                            <input type="text" name="details[${current}][remark]" class="form-control" placeholder="Tambahan keterangan..." required>
                        </td>
                        <td>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text">Rp</span>
                                <input type="text" inputmode="numeric" autocomplete="off"
                                       class="form-control nominal-display" placeholder="0" required>
                                <input type="hidden" name="details[${current}][nominal]" class="nominal-hidden" value="">
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus" data-id="${current}" title="Hapus baris">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#tabel-detail tbody').append(barisBaru);
                rowIndex++;
                hitungTotal();

                const el = document.getElementById('baris-' + current);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => el.querySelector('select')?.focus({ preventScroll: true }), 400);
                }
            });

            $(document).on('click', '.btn-hapus', function() {
                const $row = $(this).closest('tr');
                if ($row.hasClass('removing')) return;

                if ($('#tabel-detail tbody tr').not('.removing').length <= 1) {
                    alert('⚠️ Minimal harus ada 1 baris rincian biaya.');
                    return;
                }

                $row.addClass('removing').fadeOut(300, function() {
                    $(this).remove();
                    reindexRows();
                    hitungTotal();
                });
            });

            // ============================================================
            // 🎯 5. VALIDASI SUBMIT
            // ============================================================
            $('#form-operasional').on('submit', function(e) {
                let nominalValid = true;
                $('.nominal-display').each(function() {
                    const digits = bersih(this.value);
                    $(this).closest('td').find('.nominal-hidden').val(digits);
                    if (!digits) nominalValid = false;
                });
                if (!nominalValid) {
                    e.preventDefault();
                    alert('⚠️ Nominal tidak boleh kosong atau nol.');
                    return false;
                }

                const sisa  = getSisaSaldoBs();
                const total = hitungTotal();

                // Saldo diperiksa di server saat Konfirmasi.

            });

            hitungTotal();

            // ============================================================
            // 🎯 6. PERIOD LOCK FRONTEND (DENGAN FLATPICKR)
            // ============================================================
            const tglInput  = document.getElementById('tanggal_picker');
            const warning   = document.getElementById('periodWarning');
            const submitButtons = document.querySelectorAll('#form-operasional button[name="save_action"]');
            
            // 🎯 Daftar periode OPEN dari backend (diinject dari Controller)
            const openPeriods = @json($openPeriods);

            if (tglInput && typeof flatpickr !== 'undefined') {
                flatpickr(tglInput, {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d F Y", // Tampilan user: 03 September 2026
                    locale: "id",       // Bahasa Indonesia
                    maxDate: "today",   // 🎯 Tidak bisa pilih masa depan
                    defaultDate: tglInput.value || new Date(),
                    disable: [
                        function(date) {
                            const y = date.getFullYear();
                            const m = String(date.getMonth() + 1).padStart(2, '0');
                            const periodeStr = `${y}-${m}`;
                            // 🎯 NON-AKTIFKAN (abu-abu) jika bulan TIDAK ADA di daftar OPEN
                            return !openPeriods.includes(periodeStr);
                        }
                    ],
                    onChange: function(selectedDates, dateStr, instance) {
                        validatePeriod(dateStr);
                    }
                });
            }

            async function validatePeriod(dateStr) {
                if (!dateStr) return;
                const periode = dateStr.slice(0, 7);
                
                // 🎯 Validasi cepat dari data frontend
                if (!openPeriods.includes(periode)) {
                    document.getElementById('periodWarningText').textContent =
                        `Periode ${periode} tidak aktif atau sudah DITUTUP. Pilih tanggal di bulan yang berjalan/OPEN.`;
                    warning.classList.remove('d-none');
                    submitButtons.forEach(button => button.disabled = true);
                    return;
                }

                try {
                    // Validasi ganda ke API untuk memastikan sinkronisasi real-time
                    const data = await (await fetch(`/api/periode/status?periode=${periode}`)).json();
                    if (!data.open) {
                        document.getElementById('periodWarningText').textContent =
                            `Periode ${periode} sudah DITUTUP. Transaksi akan ditolak sistem — pilih tanggal di periode OPEN.`;
                        warning.classList.remove('d-none');
                        submitButtons.forEach(button => button.disabled = true);
                    } else {
                        warning.classList.add('d-none');
                        submitButtons.forEach(button => button.disabled = false);
                    }
                } catch (err) {
                    console.warn('Gagal cek status periode:', err);
                    // Fallback: jika API gagal tapi frontend bilang open, biarkan saja (backend akan catch juga)
                    warning.classList.add('d-none');
                    submitButtons.forEach(button => button.disabled = false);
                }
            }

            // Trigger validasi awal jika sudah ada nilai default
            if (tglInput && tglInput._flatpickr && tglInput._flatpickr.selectedDates.length > 0) {
                validatePeriod(tglInput._flatpickr.selectedDates[0].toISOString().slice(0, 10));
            } else if (tglInput && tglInput.value) {
                validatePeriod(tglInput.value);
            }
        });
    </script>
</body>
</html>

~~~~

## FILE: resources/views/transaksi_operasional/index.blade.php

SHA-256: `6d86b073899e31e7190c0e9ee9a69c29e2ebff43dde60c8bac0e0509327f8698`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Operasional Area Sales</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    {{-- Font modern --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --purple-gradient: linear-gradient(135deg, #6f42c1 0%, #9b5de5 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            --info-gradient: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
            --primary-color: #667eea;
            --purple-color: #6f42c1;
            --bg-color: #f0f4f8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            background-image:
                radial-gradient(#667eea 0.5px, transparent 0.5px),
                radial-gradient(#667eea 0.5px, var(--bg-color) 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            min-height: 100vh;
        }

        .text-muted {
            color: #718096 !important;
        }

        /* =========================
           PAGE HEADER
        ========================= */
        .page-header {
            background: var(--purple-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(111, 66, 193, 0.3);
        }

        .page-header h1 {
            font-weight: 800;
            margin: 0;
            font-size: 2rem;
        }

        .page-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        /* =========================
           BUTTONS
        ========================= */
        .btn-modern {
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .btn-light-header {
            background: white;
            color: var(--purple-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-light-header:hover,
        .btn-light-header:focus {
            background: white;
            color: var(--purple-color);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(4px);
        }

        .btn-ghost:hover,
        .btn-ghost:focus {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
            transition: all 0.3s ease;
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.5);
            color: white;
        }

        /* =========================
           CARD
        ========================= */
        .card-modern {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header-modern {
            background: linear-gradient(135deg, rgba(111, 66, 193, 0.10) 0%, rgba(155, 93, 229, 0.10) 100%);
            border-bottom: 2px solid rgba(111, 66, 193, 0.12);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-header-modern h5 {
            margin: 0;
            font-weight: 700;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .card-header-modern h5 i {
            font-size: 1.5rem;
            background: var(--purple-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .count-badge {
            background: rgba(111, 66, 193, 0.12);
            color: var(--purple-color);
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0.45rem 0.9rem;
            border-radius: 20px;
        }

        /* =========================
           FILTER / EXPORT
        ========================= */
        .filter-select {
            width: auto;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #4a5568;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .filter-select:focus {
            border-color: var(--purple-color);
            box-shadow: 0 0 0 4px rgba(111, 66, 193, 0.1);
        }

        /* =========================
           TABLE
        ========================= */
        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern thead th {
            background: linear-gradient(135deg, rgba(111, 66, 193, 0.05) 0%, rgba(155, 93, 229, 0.05) 100%);
            border-bottom: 2px solid #e2e8f0;
            padding: 1rem 1.25rem;
            font-weight: 700;
            color: #2d3748;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table-modern tbody td {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid #f0f4f8;
            vertical-align: middle;
            color: #4a5568;
        }

        .table-modern tbody tr:hover {
            background: rgba(111, 66, 193, 0.03);
        }

        .table-modern tbody tr:last-child td {
            border-bottom: none;
        }

        .code-badge {
            background: rgba(111, 66, 193, 0.10);
            color: var(--purple-color);
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            font-size: 0.88rem;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--purple-gradient);
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cell-icon {
            color: #a0aec0;
            margin-right: 0.4rem;
        }

        .desc-cell {
            max-width: 360px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* =========================
           ACTION BUTTONS
        ========================= */
        .btn-action {
            border: none;
            border-radius: 10px;
            padding: 0.45rem 0.9rem;
            font-weight: 600;
            font-size: 0.82rem;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.3s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-action.detail {
            background: var(--info-gradient);
            box-shadow: 0 3px 10px rgba(0, 91, 234, 0.3);
        }

        .btn-action.detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 91, 234, 0.4);
            color: white;
        }

        .btn-action.hapus {
            background: var(--danger-gradient);
            box-shadow: 0 3px 10px rgba(238, 9, 121, 0.3);
        }

        .btn-action.hapus:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(238, 9, 121, 0.4);
            color: white;
        }

        /* =========================
           EMPTY STATE
        ========================= */
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
        }

        .empty-state .icon-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(111, 66, 193, 0.12), rgba(155, 93, 229, 0.12));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .empty-state .icon-circle i {
            font-size: 2.5rem;
            background: var(--purple-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .empty-state h6 {
            font-weight: 700;
            color: #2d3748;
        }

        /* =========================
           PAGINATION
        ========================= */
        .pagination {
            gap: 4px;
        }

        .page-link {
            border: none;
            border-radius: 10px;
            color: var(--purple-color);
            font-weight: 600;
            margin: 0;
            padding: 0.5rem 0.85rem;
        }

        .page-link:hover {
            background: rgba(111, 66, 193, 0.10);
            color: var(--purple-color);
        }

        .page-item.active .page-link {
            background: var(--purple-gradient);
            color: white;
            box-shadow: 0 4px 10px rgba(111, 66, 193, 0.4);
        }

        .page-item.disabled .page-link {
            color: #a0aec0;
            background: transparent;
        }

        /* =========================
           ALERT
        ========================= */
        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.10);
            padding: 1rem 1.5rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.5rem;
            }

            .card-header-modern {
                flex-direction: column;
                align-items: flex-start;
            }

            .desc-cell {
                max-width: 220px;
            }

            .btn-action {
                padding: 0.4rem 0.75rem;
            }
        }
    </style>
</head>

<body>
    @include('partials.application-time')
    <!-- Page Header -->
    <div class="page-header">
        <div class="container" style="max-width: 1100px;">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1><i class="bi bi-briefcase me-3"></i>Operasional Area Sales</h1>
                    <p class="mb-0">Daftar transaksi operasional untuk aktivitas area sales</p>
                </div>

                <div class="col-lg-6 d-flex justify-content-lg-end gap-2 flex-wrap mt-3 mt-lg-0">
                    <a href="{{ route('transaksi_operasional.create') }}" class="btn btn-light-header btn-modern">
                        <i class="bi bi-plus-lg"></i> Tambah Baru
                    </a>
                    <a href="{{ route('dashboard.transaksi') }}" class="btn btn-ghost btn-modern">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1100px; margin-bottom: 3rem;">
        
        @if(session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                    <div>
                        <strong>Berhasil!</strong> {{ session('success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card-modern animate-slide-in">
            <div class="card-header-modern">
                <h5>
                    <i class="bi bi-list-check"></i>
                    Daftar Transaksi Operasional Area Sales
                    <span class="count-badge">{{ $transaksis->total() }} Transaksi</span>
                </h5>

                {{-- 🎯 EXPORT FORM --}}
                <form method="GET" action="{{ route('transaksi_operasional.index') }}" class="d-inline-flex gap-2 align-items-center flex-wrap">
    <select name="periode" class="form-select form-select-sm filter-select" onchange="this.form.submit()">
        <option value="">Semua Periode</option>
        @foreach(\App\Models\Periode::orderBy('periode', 'desc')->get() as $p)
            <option value="{{ $p->periode }}" {{ request('periode') === $p->periode ? 'selected' : '' }}>
                {{ $p->periode }}
            </option>
        @endforeach
    </select>

    <a href="{{ route('transaksi_operasional.export', request()->query()) }}" class="btn btn-sm btn-success-modern btn-modern">
        <i class="bi bi-download"></i> Export
    </a>
</form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
    <tr>
        <th><i class="bi bi-hash me-2"></i>ID Transaksi</th>
        <th><i class="bi bi-calendar-event me-2"></i>Tanggal</th>
        <th><i class="bi bi-person me-2"></i>User Karyawan</th>
        <th><i class="bi bi-file-text me-2"></i>Deskripsi Kegiatan</th>
        <th class="text-center"><i class="bi bi-check-circle me-2"></i>Status</th>
        <th class="text-center">Aksi</th>
    </tr>
</thead>

                       <tbody>
    @forelse($transaksis as $t)
        <tr>
            <td>
                <span class="code-badge">{{ $t->transaksi_id }}</span>
            </td>

            <td>
                <i class="bi bi-calendar3 cell-icon"></i>
                {{ \Carbon\Carbon::parse($t->tanggal)->format('d M Y') }}
            </td>

            <td>
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar">
                        {{ strtoupper(substr($t->user->name ?? $t->user->nama ?? 'U', 0, 1)) }}
                    </span>
                    <span class="fw-semibold">
                        {{ $t->user->name ?? $t->user->nama ?? 'Unknown' }}
                    </span>
                </div>
            </td>

            <td>
                <div class="desc-cell" title="{{ $t->description }}">
                    {{ $t->description ?? '-' }}
                </div>
            </td>

            <td class="text-center">
                @if($t->status === 'DRAFT')
                    <span class="badge bg-warning text-dark" style="padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-file-earmark-text me-1"></i> Draft
                    </span>
                @else
                    <span class="badge bg-success" style="padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-1"></i> Dikonfirmasi
                    </span>
                @endif
            </td>

            <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                    {{-- Tombol Detail (selalu ada) --}}
                    <a href="{{ route('transaksi_operasional.show', $t->id) }}" class="btn-action detail">
                        <i class="bi bi-eye"></i> Detail
                    </a>

                    @if($t->status === 'DRAFT' && auth()->user()->canAccessTransaction($t) && app(\App\Services\PeriodService::class)->isOpen($t->periode))
                        {{-- Tombol Konfirmasi (hanya untuk DRAFT) --}}
                        <form action="{{ route('transaksi_operasional.validate', $t->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn-action" style="background: var(--success-gradient); box-shadow: 0 3px 10px rgba(17, 153, 142, 0.3);"
                                    onclick="return confirm('Konfirmasi transaksi ini? Pemakaian BS akan dibukukan. Transaksi final tidak dapat diedit.')">
                                <i class="bi bi-check-circle"></i> Konfirmasi
                            </button>
                        </form>

                        {{-- Tombol Hapus (hanya untuk DRAFT) --}}
                        <form action="{{ route('transaksi_operasional.destroy', $t->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn-action hapus" onclick="return confirm('Yakin hapus draft transaksi operasional ini?')">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>
                    @else
                        {{-- Untuk VALIDATED, hanya tampilkan teks --}}
                        <span class="text-muted small fst-italic">{{ $t->status === 'VALIDATED' ? 'Dikonfirmasi' : 'Hanya lihat' }}</span>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <div class="icon-circle">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h6>Belum ada data transaksi</h6>
                    <p class="text-muted mb-3">
                        Mulai buat transaksi operasional area sales pertama Anda.
                    </p>
                    <a href="{{ route('transaksi_operasional.create') }}" class="btn btn-success-modern btn-modern">
                        <i class="bi bi-plus-lg"></i> Buat Transaksi Pertama
                    </a>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
                    </table>
                </div>
            </div>

            @if($transaksis->hasPages())
                <div class="card-footer bg-white border-0 d-flex justify-content-end px-4 pb-4 pt-3">
                    {{ $transaksis->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

~~~~

## FILE: resources/views/transaksi_operasional/show.blade.php

SHA-256: `473cc8a5d6056a66715c95dbb70fe2fb86baaa1150b95491ab05cb6b625358a7`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Operasional Area Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --purple-gradient: linear-gradient(135deg, #6f42c1 0%, #9b5de5 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            --purple-color: #6f42c1;
            --bg-color: #f0f4f8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            background-image: radial-gradient(#6f42c1 0.5px, transparent 0.5px), radial-gradient(#6f42c1 0.5px, var(--bg-color) 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            min-height: 100vh;
        }

        .text-muted { color: #718096 !important; }

        /* 🎯 Page Header */
        .page-header {
            background: var(--purple-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(111, 66, 193, 0.3);
        }
        .page-header h1 { font-weight: 800; margin: 0; font-size: 2rem; }
        .page-header p  { margin: 0.5rem 0 0 0; opacity: 0.9; }
        .page-header .id-chip {
            display: inline-block;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            padding: 0.3rem 0.9rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            margin-top: 0.6rem;
        }

        .btn-modern {
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .btn-ghost {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(4px);
        }
        .btn-ghost:hover { background: rgba(255, 255, 255, 0.25); color: white; }

        /* 🎯 Card statis — TANPA hover */
        .card-modern {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header-modern {
            background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(155, 93, 229, 0.1) 100%);
            border-bottom: 2px solid rgba(111, 66, 193, 0.12);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .card-header-modern.success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            border-bottom-color: rgba(17, 153, 142, 0.2);
        }
        .card-header-modern h5 {
            margin: 0;
            font-weight: 700;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .card-header-modern h5 i {
            font-size: 1.5rem;
            background: var(--purple-gradient);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .card-header-modern.success h5 i {
            background: var(--success-gradient);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        .count-badge {
            background: rgba(111, 66, 193, 0.12);
            color: var(--purple-color);
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0.45rem 0.9rem;
            border-radius: 20px;
        }

        /* 🎯 Info tiles (rangkuman) */
        .info-tile {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 1.1rem 1.25rem;
            height: 100%;
        }
        .info-tile .label {
            color: #718096;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.72rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.5rem;
        }
        .info-tile .label i { color: #9b5de5; }
        .info-tile .value {
            font-weight: 700;
            color: #2d3748;
            font-size: 0.98rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .code-badge {
            background: rgba(111, 66, 193, 0.1);
            color: var(--purple-color);
            font-weight: 800;
            padding: 0.35rem 0.8rem;
            border-radius: 10px;
            font-size: 0.92rem;
            letter-spacing: 0.5px;
        }

        .avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--purple-gradient);
            color: white; font-weight: 700; font-size: 0.85rem;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* 🎯 Tabel modern */
        .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-modern thead th {
            background: linear-gradient(135deg, rgba(111, 66, 193, 0.05) 0%, rgba(155, 93, 229, 0.05) 100%);
            border-bottom: 2px solid #e2e8f0;
            padding: 1rem 1.25rem;
            font-weight: 700;
            color: #2d3748;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .table-modern tbody td {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid #f0f4f8;
            vertical-align: middle;
            color: #4a5568;
        }
        .table-modern tbody tr:hover { background: rgba(111, 66, 193, 0.03); }
        .table-modern tbody tr:last-child td { border-bottom: none; }

        .table-modern tfoot td {
            background: linear-gradient(135deg, rgba(111, 66, 193, 0.08) 0%, rgba(155, 93, 229, 0.08) 100%);
            border-top: 2px solid rgba(111, 66, 193, 0.2);
            padding: 1.1rem 1.25rem;
            font-weight: 800;
            color: #2d3748;
        }
        .total-value {
            color: var(--purple-color);
            font-size: 1.1rem;
            font-weight: 800;
        }

        .row-num {
            width: 28px; height: 28px; border-radius: 50%;
            background: rgba(111, 66, 193, 0.1);
            color: var(--purple-color);
            font-weight: 700; font-size: 0.82rem;
            display: inline-flex; align-items: center; justify-content: center;
        }

        .category-chip {
            background: rgba(155, 93, 229, 0.1);
            color: var(--purple-color);
            font-weight: 600;
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .nominal-cell { font-weight: 700; color: #2d3748; white-space: nowrap; }

        /* 🎯 Empty state */
        .empty-state { text-align: center; padding: 3.5rem 1rem; }
        .empty-state .icon-circle {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, rgba(111, 66, 193, 0.12), rgba(155, 93, 229, 0.12));
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
        }
        .empty-state .icon-circle i {
            font-size: 2.2rem;
            background: var(--purple-gradient);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .empty-state h6 { font-weight: 700; color: #2d3748; }

        /* 🎯 Form inputs */
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.7rem 1rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--purple-color);
            box-shadow: 0 0 0 4px rgba(111, 66, 193, 0.1);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
            transition: all 0.3s ease;
        }
        .btn-success-modern:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(17, 153, 142, 0.5); color: white; }

        .btn-danger-modern {
            background: var(--danger-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(238, 9, 121, 0.4);
            transition: all 0.3s ease;
        }
        .btn-danger-modern:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(238, 9, 121, 0.5); color: white; }

        .btn-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center; padding: 0;
        }

        /* 🎯 Tombol submit utama — statis, TANPA hover */
        .btn-submit-success {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
            border-radius: 15px;
            font-weight: 700;
            letter-spacing: 0.3px;
            border: none;
        }
        .btn-submit-success:hover, .btn-submit-success:focus, .btn-submit-success:active {
            background: var(--success-gradient);
            color: white;
            transform: none;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
        }

        .alert-custom {
            border-radius: 15px; border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
        }

        .info-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-left: 4px solid #667eea;
            border-radius: 12px;
            padding: 1rem 1.5rem;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-in { animation: slideIn 0.5s ease; }

        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.5rem; }
            .card-header-modern { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')
    @php
    $isPeriodClosed = !app(\App\Services\PeriodService::class)->isOpen($transaksi->periode);
    $isDraft = $transaksi->status === 'DRAFT' && auth()->user()->canAccessTransaction($transaksi);
    $isEditMode = $isDraft && !$isPeriodClosed && request('edit') == 1;
@endphp

<!-- PAGE HEADER (Tambahkan tombol Edit di pojok kanan atas) -->
<div class="page-header">
    <div class="container" style="max-width: 1000px;">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1><i class="bi bi-receipt me-3"></i>Detail Nota Operasional</h1>
                <p class="mb-0">Rangkuman nota & mutasi pengeluaran item biaya</p>
                <span class="id-chip"><i class="bi bi-hash me-1"></i>{{ $transaksi->transaksi_id }}</span>
                
                @if($isEditMode)
                    <span class="id-chip" style="background: rgba(13, 110, 253, 0.2); border-color: rgba(13, 110, 253, 0.4); color: #0d6efd;">
                        <i class="bi bi-pencil-square me-1"></i> MODE EDIT
                    </span>
                @elseif($transaksi->status === 'DRAFT')
                    <span class="id-chip" style="background: rgba(255, 193, 7, 0.2); border-color: rgba(255, 193, 7, 0.4); color: #ffc107;">
                        <i class="bi bi-file-earmark-text me-1"></i> DRAFT
                    </span>
                @else
                    <span class="id-chip" style="background: rgba(25, 135, 84, 0.2); border-color: rgba(25, 135, 84, 0.4); color: #198754;">
                        <i class="bi bi-check-circle-fill me-1"></i> Dikonfirmasi
                    </span>
                @endif
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 d-flex justify-content-lg-end gap-2 flex-wrap">
                {{-- 🎯 TOMBOL EDIT --}}
                @if($isDraft && !$isPeriodClosed && !$isEditMode)
                    <a href="{{ route('transaksi_operasional.show', $transaksi->id) }}?edit=1" class="btn btn-modern" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);">
                        <i class="bi bi-pencil-square"></i> Edit Transaksi
                    </a>
                @endif
                <a href="{{ route('transaksi_operasional.index') }}" class="btn btn-ghost btn-modern">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

    <div class="container" style="max-width: 1000px; margin-bottom: 3rem;">
        
        @if(session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                    <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-custom alert-dismissible fade show mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-circle-fill me-3 fs-4"></i>
                    <div>
                        <strong>Gagal:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- 🎯 CARD 1: RANGKUMAN NOTA -->
        <div class="card-modern animate-slide-in">
            <div class="card-header-modern">
                <h5>
                    <i class="bi bi-pin-map"></i> Rangkuman Nota Operasional Sales
                    <span class="count-badge">{{ $transaksi->details->count() }} Item Biaya</span>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="info-tile">
                            <div class="label"><i class="bi bi-hash"></i>ID Transaksi</div>
                            <div class="value"><span class="code-badge">{{ $transaksi->transaksi_id }}</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-tile">
                            <div class="label"><i class="bi bi-calendar-event"></i>Tanggal Transaksi</div>
                            <div class="value"><i class="bi bi-calendar3 text-muted"></i>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d F Y') }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-tile">
                            <div class="label"><i class="bi bi-person-badge"></i>Sales Terkait</div>
                            <div class="value">
                                <span class="avatar">{{ strtoupper(substr($transaksi->user->name ?? $transaksi->user->nama ?? '-', 0, 1)) }}</span>
                                {{ $transaksi->user->name ?? $transaksi->user->nama ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-tile">
                            <div class="label"><i class="bi bi-wallet2"></i>Pemilik Saldo BS</div>
                            <div class="value">
                                @if($transaksi->bsCashOwnerId())
                                    <span class="code-badge">{{ $transaksi->bsOwner?->nama ?? $transaksi->bonSementara?->user?->nama ?? '-' }}</span>
                                @else
                                    <span class="text-muted">Arsip tanpa BS</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-tile">
                            <div class="label"><i class="bi bi-chat-left-text"></i>Keterangan Umum</div>
                            <div class="value">{{ $transaksi->description ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🎯 CARD 2: MUTASI PENGELUARAN -->
        <div class="card-modern animate-slide-in" style="animation-delay: 0.1s">
            <div class="card-header-modern">
                <h5><i class="bi bi-bar-chart-line"></i> Mutasi Pengeluaran Item Biaya</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th width="8%" class="text-center">No.</th>
                                <th width="30%"><i class="bi bi-tag me-2"></i>Kategori Biaya</th>
                                <th width="40%"><i class="bi bi-chat-left-text me-2"></i>Keterangan Item (Remark)</th>
                                <th width="22%" class="text-end"><i class="bi bi-currency-dollar me-2"></i>Nominal Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalKeseluruhan = 0; @endphp
                            
                            @forelse($transaksi->details as $index => $detail)
                                @php $totalKeseluruhan += $detail->nominal; @endphp
                                <tr>
                                    <td class="text-center"><span class="row-num">{{ $index + 1 }}</span></td>
                                    <td>
                                        <span class="category-chip">
                                            <i class="bi bi-tag-fill"></i>
                                            {{ $detail->costCategory->nama ?? $detail->costCategory->nama ?? 'Kategori #'.$detail->cost_category_id }}
                                        </span>
                                    </td>
                                    <td>{{ $detail->remark }}</td>
                                    <td class="text-end nominal-cell">Rp {{ number_format($detail->nominal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <div class="icon-circle"><i class="bi bi-inbox"></i></div>
                                            <h6>Rincian item biaya kosong</h6>
                                            <p class="text-muted mb-0">
                                                @if($transaksi->status === 'DRAFT')
                                                    Tambahkan rincian biaya pertama melalui form di bawah.
                                                @else
                                                    Tidak ada rincian biaya pada transaksi ini.
                                                @endif
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($totalKeseluruhan > 0)
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">TOTAL KESELURUHAN NOTA :</td>
                                <td class="text-end"><span class="total-value">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</span></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- 🎯 CARD 3 & 4: FORM TAMBAH RINCIAN & KONFIRMASI (Hanya untuk DRAFT) -->
        @php
            $isPeriodClosed = !app(\App\Services\PeriodService::class)->isOpen($transaksi->periode);
            $isDraft = $transaksi->status === 'DRAFT' && auth()->user()->canAccessTransaction($transaksi);
        @endphp

       @if($isDraft && !$isPeriodClosed)
    
    @if($isEditMode)
        {{-- ================= MODE EDIT ================= --}}
        <form action="{{ route('transaksi_operasional.update', $transaksi->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Card Edit Header --}}
            <div class="card-modern animate-slide-in">
                <div class="card-header-modern success">
                    <h5><i class="bi bi-pencil-square"></i> Edit Informasi Utama</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('transaksi_operasional.show', $transaksi->id) }}" class="btn btn-modern" style="background: #f0f4f8; color: #4a5568;">
                            <i class="bi bi-x-lg"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success-modern btn-modern">
                            <i class="bi bi-check-lg"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted">Tanggal Transaksi</label>
                            <input type="date" name="tanggal" value="{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            @include('partials.transaction-funding', ['fundingType' => 'operasional_sales', 'fundingDocument' => $transaksi])
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted">Deskripsi Kegiatan</label>
                            <input type="text" name="description" value="{{ $transaksi->description }}" class="form-control" placeholder="Masukkan deskripsi...">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Edit Details --}}
            <div class="card-modern animate-slide-in" style="animation-delay: 0.1s">
                <div class="card-header-modern success">
                    <h5><i class="bi bi-list-ul"></i> Edit Rincian Item Biaya</h5>
                    <button type="button" class="btn btn-sm btn-success-modern btn-modern" id="btn-tambah-baris-edit">
                        <i class="bi bi-plus-lg"></i> Tambah Baris Baru
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th width="30%">Cost Category</th>
                                    <th width="40%">Remark (Keterangan)</th>
                                    <th width="20%">Nominal (Rp)</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-detail-edit">
                                @foreach($transaksi->details as $index => $detail)
                                    <tr class="detail-row-edit">
                                        <td>
                                            <input type="hidden" name="details[{{ $detail->id }}][id]" value="{{ $detail->id }}">
                                            <select name="details[{{ $detail->id }}][cost_category_id]" class="form-select" required>
                                                <option value="">-- Pilih --</option>
                                                @foreach($cost_categories as $cc)
                                                    <option value="{{ $cc->id }}" {{ $detail->cost_category_id == $cc->id ? 'selected' : '' }}>
                                                        {{ $cc->name ?? $cc->nama ?? $cc->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="details[{{ $detail->id }}][remark]" value="{{ $detail->remark }}" class="form-control" required>
                                        </td>
                                        <td>
                                            <input type="number" name="details[{{ $detail->id }}][nominal]" value="{{ $detail->nominal }}" class="form-control" required min="1">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus-detail" title="Tandai untuk dihapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <input type="checkbox" name="delete_details[]" value="{{ $detail->id }}" class="d-none check-delete-detail">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>

    @else
        {{-- ================= MODE VIEW NORMAL (Kode Asli Anda) ================= --}}
        {{-- Form Tambah Rincian & Tombol Konfirmasi --}}
        
        <div class="card-modern animate-slide-in" style="animation-delay: 0.2s">
            <div class="card-header-modern success">
                <h5><i class="bi bi-plus-circle"></i> Tambah Rincian Biaya Baru</h5>
                <button type="button" class="btn btn-sm btn-success-modern btn-modern" id="btn-tambah-baris-show">
                    <i class="bi bi-plus-lg"></i> Tambah Baris
                </button>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('transaksi_operasional.store_detail', $transaksi->id) }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table-modern" id="tabel-detail-show" style="border: 2px solid #e2e8f0; border-radius: 15px; overflow: hidden;">
                            <thead>
                                <tr>
                                    <th width="30%">Cost Category</th>
                                    <th width="40%">Remark (Keterangan)</th>
                                    <th width="20%">Nominal (Rp)</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="baris-show-0">
                                    <td>
                                        <select name="details[0][cost_category_id]" class="form-select" required>
                                            <option value="">-- Pilih Cost Category --</option>
                                            @foreach($cost_categories as $cc)
                                                <option value="{{ $cc->id }}">{{ $cc->name ?? $cc->nama ?? $cc->code }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="details[0][remark]" class="form-control" placeholder="Misal: Bensin Motor" required>
                                    </td>
                                    <td>
                                        <input type="number" name="details[0][nominal]" class="form-control" placeholder="0" min="1" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus-show" data-id="0" title="Hapus baris ini">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-submit-success btn-lg py-3">
                            <i class="bi bi-save me-2 fs-5"></i> Simpan Rincian Baru ke Draft Ini
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-modern animate-slide-in" style="animation-delay: 0.3s">
            <div class="card-header-modern" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); border-bottom-color: rgba(17, 153, 142, 0.3);">
                <h5 style="color: #11998e;"><i class="bi bi-check-circle"></i> Konfirmasi Transaksi</h5>
            </div>
            <div class="card-body p-4">
                <div class="info-box mb-3">
                    <i class="bi bi-info-circle me-2 text-primary"></i>
                    <small class="text-muted">
                        <strong>Perhatian:</strong> Setelah dikonfirmasi, transaksi akan mengurangi saldo BS. Saldo BS gabungan harus mencukupi. Transaksi final tidak dapat diedit atau dihapus.
                    </small>
                </div>
                <form action="{{ route('transaksi_operasional.validate', $transaksi->id) }}" method="POST">
                    @csrf
                    <div class="d-grid">
                        <button type="submit" class="btn btn-submit-success btn-lg py-3"
                                onclick="return confirm('Konfirmasi transaksi ini? Pemakaian BS akan dibukukan. Transaksi final tidak dapat diedit atau dihapus.')">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i> Konfirmasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@elseif($isDraft && $isPeriodClosed)
    {{-- TAMPILAN: DRAFT tapi Periode Sudah CLOSED --}}
    <div class="card-modern animate-slide-in" style="animation-delay: 0.2s">
        <div class="card-header-modern" style="background: linear-gradient(135deg, rgba(238, 9, 121, 0.1) 0%, rgba(255, 106, 0, 0.1) 100%); border-bottom-color: rgba(238, 9, 121, 0.2);">
            <h5 style="color: #ee0979;"><i class="bi bi-lock-fill"></i> Periode Sudah Di-Close</h5>
        </div>
        <div class="card-body p-4 text-center">
            <p class="text-muted mb-3">
                Draft transaksi ini <strong>tidak dapat diedit maupun dikonfirmasi</strong> karena periode 
                <span class="code-badge" style="background: rgba(238, 9, 121, 0.1); color: #ee0979;">{{ $transaksi->periode }}</span> 
                sudah di-close oleh Finance.
            </p>
        </div>
    </div>

@else
    {{-- TAMPILAN: SUDAH VALIDATED --}}
    <div class="card-modern animate-slide-in" style="animation-delay: 0.2s">
        <div class="card-header-modern" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(32, 168, 107, 0.1) 100%); border-bottom-color: rgba(25, 135, 84, 0.2);">
            <h5 style="color: #198754;"><i class="bi bi-lock-fill"></i> Transaksi Sudah Dikonfirmasi</h5>
        </div>
        <div class="card-body p-4 text-center">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(32, 168, 107, 0.1)); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: #198754;"></i>
            </div>
            <p class="text-muted mb-2">Waktu Konfirmasi: <strong>{{ \App\Support\AppTime::format($transaksi->validated_at) }}</strong></p>
            <p class="text-muted mb-0">
                Transaksi ini <strong>sudah dikonfirmasi</strong>. {{ $transaksi->bsCashOwnerId() ? 'Pemakaian saldo BS gabungan telah dibukukan.' : 'Arsip tanpa BS.' }}
                Tidak ada perubahan yang dapat dilakukan lagi.
            </p>
        </div>
    </div>
@endif
    </div>

    <script>
        $(document).ready(function() {
            let rowIndexShow = 1; 
            
            const costCategoryOptions = `
                <option value="">-- Pilih Cost Category --</option>
                @foreach($cost_categories as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->name ?? $cc->nama ?? $cc->code }}</option>
                @endforeach
            `;

            $('#btn-tambah-baris-show').click(function() {
                let barisBaru = `
                    <tr id="baris-show-${rowIndexShow}" class="animate-slide-in">
                        <td>
                            <select name="details[${rowIndexShow}][cost_category_id]" class="form-select" required>
                                ${costCategoryOptions}
                            </select>
                        </td>
                        <td>
                            <input type="text" name="details[${rowIndexShow}][remark]" class="form-control" placeholder="Keterangan tambahan..." required>
                        </td>
                        <td>
                            <input type="number" name="details[${rowIndexShow}][nominal]" class="form-control" placeholder="0" min="1" required>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus-show" data-id="${rowIndexShow}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#tabel-detail-show tbody').append(barisBaru);
                rowIndexShow++; 
            });

            // Handler hapus dengan pengaman "baris terakhir"
            $(document).on('click', '.btn-hapus-show', function() {
                const totalBaris = $('#tabel-detail-show tbody tr').length;

                if (totalBaris <= 1) {
                    const row = $(this).closest('tr');
                    row.find('select').val('');
                    row.find('input[type="text"]').val('');
                    row.find('input[type="number"]').val('');
                    row.fadeOut(150, function() { $(this).fadeIn(150); });
                    return;
                }

                const idBaris = $(this).data('id');
                $('#baris-show-' + idBaris).fadeOut(300, function() {
                    $(this).remove();
                });
            });
        });

        // === LOGIC MODE EDIT ===

// 1. Tombol Hapus Detail Lama (Tandai untuk dihapus, bukan langsung hapus)
$(document).on('click', '.btn-hapus-detail', function() {
    const row = $(this).closest('tr');
    const checkbox = row.find('.check-delete-detail');
    
    if (checkbox.is(':checked')) {
        // Batalkan penghapusan
        checkbox.prop('checked', false);
        row.removeClass('text-decoration-line-through opacity-50');
        row.find('input:not(.check-delete-detail), select').prop('disabled', false);
    } else {
        // Tandai untuk dihapus
        checkbox.prop('checked', true);
        row.addClass('text-decoration-line-through opacity-50');
        row.find('input:not(.check-delete-detail), select').prop('disabled', true);
    }
});

// 2. Tombol Tambah Baris Baru di Mode Edit
$('#btn-tambah-baris-edit').click(function() {
    let index = $('#tabel-detail-edit tr.detail-row-new').length;
    let barisBaru = `
        <tr class="detail-row-new animate-slide-in">
            <td>
                <select name="new_details[${index}][cost_category_id]" class="form-select" required>
                    <option value="">-- Pilih Cost Category --</option>
                    @foreach($cost_categories as $cc)
                        <option value="{{ $cc->id }}">{{ $cc->name ?? $cc->nama ?? $cc->code }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="new_details[${index}][remark]" class="form-control" placeholder="Keterangan tambahan..." required>
            </td>
            <td>
                <input type="number" name="new_details[${index}][nominal]" class="form-control" placeholder="0" min="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus-new">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#tabel-detail-edit').append(barisBaru);
});

// 3. Hapus Baris Baru yang belum di-save
$(document).on('click', '.btn-hapus-new', function() {
    $(this).closest('tr').fadeOut(300, function() { $(this).remove(); });
});
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

~~~~
