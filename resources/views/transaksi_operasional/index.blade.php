<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
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
    @include('partials.mobile-assets')
    @include('partials.transaction-actions-assets')
    @include('partials.transaction-list-assets')
</head>

<body class="transaction-list-page">
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
    @if(!($monitoringReadOnly ?? false))
    <a href="{{ route('transaksi_operasional.create') }}" class="btn btn-light-header btn-modern">
        <i class="bi bi-plus-lg"></i> Tambah Baru
    </a>
    @endif
    @if($monitoringReadOnly ?? false)
    <a href="{{ $monitoringBackUrl }}" class="btn btn-ghost btn-modern">
        <i class="bi bi-arrow-left"></i> Kembali ke Monitoring
    </a>
    @else
    <a href="{{ route('dashboard.transaksi') }}" class="btn btn-ghost btn-modern">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    @endif
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
                <h5 class="transaction-list-heading">
                    <i class="bi bi-list-check" aria-hidden="true"></i>
                    <span class="transaction-list-heading-full">Daftar Transaksi Operasional Area Sales</span>
                    <span class="transaction-list-heading-short">Daftar Transaksi</span>
                    <span class="count-badge">{{ $transaksis->total() }} Transaksi</span>
                </h5>

                {{-- 🎯 EXPORT FORM --}}
                <form method="GET" action="{{ route('transaksi_operasional.index') }}" class="transaction-list-filter transaction-list-filter--auto d-inline-flex gap-2 align-items-center flex-wrap">
    @include('partials.transaction-reported-filter')
    <label class="transaction-list-period-label" for="transaction-list-period">Periode transaksi</label>
    <select id="transaction-list-period" name="periode" class="form-select form-select-sm filter-select" onchange="this.form.submit()" aria-label="Periode transaksi">
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
                <div class="table-responsive" aria-label="Daftar transaksi operasional">
                    <table class="table-modern" data-transaction-list="opr" role="table">
                        <thead role="rowgroup">
    <tr>
        <th><i class="bi bi-hash me-2"></i>ID Transaksi</th>
        <th><i class="bi bi-calendar-event me-2"></i>Tanggal</th>
        <th><i class="bi bi-person me-2"></i>User Karyawan</th>
        <th><i class="bi bi-file-text me-2"></i>Deskripsi Kegiatan</th>
        <th class="text-end"><i class="bi bi-cash-stack me-2"></i>Total Nominal</th>
        <th class="text-center"><i class="bi bi-check-circle me-2"></i>Status</th>
        <th class="text-center">Aksi</th>
    </tr>
</thead>

                       <tbody role="rowgroup">
    @forelse($transaksis as $t)
        <tr role="row">
            <td data-field="id" data-label="ID Transaksi" role="cell">
                <span class="code-badge">{{ $t->transaksi_id }}</span>
            </td>

            <td data-field="date" data-label="Tanggal" role="cell">
                <i class="bi bi-calendar3 cell-icon"></i>
                {{ \Carbon\Carbon::parse($t->tanggal)->format('d M Y') }}
            </td>

            <td data-field="user" data-label="Karyawan" role="cell">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar">
                        {{ strtoupper(substr($t->user->name ?? $t->user->nama ?? 'U', 0, 1)) }}
                    </span>
                    <span class="fw-semibold">
                        {{ $t->user->name ?? $t->user->nama ?? 'Unknown' }}
                    </span>
                </div>
            </td>

            <td data-field="description" data-label="Deskripsi kegiatan" role="cell">
                <div class="desc-cell" title="{{ $t->description }}">
                    {{ $t->description ?: '-' }}
                </div>
            </td>

            <td data-field="total" data-label="Total nominal" role="cell" class="transaction-total text-end text-nowrap fw-semibold">Rp {{ number_format($t->total_nominal ?? 0, 2, ',', '.') }}</td>

            <td data-field="status" data-label="Status transaksi & pemeriksaan" role="cell" class="text-center">
                @if($t->status === 'DRAFT')
                    <span class="badge bg-warning text-dark" style="padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-file-earmark-text me-1"></i> Draft
                    </span>
                @else
                    <span class="badge bg-success" style="padding: 0.4rem 0.8rem; border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-check-circle-fill me-1"></i> Dikonfirmasi
                    </span>
                @endif
                @include('partials.bs-finance-check-status', ['transaction' => $t, 'jenis' => 'OPR'])
            </td>

            <td data-field="actions" role="cell" class="text-center">
                @include('partials.transaction-actions', [
    'transaction' => $t,
    'transactionType' => 'opr',
    'detailUrl' => route('transaksi_operasional.show', $t->id),
    'confirmUrl' => route('transaksi_operasional.validate', $t->id),
    'deleteUrl' => route('transaksi_operasional.destroy', $t->id),
    'monitoringReadOnly' => $monitoringReadOnly ?? false,
])
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7">
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
