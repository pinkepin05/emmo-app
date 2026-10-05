<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Perjalanan Dinas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    {{-- 🎯 Font: Plus Jakarta Sans (konsisten dengan halaman lain) --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            --info-gradient: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
            --primary-color: #667eea;
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

        .text-muted { color: #718096 !important; }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }

        .page-header h1 { font-weight: 800; margin: 0; font-size: 2rem; }
        .page-header p  { margin: 0.5rem 0 0 0; opacity: 0.9; }

        /* 🎯 Tombol di atas gradient header */
        .btn-modern {
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-light-header {
            background: white;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(4px);
        }
        .btn-ghost:hover { background: rgba(255, 255, 255, 0.25); color: white; }

        /* 🎯 Card statis — TANPA hover effect */
        .card-modern {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header-modern {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
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
        }

        .card-header-modern h5 i {
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .count-badge {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.45rem 0.9rem;
            border-radius: 20px;
        }

        /* 🎯 Toolbar filter export */
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
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
            transition: all 0.3s ease;
        }
        .btn-success-modern:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(17, 153, 142, 0.5); color: white; }

        /* 🎯 Tabel modern */
        .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }

        .table-modern thead th {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
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

        .table-modern tbody tr:hover { background: rgba(102, 126, 234, 0.03); }
        .table-modern tbody tr:last-child td { border-bottom: none; }

        /* ID transaksi sebagai code badge */
        .code-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            font-size: 0.88rem;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        /* Avatar inisial user */
        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cell-icon { color: #a0aec0; margin-right: 0.4rem; }

        .desc-cell {
            max-width: 280px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* 🎯 Tombol aksi kecil */
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
        }

        .btn-action.detail {
            background: var(--info-gradient);
            box-shadow: 0 3px 10px rgba(0, 91, 234, 0.3);
        }
        .btn-action.detail:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 91, 234, 0.4); color: white; }

        .btn-action.hapus {
            background: var(--danger-gradient);
            box-shadow: 0 3px 10px rgba(238, 9, 121, 0.3);
        }
        .btn-action.hapus:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(238, 9, 121, 0.4); color: white; }

        /* 🎯 Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
        }
        .empty-state .icon-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .empty-state .icon-circle i {
            font-size: 2.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .empty-state h6 { font-weight: 700; color: #2d3748; }

        /* 🎯 Pagination custom */
        .pagination { gap: 4px; }
        .page-link {
            border: none;
            border-radius: 10px;
            color: #667eea;
            font-weight: 600;
            margin: 0;
            padding: 0.5rem 0.85rem;
        }
        .page-link:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .page-item.active .page-link {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.4);
        }
        .page-item.disabled .page-link { color: #a0aec0; background: transparent; }

        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            .desc-cell { max-width: 180px; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1><i class="bi bi-airplane me-3"></i>Perjalanan Dinas</h1>
                    <p class="mb-0">Daftar seluruh transaksi perjalanan dinas Anda</p>
                </div>
                <div class="col-lg-6 d-flex justify-content-lg-end gap-2 flex-wrap mt-3 mt-lg-0">
                    <a href="{{ route('transaksi_perjalanan_dinas.create') }}" class="btn btn-light-header btn-modern">
                        <i class="bi bi-plus-lg"></i> Transaksi Baru
                    </a>
                    <a href="{{ route('dashboard.transaksi') }}" class="btn btn-ghost btn-modern">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1200px; margin-bottom: 3rem;">
        @if(session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                    <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card-modern animate-slide-in">
            <div class="card-header-modern">
                <h5>
                    <i class="bi bi-list-check"></i> Daftar Transaksi Perjalanan Dinas
                    <span class="count-badge">{{ $transaksis->total() }} Transaksi</span>
                </h5>
                
                {{-- 🎯 EXPORT FORM --}}
                <form method="GET" action="{{ route('transaksi_perjalanan_dinas.export') }}" class="d-inline-flex gap-2 align-items-center flex-wrap">
                    <select name="periode" class="form-select form-select-sm filter-select">
                        <option value="">Semua Periode</option>
                        @foreach(\App\Models\Periode::orderBy('periode', 'desc')->get() as $p)
                            <option value="{{ $p->periode }}">{{ $p->periode }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-success-modern btn-modern">
                        <i class="bi bi-download"></i> Export
                    </button>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-2"></i>ID Transaksi</th>
                                <th><i class="bi bi-calendar-event me-2"></i>Tanggal</th>
                                <th><i class="bi bi-person me-2"></i>User</th>
                                <th><i class="bi bi-geo-alt me-2"></i>Tujuan</th>
                                <th><i class="bi bi-file-text me-2"></i>Deskripsi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksis as $t)
                            <tr>
                                <td><span class="code-badge">{{ $t->transaksi_id }}</span><div class="small mt-1">{{ $t->status_label }}</div></td>
                                <td>
                                    <i class="bi bi-calendar3 cell-icon"></i>{{ \Carbon\Carbon::parse($t->tanggal)->format('d M Y') }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ strtoupper(substr($t->user->nama ?? '-', 0, 1)) }}</span>
                                        <span class="fw-semibold">{{ $t->user->nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td><i class="bi bi-geo-alt cell-icon"></i>{{ $t->tujuanDinas->nama ?? '-' }}</td>
                                <td><div class="desc-cell" title="{{ $t->description }}">{{ $t->description }}</div></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('transaksi_perjalanan_dinas.show', $t->transaksi_id) }}" class="btn-action detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        @if($t->status === 'DRAFT' && auth()->user()->canAccessTransaction($t) && app(\App\Services\PeriodService::class)->isOpen($t->periode))
                                        <form action="{{ route('transaksi_perjalanan_dinas.destroy', $t->transaksi_id) }}" method="POST" class="d-inline">
                                            @csrf 
                                            @method('DELETE')
                                            <button class="btn-action hapus" onclick="return confirm('Yakin hapus draft dinas ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
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
                                        <p class="text-muted mb-3">Mulai buat transaksi perjalanan dinas pertama Anda.</p>
                                        <a href="{{ route('transaksi_perjalanan_dinas.create') }}" class="btn btn-success-modern btn-modern">
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
            <div class="card-footer bg-white border-0 d-flex justify-content-end px-4 pb-4">
                {{ $transaksis->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
