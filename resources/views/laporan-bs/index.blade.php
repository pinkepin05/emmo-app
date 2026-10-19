<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
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
    @include('partials.mobile-assets')
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
            Semua pencairan masuk ke saldo BS gabungan. Pemakaian Operasional Sales, Perjalanan Dinas, dan event mengurangi saldo setelah dikonfirmasi Finance. Riwayat penyelesaian lama tetap ditampilkan.
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
