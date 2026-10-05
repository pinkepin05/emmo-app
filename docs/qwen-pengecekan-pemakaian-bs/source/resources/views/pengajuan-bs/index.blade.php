<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengajuan BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body: #f8fafc; --bg-card: #ffffff; --text-primary: #0f172a; --text-secondary: #64748b; --border-color: #e2e8f0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-primary); min-height: 100vh; }
        .page-header { padding: 2rem 0 1rem; }
        .card-custom { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .btn-nav { background-color: white; border: 1px solid var(--border-color); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; }
        .btn-primary-custom { background-color: var(--text-primary); color: white; border-radius: 8px; padding: 0.5rem 1rem; font-weight: 600; border: none; text-decoration: none; font-size: 0.875rem;}
        .table-custom { border-collapse: separate; border-spacing: 0; width: 100%; }
        .table-custom th { background-color: #f8fafc; color: var(--text-secondary); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-color); padding: 1rem; }
        .table-custom td { border-bottom: 1px solid var(--border-color); padding: 1rem; font-size: 0.95rem; color: var(--text-primary); }
        .badge-status { padding: 0.4em 0.8em; border-radius: 6px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;}
        .badge-tipe { padding: 0.4em 0.8em; border-radius: 6px; font-weight: 600; font-size: 0.72rem; }
    </style>
</head>
<body class="py-4 py-md-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1100px;">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Daftar Pengajuan BS Saya</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('dashboard.transaksi') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
                <a href="{{ route('pengajuan-bs.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg me-1"></i> Buat Pengajuan</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">✅ {{ session('success') }}</div>
        @endif

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Doc No</th>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Nominal</th>
                            <th>Nominal Cair (Finance)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $bs)
                        <tr>
                            <td class="fw-semibold">{{ $bs->document_no }}</td>
                            <td>{{ \App\Support\AppTime::format($bs->created_at) }}</td>

                            {{-- 🎯 BARU: Kolom Tipe Pengajuan --}}
                            <td>
                                @if(($bs->tipe_pengajuan ?? 'operasional_sales') === 'perjalanan_dinas')
                                    <span class="badge-tipe" style="background: #ecfdf5; color: #047857;">
                                        <i class="bi bi-airplane-fill me-1"></i>Perjalanan Dinas
                                    </span>
                                @else
                                    <span class="badge-tipe" style="background: #eff6ff; color: #1d4ed8;">
                                        <i class="bi bi-briefcase-fill me-1"></i>Operasional Sales
                                    </span>
                                @endif
                            </td>

                            <td>Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</td>

                            <td>
                                @if(($bs->nominal_approved ?? 0) > 0)
                                    <span class="fw-semibold text-success">Rp {{ number_format($bs->nominal_approved, 0, ',', '.') }}</span>
                                    @if($bs->nominal_approved < $bs->nominal_pengajuan)
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                            <i class="bi bi-scissors me-1"></i>Dipotong Rp {{ number_format($bs->nominal_pengajuan - $bs->nominal_approved, 0, ',', '.') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $colors = [
                                        'PENDING_ATASAN'  => 'bg-warning text-dark',
                                        'APPROVED_ATASAN' => 'bg-info text-white',
                                        'REJECTED_ATASAN' => 'bg-danger text-white',
                                        'DISBURSED'       => 'bg-success text-white',
                                        'SETTLED'         => 'bg-secondary text-white'
                                    ];
                                @endphp
                                <span class="badge-status {{ $colors[$bs->status] ?? 'bg-secondary text-white' }}">
                                    {{ str_replace('_', ' ', $bs->status) }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('pengajuan-bs.show', $bs->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                                    <i class="bi bi-clock-history"></i> Riwayat &amp; Transaksi
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Belum ada pengajuan BS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
