<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Perjalanan Dinas - {{ $transaksi->transaksi_id }}</title>
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

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-primary); }
        .page-header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; padding: 2rem 0; margin-bottom: 2rem; }
        .page-header h1 { font-weight: 600; margin: 0; font-size: 1.75rem; }
        
        .btn-modern { border-radius: 6px; padding: 0.5rem 1rem; font-weight: 500; border: none; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; cursor: pointer; }
        .btn-ghost { background: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255, 255, 255, 0.3); }
        .btn-ghost:hover { background: rgba(255, 255, 255, 0.3); color: white; }
        
        .card-modern { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem; }
        .card-header-modern { background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
        .card-header-modern h5 { margin: 0; font-weight: 600; color: var(--text-primary); font-size: 1rem; }

        .detail-label { font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .detail-value { font-size: 0.95rem; font-weight: 500; color: var(--text-primary); }
        
        .form-control, .form-select { border: 1px solid var(--border-color); border-radius: 6px; padding: 0.625rem 0.75rem; font-size: 0.875rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

        .badge-soft-success { background: #d1fae5; color: var(--success-color); padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .badge-soft-danger { background: #fee2e2; color: var(--danger-color); padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .badge-soft-info { background: #dbeafe; color: var(--primary-color); padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .badge-soft-warning { background: #fef3c7; color: var(--warning-color); padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }

        .stat-box { background: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 1rem; text-align: center; }
        .stat-box small { color: var(--text-secondary); font-weight: 500; font-size: 0.75rem; text-transform: uppercase; }
        .stat-box .value { font-size: 1.125rem; font-weight: 600; margin-top: 0.25rem; display: block; }

        .table-modern { width: 100%; border-collapse: collapse; }
        .table-modern th { background: #f8fafc; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); }
        .table-modern td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.875rem; vertical-align: middle; }
        .table-modern tbody tr:hover { background: #f8fafc; }
        .table-modern tfoot td { background: #f8fafc; font-weight: 700; font-size: 0.95rem; }

        .btn-action-sm { border: none; border-radius: 5px; padding: 0.3rem 0.6rem; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer; }

        .to-delete { text-decoration: line-through; opacity: 0.5; background: #fee2e2 !important; }
        .to-delete td { color: var(--danger-color); }

        /* 🎯 Format Nominal Rupiah */
        .nominal-formatted { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
    </style>
    @include('partials.mobile-assets')
</head>
<body>
    @include('partials.application-time')
    <div class="page-header">
        <div class="container" style="max-width: 1000px;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="bi bi-airplane me-2"></i>Detail Perjalanan Dinas</h1>
                    <p class="mb-0">ID: <strong>{{ $transaksi->transaksi_id }}</strong></p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('transaksi_perjalanan_dinas.index') }}" class="btn btn-ghost btn-modern">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1000px; margin-bottom: 3rem;">
        @include('partials.finance-correction-notice', ['financeSourceType' => 'PD'])
        @if(session('success'))
            <div class="alert alert-success mb-4" style="border-radius: 6px; border: none;">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger mb-4" style="border-radius: 6px; border: none;">
                <strong>Gagal:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- 🎯 MODE 1: TAMPILAN DETAIL (Default)       -->
        <!-- ========================================== -->
        @php
            $canEdit = $transaksi->status === 'DRAFT'
                && app(\App\Services\PeriodService::class)->isOpen($transaksi->periode)
                && auth()->user()->canAccessTransaction($transaksi);
            $isEditMode = $canEdit && (request('edit') == 1 || old('_transaction_edit') === $transaksi->transaksi_id);
        @endphp
        @if(!$isEditMode)
        @include('partials.travel-confirmation')
        @include('partials.bs-finance-check-status', ['transaction' => $transaksi, 'jenis' => 'PD'])
        @include('bs-usage-approvals.actions')
        @include('bs-usage-approvals.history')
        <div id="view-mode">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5><i class="bi bi-info-circle me-2"></i>Informasi Transaksi</h5>
                    @if($canEdit)
                    <a href="{{ route('transaksi_perjalanan_dinas.show', $transaksi->transaksi_id) }}?edit=1" class="btn btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit Transaksi
                    </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="detail-label">Tanggal Dinas</div>
                            <div class="detail-value"><i class="bi bi-calendar3 me-2 text-muted"></i>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d F Y') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">User (Karyawan)</div>
                            <div class="detail-value"><i class="bi bi-person me-2 text-muted"></i>{{ $transaksi->user->nama ?? $transaksi->user->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Pemilik Saldo BS</div>
                            <div class="detail-value">
                                @if($transaksi->bsCashOwnerId())
                                    <span class="badge-soft-info">{{ $transaksi->bsOwner?->nama ?? $transaksi->bonSementara?->user?->nama ?? '-' }}</span>
                                @else
                                    <span class="text-muted">Arsip tanpa BS</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Tujuan Dinas</div>
                            <div class="detail-value">
                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                {{ $transaksi->tujuanDinas->nama ?? '-' }}
                                @if($transaksi->tujuanDinas && $transaksi->tujuanDinas->parent)
                                    , {{ $transaksi->tujuanDinas->parent->nama }}
                                @endif
                                @if($transaksi->tujuanDinas)
                                    <span class="badge-soft-info ms-2">{{ $transaksi->tujuanDinas->teritory }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Jarak</div>
                            <div class="detail-value"><i class="bi bi-signpost-split me-2 text-muted"></i>{{ $transaksi->jarak_km }} KM</div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-label">Durasi</div>
                            <div class="detail-value"><i class="bi bi-clock me-2 text-muted"></i>{{ $transaksi->durasi_jam }} Jam</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Total Pengeluaran</div>
                            <div class="detail-value" style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                                Rp {{ number_format($transaksi->details->sum('nominal'), 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Status Plafon</div>
                            <div class="detail-value">
                                @if($transaksi->is_over_plafon)
                                    <span class="badge-soft-danger">⚠️ OVER PLAFON (Selisih: Rp {{ number_format($transaksi->selisih_plafon, 0, ',', '.') }})</span>
                                @elseif($transaksi->plafon_value)
                                    <span class="badge-soft-success">✓ Dalam Plafon (Sisa: Rp {{ number_format($transaksi->plafon_value - $transaksi->details->sum('nominal'), 0, ',', '.') }})</span>
                                @else
                                    <span class="badge-soft-info">- Tidak ada data plafon</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="detail-label">Deskripsi Kegiatan</div>
                            <div class="detail-value p-3" style="background: #f8fafc; border-radius: 6px; border: 1px solid var(--border-color);">
                                {{ $transaksi->description }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Rincian Biaya -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5><i class="bi bi-list-ul me-2"></i>Rincian Biaya ({{ $transaksi->details->count() }} Item)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 30%;">Kategori Biaya</th>
                                    <th style="width: 35%;">Keterangan</th>
                                    <th style="width: 20%;" class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total = 0; @endphp
                                @forelse($transaksi->details as $i => $d)
                                    @php $total += $d->nominal; @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $d->costCategory->name ?? $d->costCategory->description ?? '-' }}</td>
                                        <td>{{ $d->remark ?? '-' }}</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($d->nominal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada rincian biaya.</td></tr>
                                @endforelse
                            </tbody>
                            @if($total > 0)
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">TOTAL</td>
                                    <td class="text-end" style="color: var(--primary-color);">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
        @else
        <div id="edit-mode">
            <form action="{{ route('transaksi_perjalanan_dinas.update', $transaksi->transaksi_id) }}" method="POST" id="form-edit-pd" data-transaction-editor="travel" data-provinces-url="{{ route('api.wilayah.provinsi') }}" data-cities-url="{{ route('api.wilayah.kota') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_transaction_edit" value="{{ $transaksi->transaksi_id }}">

                <div class="card-modern">
                    <div class="card-header-modern" style="background: #fffbeb; border-bottom-color: #fcd34d;">
                        <h5 style="color: var(--warning-color);"><i class="bi bi-pencil-square me-2"></i>Mode Edit Data</h5>
                        <a href="{{ route('transaksi_perjalanan_dinas.show', $transaksi->transaksi_id) }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">ID Transaksi</label>
                                <input type="text" class="form-control" value="{{ $transaksi->transaksi_id }}" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tanggal Dinas <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', \Carbon\Carbon::parse($transaksi->tanggal)->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                @include('partials.transaction-funding', ['fundingType' => 'perjalanan_dinas', 'fundingDocument' => $transaksi])
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jarak (KM) <span class="text-danger">*</span></label>
                                <input type="number" name="jarak_km" id="edit-jarak" class="form-control" value="{{ old('jarak_km', $transaksi->jarak_km) }}" min="0" max="99999999.99" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Durasi (Jam) <span class="text-danger">*</span></label>
                                <input type="number" name="durasi_jam" id="edit-durasi" class="form-control" value="{{ old('durasi_jam', $transaksi->durasi_jam) }}" min="0" max="24" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Territory <span class="text-danger">*</span></label>
                                <select name="teritory" id="edit-teritory" class="form-select" required>
                                    <option value="Jawa" {{ $currentTerritory == 'Jawa' ? 'selected' : '' }}>Jawa</option>
                                    <option value="Luar Jawa" {{ $currentTerritory == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Provinsi <span class="text-danger">*</span></label>
                                <select name="provinsi_id" id="edit-provinsi" class="form-select" required>
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $p)
                                        <option value="{{ $p->id }}" {{ $currentProvinceId == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kota/Kabupaten <span class="text-danger">*</span></label>
                                <select name="tujuan_dinas_id" id="edit-kota" class="form-select" required>
                                    <option value="">-- Pilih Kota --</option>
                                    @foreach($cities as $c)
                                        <option value="{{ $c->id }}" {{ old('tujuan_dinas_id', $transaksi->tujuan_dinas_id) == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="3" maxlength="255" required>{{ old('description', $transaksi->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                @include('partials.transaction-edit-details', ['requireRemark' => false, 'cancelUrl' => route('transaksi_perjalanan_dinas.show', $transaksi->transaksi_id)])
            </form>
        </div>
        @endif
    </div>

    <script src="{{ asset('js/transaction-editor.js') }}?v={{ filemtime(public_path('js/transaction-editor.js')) }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
