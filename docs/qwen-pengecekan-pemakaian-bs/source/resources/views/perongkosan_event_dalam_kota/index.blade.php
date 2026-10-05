<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Event Dalam Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
        }

        .page-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .page-header h1 { 
            font-weight: 600; 
            margin: 0; 
            font-size: 1.75rem; 
        }

        .btn-modern {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-light-header {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .btn-light-header:hover { 
            background: white; 
            color: var(--primary-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }
        
        .btn-ghost {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn-ghost:hover { 
            background: rgba(255, 255, 255, 0.3); 
            color: white; 
        }

        .card-modern {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-header-modern {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .card-header-modern h5 {
            margin: 0;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .count-badge {
            background: #dbeafe;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
        }

        .filter-select {
            width: auto;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--text-primary);
            padding: 0.5rem 2rem 0.5rem 0.75rem;
        }
        
        .filter-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-success-modern {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .btn-success-modern:hover { 
            background: #047857; 
            color: white; 
        }

        .table-modern { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0; 
        }
        
        .table-modern thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--border-color);
            padding: 0.875rem 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        
        .table-modern tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            color: var(--text-primary);
            font-size: 0.875rem;
        }
        
        .table-modern tbody tr:hover { 
            background: #f8fafc; 
        }
        
        .table-modern tbody tr:last-child td { 
            border-bottom: none; 
        }

        .code-badge {
            background: #dbeafe;
            color: var(--primary-color);
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .avatar {
            width: 32px; 
            height: 32px; 
            border-radius: 50%;
            background: var(--primary-color);
            color: white; 
            font-weight: 600; 
            font-size: 0.8rem;
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
        }

        .desc-cell {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nominal-cell { 
            font-weight: 600; 
            color: var(--text-primary); 
            white-space: nowrap; 
        }

        .foto-chip {
            width: 36px; 
            height: 36px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            border: 1px solid;
            text-decoration: none;
            margin: 0 2px;
            color: var(--text-secondary);
            border-color: var(--border-color);
            background: white;
        }
        
        .foto-chip:hover { 
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-action {
            border: none;
            border-radius: 6px;
            padding: 0.4rem 0.75rem;
            font-weight: 500;
            font-size: 0.8rem;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
        }
        
        .btn-action.edit {
            background: var(--warning-color);
        }
        
        .btn-action.edit:hover { 
            background: #b45309; 
            color: white; 
        }
        
        .btn-action.hapus {
            background: var(--danger-color);
        }
        
        .btn-action.hapus:hover { 
            background: #b91c1c; 
            color: white; 
        }

        .empty-state { 
            text-align: center; 
            padding: 3rem 1rem; 
        }
        
        .empty-state .icon-circle {
            width: 80px; 
            height: 80px; 
            border-radius: 50%;
            background: #f1f5f9;
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .empty-state .icon-circle i {
            font-size: 2rem;
            color: var(--text-secondary);
        }
        
        .empty-state h6 { 
            font-weight: 600; 
            color: var(--text-primary); 
        }

        .pagination { gap: 4px; }
        
        .page-link {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-weight: 500;
            margin: 0;
            padding: 0.5rem 0.75rem;
        }
        
        .page-link:hover { 
            background: var(--bg-color); 
            color: var(--text-primary); 
        }
        
        .page-item.active .page-link {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .page-item.disabled .page-link { 
            color: var(--text-secondary); 
            background: transparent; 
        }

        .alert-custom {
            border-radius: 6px; 
            border: none;
            padding: 1rem 1.25rem;
        }

        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.25rem; }
            .desc-cell { max-width: 150px; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')
    <div class="page-header">
        <div class="container" style="max-width: 1200px;">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1><i class="bi bi-briefcase me-2"></i>Event Dalam Kota</h1>
                    <p class="mb-0">Daftar seluruh transaksi perongkosan event dalam kota</p>
                </div>
                <div class="col-lg-6 d-flex justify-content-lg-end gap-2 flex-wrap mt-3 mt-lg-0">
                    <a href="{{ route('perongkosan_event_dalam_kota.create') }}" class="btn btn-light-header btn-modern">
                        <i class="bi bi-plus-lg"></i> Tambah
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
            <div class="alert alert-success alert-custom mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle me-2"></i>
                    <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                </div>
            </div>
        @endif

        <div class="card-modern">
            <div class="card-header-modern">
                <h5>
                    <i class="bi bi-list-check"></i> Daftar Transaksi Event Dalam Kota
                    <span class="count-badge">{{ $events->total() }} Transaksi</span>
                </h5>

                <form method="GET" action="{{ route('perongkosan_event_dalam_kota.export') }}" class="d-flex gap-2 align-items-center flex-wrap">
                    <select name="periode" class="form-select filter-select">
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
                                <th>ID Transaksi</th>
                                <th>Tanggal</th>
                                <th>User (Karyawan)</th>
                                <th>Deskripsi</th>
                                <th>Nominal (Rp)</th>
                                <th class="text-center">Foto Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                            <tr>
                                <td><span class="code-badge">{{ $event->transaksi_id }}</span></td>
                                <td><i class="bi bi-calendar3 me-2 text-muted"></i>{{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ strtoupper(substr($event->user->name ?? $event->user->nama ?? 'U', 0, 1)) }}</span>
                                        <span class="fw-medium">{{ $event->user->name ?? $event->user->nama ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td><div class="desc-cell" title="{{ $event->description }}">{{ $event->description }}</div></td>
                                <td class="nominal-cell">Rp {{ number_format($event->nominal, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        @if($event->foto_event_1)
                                            <a href="{{ asset('storage/' . $event->foto_event_1) }}" target="_blank" class="foto-chip" title="Lihat Foto 1">
                                                <i class="bi bi-camera"></i> 1
                                            </a>
                                        @endif
                                        @if($event->foto_event_2)
                                            <a href="{{ asset('storage/' . $event->foto_event_2) }}" target="_blank" class="foto-chip" title="Lihat Foto 2">
                                                <i class="bi bi-camera"></i> 2
                                            </a>
                                        @endif
                                        @if($event->foto_event_3)
                                            <a href="{{ asset('storage/' . $event->foto_event_3) }}" target="_blank" class="foto-chip" title="Lihat Foto 3">
                                                <i class="bi bi-camera"></i> 3
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('perongkosan_event_dalam_kota.show', $event->id) }}" class="btn-action edit" style="background: var(--primary-color);">
    <i class="bi bi-eye"></i> Lihat Detail
</a>
                                        <form action="{{ route('perongkosan_event_dalam_kota.destroy', $event->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn-action hapus" onclick="return confirm('Yakin hapus data ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="icon-circle"><i class="bi bi-inbox"></i></div>
                                        <h6>Belum ada data transaksi</h6>
                                        <p class="text-muted mb-3">Mulai buat transaksi event dalam kota pertama Anda.</p>
                                        <a href="{{ route('perongkosan_event_dalam_kota.create') }}" class="btn btn-success-modern btn-modern">
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

            @if($events->hasPages())
            <div class="card-footer bg-white border-0 d-flex justify-content-end px-4 pb-4 pt-3">
                {{ $events->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
