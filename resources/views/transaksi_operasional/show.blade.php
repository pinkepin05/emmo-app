<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Detail Operasional Area Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        /* 🎯 Card statis */
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

        /* 🎯 FOTO KOLOM TERSENDIRI */
        .photo-cell {
            text-align: center;
            vertical-align: middle;
        }
        .photo-thumbnail-col {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            transition: all 0.25s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .photo-thumbnail-col:hover {
            border-color: var(--purple-color);
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.25);
        }
        .no-photo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #94a3b8;
            font-size: 1.1rem;
        }

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
            .table-modern thead { display: none; }
            .table-modern, .table-modern tbody, .table-modern tr, .table-modern td {
                display: block;
                width: 100%;
            }
            .table-modern tr {
                margin-bottom: 1rem;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 1rem;
                background: white;
            }
            .table-modern td {
                border: none;
                padding: 0.5rem 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .table-modern td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #718096;
                font-size: 0.8rem;
                text-transform: uppercase;
            }
            .photo-cell { justify-content: flex-end; }
        }
    </style>
    @include('partials.mobile-assets')
</head>
<body>
    @include('partials.application-time')
    @php
    $monitoringReadOnly = $monitoringReadOnly ?? false;
    $isPeriodClosed = !app(\App\Services\PeriodService::class)->isOpen($transaksi->periode);
    $isDraft = !$monitoringReadOnly && $transaksi->status === 'DRAFT' && auth()->user()->canAccessTransaction($transaksi);
    $isEditMode = $isDraft && !$isPeriodClosed && (request('edit') == 1 || old('_transaction_edit') === $transaksi->transaksi_id);
@endphp

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="container" style="max-width: 1100px;">
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
                @if(!$monitoringReadOnly && $isDraft && !$isPeriodClosed && !$isEditMode)
<a href="{{ route('transaksi_operasional.show', $transaksi->id) }}?edit=1" class="btn btn-modern" ...>
    <i class="bi bi-pencil-square"></i> Edit Transaksi
</a>
@endif
                <a href="{{ $monitoringReadOnly ? ($monitoringBackUrl ?? route('transaksi_operasional.index')) : route('transaksi_operasional.index') }}" class="btn btn-ghost btn-modern">
    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
</a>
            </div>
        </div>
    </div>
</div>

    <div class="container" style="max-width: 1100px; margin-bottom: 3rem;">
        @include('partials.finance-correction-notice', ['financeSourceType' => 'OPR'])
        
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

        @if(!$isEditMode)
        <div id="view-mode">
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

        <!-- 🎯 CARD 2: MUTASI PENGELUARAN (DENGAN KOLOM FOTO TERSENDIRI) -->
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
                                <th width="25%"><i class="bi bi-tag me-2"></i>Kategori Biaya</th>
                                <th width="35%"><i class="bi bi-chat-left-text me-2"></i>Keterangan Item (Remark)</th>
                                <th width="12%" class="text-center">Foto Bukti</th>
                                <th width="20%" class="text-end"><i class="bi bi-currency-dollar me-2"></i>Nominal Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalKeseluruhan = 0; @endphp
                            
                            @forelse($transaksi->details as $index => $detail)
                                @php $totalKeseluruhan += $detail->nominal; @endphp
                                <tr>
                                    <td class="text-center" data-label="No."><span class="row-num">{{ $index + 1 }}</span></td>
                                    <td data-label="Kategori">
                                        <span class="category-chip">
                                            <i class="bi bi-tag-fill"></i>
                                            {{ $detail->costCategory->nama ?? 'Kategori #'.$detail->cost_category_id }}
                                        </span>
                                    </td>
                                    <td data-label="Keterangan">{{ $detail->remark }}</td>
                                    
                                    {{-- 🎯 KOLOM FOTO TERSENDIRI --}}
                                    <td class="photo-cell" data-label="Foto">
                                        @if($detail->photo_url)
                                            <a href="{{ $detail->photo_url }}" target="_blank" rel="noopener" title="Klik untuk memperbesar">
                                                <img src="{{ $detail->photo_url }}" alt="Foto bukti" class="photo-thumbnail-col" loading="lazy">
                                            </a>
                                        @else
                                            <div class="no-photo-badge" title="Tidak ada foto">
                                                <i class="bi bi-image-slash"></i>
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <td class="text-end nominal-cell" data-label="Nominal">Rp {{ number_format($detail->nominal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="icon-circle"><i class="bi bi-inbox"></i></div>
                                            <h6>Rincian item biaya kosong</h6>
                                            <p class="text-muted mb-0">
                                                @if($transaksi->status === 'DRAFT')
                                                    Klik Edit Transaksi untuk menambahkan rincian biaya.
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
                                <td colspan="4" class="text-end">TOTAL KESELURUHAN NOTA :</td>
                                <td class="text-end"><span class="total-value">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</span></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        </div>
        @endif

        <!-- 🎯 CARD 3 & 4: FORM TAMBAH RINCIAN & KONFIRMASI (Hanya untuk DRAFT) -->
      @php
$monitoringReadOnly = $monitoringReadOnly ?? false;
$isPeriodClosed = !app(\App\Services\PeriodService::class)->isOpen($transaksi->periode);
$isDraft = !$monitoringReadOnly && $transaksi->status === 'DRAFT' && auth()->user()->canAccessTransaction($transaksi);
$isEditMode = $isDraft && !$isPeriodClosed && (request('edit') == 1 || old('_transaction_edit') === $transaksi->transaksi_id);
@endphp
       @include('partials.bs-finance-check-status', ['transaction' => $transaksi, 'jenis' => 'OPR'])
       @if($isDraft && !$isPeriodClosed)
    
    @if($isEditMode)
        {{-- ================= MODE EDIT ================= --}}
        <form action="{{ route('transaksi_operasional.update', $transaksi->id) }}" method="POST" enctype="multipart/form-data" id="form-edit-operasional" data-transaction-editor="operational">
            @csrf
            @method('PUT')
            <input type="hidden" name="_transaction_edit" value="{{ $transaksi->transaksi_id }}">

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
                            <input type="date" name="tanggal" value="{{ old('tanggal', \Carbon\Carbon::parse($transaksi->tanggal)->format('Y-m-d')) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            @include('partials.transaction-funding', ['fundingType' => 'operasional_sales', 'fundingDocument' => $transaksi])
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted">Deskripsi Kegiatan</label>
                            <input type="text" name="description" value="{{ old('description', $transaksi->description) }}" class="form-control" maxlength="255" placeholder="Masukkan deskripsi...">
                        </div>
                    </div>
                </div>
            </div>

            @include('partials.transaction-edit-details', ['requireRemark' => true, 'cancelUrl' => route('transaksi_operasional.show', $transaksi->id)])
        </form>

    @else
        {{-- ================= MODE VIEW NORMAL ================= --}}
        <div class="card-modern animate-slide-in" style="animation-delay: 0.3s">
            <div class="card-header-modern" style="background: linear-gradient(135deg, rgba(17, 153, 142, 0.15) 0%, rgba(56, 239, 125, 0.15) 100%); border-bottom-color: rgba(17, 153, 142, 0.3);">
                <h5 style="color: #11998e;"><i class="bi bi-check-circle"></i> Konfirmasi Transaksi</h5>
            </div>
            <div class="card-body p-4">
                <div class="info-box mb-3">
                    <i class="bi bi-info-circle me-2 text-primary"></i>
                    <small class="text-muted">
                        <strong>Perhatian:</strong> Konfirmasi mengunci transaksi untuk diperiksa Finance. Saldo BS berkurang sesuai nominal yang dikonfirmasi Finance. Transaksi final tidak dapat diedit atau dihapus.
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
                Transaksi ini <strong>sudah dikonfirmasi</strong>. {{ $transaksi->bsCashOwnerId() ? 'Pengurangan saldo BS mengikuti hasil konfirmasi Finance terakhir.' : 'Arsip tanpa BS.' }}
                Tidak ada perubahan yang dapat dilakukan lagi.
            </p>
        </div>
    </div>
@endif
    </div>

    <script src="{{ asset('js/transaction-editor.js') }}?v={{ filemtime(public_path('js/transaction-editor.js')) }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
