<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Edit Event Dalam Kota - {{ $event->transaksi_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
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
        }
        
        .card-header-modern.warning {
            background: #fffbeb;
            border-bottom-color: #fcd34d;
        }
        
        .card-header-modern h5 {
            margin: 0;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .input-group-text {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-right: none;
            color: var(--secondary-color);
        }
        
        .input-with-icon .form-control { 
            border-radius: 0 6px 6px 0; 
        }

        .helper-text { 
            font-size: 0.75rem; 
            color: var(--text-secondary); 
            margin-top: 0.25rem; 
        }

        .stat-box {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1rem;
            text-align: center;
        }
        
        .stat-box small {
            color: var(--text-secondary); 
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        
        .stat-box .value { 
            font-size: 1.125rem; 
            font-weight: 600; 
            margin-top: 0.25rem; 
            display: block;
        }

        .upload-zone {
            background: #f8fafc;
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
        }

        .file-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1rem;
        }

        .foto-preview {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            margin-bottom: 0.75rem;
        }
        
        .foto-empty {
            width: 100%;
            height: 120px;
            border-radius: 6px;
            border: 2px dashed var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            flex-direction: column;
            gap: 0.5rem;
        }

        .req-badge, .opt-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-left: 0.5rem;
        }
        
        .req-badge { 
            background: #fee2e2; 
            color: var(--danger-color); 
        }
        
        .opt-badge { 
            background: #e2e8f0; 
            color: var(--text-secondary); 
        }

        .btn-submit-event {
            background: var(--warning-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }
        
        .btn-submit-event:hover {
            background: #b45309;
            color: white;
        }

        .badge-soft-success {
            background: #d1fae5;
            color: var(--success-color);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .badge-soft-danger {
            background: #fee2e2;
            color: var(--danger-color);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert-custom {
            border-radius: 6px; 
            border: none;
            padding: 1rem 1.25rem;
        }
    </style>
    @include('partials.mobile-assets')
</head>
<body>
    @include('partials.application-time')
    <div class="page-header">
        <div class="container" style="max-width: 900px;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="bi bi-pencil-square me-2"></i>Edit Event Dalam Kota</h1>
                    <p class="mb-0">Memperbarui data transaksi <strong>{{ $event->transaksi_id }}</strong></p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('perongkosan_event_dalam_kota.index') }}" class="btn btn-ghost btn-modern">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 900px; margin-bottom: 3rem;">
        
        @if($errors->any())
            <div class="alert alert-danger alert-custom mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <div>
                        <strong>Gagal Memperbarui:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="card-modern" id="card-info-plafon-event" style="display:none;">
            <div class="card-header-modern">
                <h5><i class="bi bi-bar-chart"></i> Informasi Plafon Event</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 text-center">
                    <div class="col-md-4">
                        <div class="stat-box">
                            <small class="d-block">Plafon</small>
                            <span id="info-plafon-event-total" class="value">Rp 0</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <small class="d-block">Nominal Input</small>
                            <span id="info-nominal-event" class="value">Rp 0</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <small class="d-block">Sisa</small>
                            <span id="info-sisa-event" class="value">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div id="plafon-event-status" class="mt-3 text-center"></div>
            </div>
        </div>

        <div class="card-modern">
            <div class="card-header-modern warning">
                <h5><i class="bi bi-pencil-square"></i> Form Edit Event Dalam Kota</h5>
                <span class="badge bg-warning text-dark">ID: {{ $event->transaksi_id }}</span>
            </div>
            <div class="card-body p-4">
                <form id="form-event" action="{{ route('perongkosan_event_dalam_kota.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">ID Transaksi</label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-magic"></i></span>
                                <input type="text" class="form-control" value="{{ $event->transaksi_id }}" disabled>
                            </div>
                            <small class="helper-text">ID transaksi tidak dapat diubah</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Event <span class="text-danger">*</span></label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="date" name="tanggal" id="tanggal" class="form-control" 
                                       value="{{ old('tanggal', $event->tanggal) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">User (Karyawan)</label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="text" class="form-control" 
                                       value="{{ $event->user->nama ?? $event->user->name ?? 'Unknown' }} (NIK: {{ $event->user->NIK ?? '-' }})" 
                                       readonly disabled>
                            </div>
                            <small class="helper-text">Transaksi tercatat atas nama pemilik asli</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="nominal" id="nominal" class="form-control" 
                                       placeholder="0" min="1" value="{{ old('nominal', $event->nominal) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Hari Libur? <span class="text-danger">*</span></label>
                            <select name="is_libur" id="is_libur" class="form-select" required>
                                <option value="0" {{ old('is_libur', $event->is_libur) == '0' ? 'selected' : '' }}>Bukan Hari Libur (N)</option>
                                <option value="1" {{ old('is_libur', $event->is_libur) == '1' ? 'selected' : '' }}>Hari Libur (Y)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Jam Kerja Event <span class="text-danger">*</span></label>
                            <select name="jam_kerja_event_id" id="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($jam_kerja as $jk)
                                    <option value="{{ $jk->id }}" 
                                            {{ old('jam_kerja_event_id', $event->jam_kerja_event_id) == $jk->id ? 'selected' : '' }}>
                                        {{ $jk->name ?? $jk->nama ?? $jk->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Tulis rincian deskripsi singkat event..." required>{{ old('description', $event->description) }}</textarea>
                    </div>

                    <div class="mt-4">
                        <div class="upload-zone">
                            <div class="upload-head">
                                <i class="bi bi-camera"></i>
                                Foto Bukti Nota/Kegiatan
                                <span class="badge bg-light text-dark ms-auto">Maks 2MB/foto</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="file-item">
                                        <label class="form-label small mb-2">
                                            Foto Event 1 <span class="req-badge">WAJIB</span>
                                        </label>
                                        @if($event->foto_event_1)
                                            <img src="{{ asset('storage/' . $event->foto_event_1) }}" 
                                                 class="foto-preview" alt="Foto 1" id="preview-foto-1">
                                        @else
                                            <div class="foto-empty" id="preview-foto-1-empty">
                                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                                                <span>Belum ada foto</span>
                                            </div>
                                        @endif
                                        <input type="file" name="foto_event_1" id="foto_event_1" 
                                               class="form-control" accept="image/*">
                                        <small class="helper-text">
                                            @if($event->foto_event_1)
                                                Kosongkan jika tidak ingin mengganti
                                            @else
                                                Wajib di-upload
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="file-item">
                                        <label class="form-label small mb-2">
                                            Foto Event 2 <span class="opt-badge">OPSIONAL</span>
                                        </label>
                                        @if($event->foto_event_2)
                                            <img src="{{ asset('storage/' . $event->foto_event_2) }}" 
                                                 class="foto-preview" alt="Foto 2" id="preview-foto-2">
                                        @else
                                            <div class="foto-empty" id="preview-foto-2-empty">
                                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                                                <span>Belum ada foto</span>
                                            </div>
                                        @endif
                                        <input type="file" name="foto_event_2" id="foto_event_2" 
                                               class="form-control" accept="image/*">
                                        <small class="helper-text">Kosongkan untuk tetap pakai foto lama</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="file-item">
                                        <label class="form-label small mb-2">
                                            Foto Event 3 <span class="opt-badge">OPSIONAL</span>
                                        </label>
                                        @if($event->foto_event_3)
                                            <img src="{{ asset('storage/' . $event->foto_event_3) }}" 
                                                 class="foto-preview" alt="Foto 3" id="preview-foto-3">
                                        @else
                                            <div class="foto-empty" id="preview-foto-3-empty">
                                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                                                <span>Belum ada foto</span>
                                            </div>
                                        @endif
                                        <input type="file" name="foto_event_3" id="foto_event_3" 
                                               class="form-control" accept="image/*">
                                        <small class="helper-text">Kosongkan untuk tetap pakai foto lama</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" id="btn-submit" class="btn btn-submit-event">
                            <i class="bi bi-save me-2"></i> Perbarui Data Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('form-event').addEventListener('submit', function() {
            var btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sedang Memperbarui...';
        });

        function setupPreview(inputId, previewImgId, previewEmptyId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        let img = document.getElementById(previewImgId);
                        let empty = document.getElementById(previewEmptyId);
                        
                        if (!img && empty) {
                            img = document.createElement('img');
                            img.id = previewImgId;
                            img.className = 'foto-preview';
                            empty.parentNode.insertBefore(img, empty);
                            empty.remove();
                        }
                        if (img) {
                            img.src = ev.target.result;
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        setupPreview('foto_event_1', 'preview-foto-1', 'preview-foto-1-empty');
        setupPreview('foto_event_2', 'preview-foto-2', 'preview-foto-2-empty');
        setupPreview('foto_event_3', 'preview-foto-3', 'preview-foto-3-empty');

        $(document).ready(function() {
            let plafonEvent = 0;

            function formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); }

            function renderPlafonEvent() {
                if (plafonEvent <= 0) {
                    $('#card-info-plafon-event').hide();
                    return;
                }
                const nominal = parseFloat($('#nominal').val()) || 0;
                const sisa = plafonEvent - nominal;

                $('#info-plafon-event-total').text(formatRupiah(plafonEvent));
                $('#info-nominal-event').text(formatRupiah(nominal));
                $('#info-sisa-event').text(formatRupiah(sisa));

                if (sisa < 0) {
                    $('#info-sisa-event').css('color', 'var(--danger-color)');
                    $('#plafon-event-status').html('<span class="badge-soft-danger">⚠️ OVER PLAFON - Selisih: ' + formatRupiah(Math.abs(sisa)) + '</span>');
                } else {
                    $('#info-sisa-event').css('color', 'var(--success-color)');
                    $('#plafon-event-status').html('<span class="badge-soft-success">✓ Dalam Plafon - Sisa: ' + formatRupiah(sisa) + '</span>');
                }
            }

            function ambilInfoPlafonEvent() {
                const tanggal = $('#tanggal').val();
                const isLibur = $('#is_libur').val();
                const jamId   = $('#jam_kerja_event_id').val();

                if (!tanggal || isLibur === '' || isLibur === null || !jamId) {
                    plafonEvent = 0;
                    $('#card-info-plafon-event').hide();
                    return;
                }

                $.ajax({
                    url: '{{ route("api.event_dalam_kota.plafon_info") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tanggal: tanggal,
                        is_libur: isLibur,
                        jam_kerja_event_id: jamId
                    },
                    success: function(response) {
                        if (response.found) {
                            plafonEvent = response.plafon_value;
                            $('#card-info-plafon-event').show();
                            renderPlafonEvent();
                        } else {
                            plafonEvent = 0;
                            $('#card-info-plafon-event').hide();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error mengambil plafon:', xhr.responseText);
                    }
                });
            }

            $('#tanggal, #is_libur, #jam_kerja_event_id').on('change', ambilInfoPlafonEvent);
            $('#nominal').on('input change', renderPlafonEvent);
            ambilInfoPlafonEvent();
        });
    </script>
</body>
</html>
