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
