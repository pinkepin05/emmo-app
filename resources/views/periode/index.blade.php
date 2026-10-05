<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Periode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body:#f8fafc; --border-color:#e2e8f0; --text-primary:#0f172a; --text-secondary:#64748b; }
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg-body);color:var(--text-primary);}
        .card-custom{background:#fff;border:1px solid var(--border-color);border-radius:16px;overflow:hidden;}
        .table-custom th{background:#f8fafc;color:var(--text-secondary);font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;padding:1rem;border-bottom:1px solid var(--border-color);}
        .table-custom td{padding:1rem;border-bottom:1px solid var(--border-color);vertical-align:middle;}
        .btn-nav{background:#fff;border:1px solid var(--border-color);color:var(--text-secondary);padding:.5rem 1rem;border-radius:8px;text-decoration:none;font-size:.875rem;}
        .btn-nav:hover{color:var(--text-primary);background:#f1f5f9;}

        /* ✅ FIX 1: semua tombol aksi pakai inline-flex (bukan inline),
           agar padding vertikal dihitung → tidak terpotong/overlap lagi,
           dan ikon rata tengah dengan teks. */
        .btn-history,
        .btn-close-periode,
        .btn-reopen-periode{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.4rem;
            border-radius:8px;
            padding:.45rem .9rem;
            font-weight:600;
            font-size:.8rem;
            line-height:1.2;
            white-space:nowrap;
        }
        .btn-history{background:#fff;border:1px solid var(--border-color);color:var(--text-secondary);text-decoration:none;}
        .btn-history:hover{color:var(--text-primary);background:#f1f5f9;}
        .btn-close-periode{background:#ef4444;color:#fff;border:none;}
        .btn-close-periode:hover{background:#dc2626;}
        .btn-reopen-periode{background:#f59e0b;color:#fff;border:none;}
        .btn-reopen-periode:hover{background:#d97706;}

        /* ✅ FIX 2: wrapper aksi — satu baris, rata kanan, spacing konsisten,
           vertikal center. Form di dalamnya tidak menambah margin. */
        .action-wrap{display:flex;align-items:center;justify-content:flex-end;gap:.5rem;flex-wrap:nowrap;}
        .action-wrap form{margin:0;}
        .lock-note{display:inline-flex;align-items:center;gap:.35rem;white-space:nowrap;}

        .badge-status{padding:.4em .8em;border-radius:6px;font-weight:600;font-size:.75rem;text-transform:uppercase;}
        .badge-current{background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;}
        .audit-note{font-size:.72rem;color:var(--text-secondary);}
    </style>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @include('partials.mobile-assets')
</head>
<body class="py-5">
    @include('partials.application-time')
<div class="container" style="max-width:950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Manajemen Periode & Tutup Buku</h3>
        <a href="{{ route('dashboard.master_data') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div class="small">
            <strong>Mode Auto-Roll Aktif:</strong> Sistem otomatis membuka periode baru saat bulan di dunia nyata berganti.
            Finance dapat menutup periode lama setelah seluruh draft diselesaikan dan saldo diperiksa. Sisa saldo BS diteruskan ke periode berikutnya.
            Periode CLOSED dapat <strong>dibuka kembali</strong> selama periode sesudahnya belum memiliki transaksi.
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success border-0 shadow-sm" style="border-radius:12px;">✅ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger border-0 shadow-sm" style="border-radius:12px;"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="card-custom">
        {{-- ✅ FIX 3: aman di layar sempit, tombol tidak wrap acak --}}
        <div class="table-responsive">
            <table class="table-custom mb-0 w-100">
                <thead>
                <tr>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Ditutup Pada</th>
                    <th class="text-end pe-4" style="white-space:nowrap;">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($periods as $p)
                    <tr>
                        <td class="fw-semibold">
                            {{ $p->periode }}
                            @if($p->periode === $currentMonth)
                                <span class="badge-current badge ms-1" style="font-size:.65rem;">BULAN SAAT INI</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-status {{ $p->status === 'OPEN' ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $p->status }}</span>
                            @if($p->reopened_at)
                                <div class="audit-note mt-1"><i class="bi bi-unlock-fill"></i> Dibuka kembali: {{ \App\Support\AppTime::format($p->reopened_at) }} — {{ $p->reopen_reason }}</div>
                            @endif
                        </td>
                        <td class="text-muted small">
                            @if($p->closed_at)
                                {{ \App\Support\AppTime::format($p->closed_at) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="pe-4">
                            {{-- ✅ FIX 4: Riwayat + Buka Kembali sejajar satu baris --}}
                            <div class="action-wrap">
                                <a href="{{ route('periode.show', $p->periode) }}" class="btn-history">
                                    <i class="bi bi-clock-history"></i> Riwayat
                                </a>

                                @if($p->status === 'OPEN')
                                    {{-- 🎯 GUARD UI: Kunci tombol jika bulan riil belum melewati periode ini --}}
                                    @if($currentMonth <= $p->periode)
                                        <span class="lock-note text-muted small" title="Bulan di dunia nyata belum melewati periode ini">
                                            <i class="bi bi-lock-fill"></i> Terkunci
                                        </span>
                                    @else
                                        <form action="{{ route('periode.close', $p->id) }}" method="POST"
                                              onsubmit="return confirm('Tutup buku periode {{ $p->periode }}? Transaksi periode ini akan dibekukan.');">
                                            @csrf
                                            <button class="btn-close-periode"><i class="bi bi-lock-fill"></i> Tutup Buku</button>
                                        </form>
                                    @endif
                                @else
                                    <button type="button" class="btn-reopen-periode btn-reopen"
                                            data-bs-toggle="modal" data-bs-target="#reopenModal"
                                            data-action="{{ route('periode.reopen', $p->id) }}"
                                            data-periode="{{ $p->periode }}">
                                        <i class="bi bi-unlock-fill"></i> Buka Kembali
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada periode.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🎯 MODAL REOPEN (wajib alasan → audit) --}}
<div class="modal fade" id="reopenModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="reopenForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Buka Kembali Periode <span id="reopenLabel" class="fw-bold"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small mb-3">
                    Periode akan kembali menerima transaksi. Sistem menolak reopen bila periode sesudahnya
                    sudah berisi transaksi (menjaga integritas rollover saldo BS).
                </div>
                <label class="form-label small fw-semibold">Alasan (wajib — tercatat sebagai audit)</label>
                <input type="text" name="reason" class="form-control" required minlength="5"
                       placeholder="Contoh: salah klik tutup buku / koreksi transaksi">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-warning text-white fw-semibold">Buka Kembali</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-reopen').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('reopenLabel').textContent = btn.dataset.periode;
        document.getElementById('reopenForm').action = btn.dataset.action;
    });
});
</script>
</body>
</html>
