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
