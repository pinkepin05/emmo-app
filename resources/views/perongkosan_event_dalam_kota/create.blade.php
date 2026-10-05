<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Input Event Dalam Kota</title>
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
            line-height: 1.6;
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
        
        .page-header p { 
            margin: 0.5rem 0 0 0; 
            opacity: 0.9; 
            font-size: 0.95rem;
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
        
        .card-header-modern h5 {
            margin: 0;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-control:disabled, .form-control[readonly] {
            background: #f8fafc;
            border-color: var(--border-color);
        }

        .input-group-text {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-right: none;
            color: var(--secondary-color);
            font-size: 0.875rem;
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
            letter-spacing: 0.05em;
        }
        
        .stat-box .value { 
            font-size: 1.125rem; 
            font-weight: 600; 
            margin-top: 0.25rem; 
            display: block;
            color: var(--text-primary);
        }

        .upload-zone {
            background: #f8fafc;
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
        }
        
        .upload-head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .file-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1rem;
        }
        
        .file-item .form-control { 
            padding: 0.5rem; 
            font-size: 0.8rem; 
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
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: background 0.2s;
        }
        
        .btn-submit-event:hover {
            background: var(--primary-dark);
            color: white;
        }
        
        .btn-submit-event:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .alert-custom {
            border-radius: 6px; 
            border: none;
            padding: 1rem 1.25rem;
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

        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.25rem; }
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
                    <h1><i class="bi bi-briefcase me-2"></i>Event Dalam Kota</h1>
                    <p class="mb-0">Form perongkosan event dalam kota dengan validasi plafon real-time</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('dashboard.transaksi') }}" class="btn btn-ghost btn-modern">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 900px; margin-bottom: 3rem;">
        
        @if(session('success'))
            <div class="alert alert-success alert-custom mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle me-2"></i>
                    <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-custom mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <div>
                        <strong>Gagal Menyimpan:</strong>
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
            <div class="card-header-modern">
                <h5><i class="bi bi-file-earmark-plus"></i> Form Perongkosan Event Dalam Kota</h5>
            </div>
            <div class="card-body p-4">
                <form id="form-event" action="{{ route('perongkosan_event_dalam_kota.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">ID Transaksi</label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-magic"></i></span>
                                <input type="text" class="form-control" value="Otomatis (EV-DLM-YYMMDD-XXX)" disabled>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Event <span class="text-danger">*</span></label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', \App\Support\AppTime::now()->toDateString()) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">User (Karyawan)</label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="text" class="form-control" value="{{ Auth::user()->nama ?? Auth::user()->name }} (NIK: {{ Auth::user()->NIK ?? '-' }})" readonly disabled>
                            </div>
                            <small class="helper-text">Transaksi otomatis dicatat atas nama akun Anda</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="nominal" id="nominal" class="form-control" placeholder="0" min="1" value="{{ old('nominal') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Hari Libur? <span class="text-danger">*</span></label>
                            <select name="is_libur" id="is_libur" class="form-select" required>
                                <option value="0" {{ old('is_libur') == '0' ? 'selected' : '' }}>Bukan Hari Libur (N)</option>
                                <option value="1" {{ old('is_libur') == '1' ? 'selected' : '' }}>Hari Libur (Y)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Jam Kerja Event <span class="text-danger">*</span></label>
                            <select name="jam_kerja_event_id" id="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($jam_kerja as $jk)
                                    <option value="{{ $jk->id }}" {{ old('jam_kerja_event_id') == $jk->id ? 'selected' : '' }}>{{ $jk->name ?? $jk->nama ?? $jk->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Tulis rincian deskripsi singkat event..." required>{{ old('description') }}</textarea>
                    </div>

                    <div class="mt-4">
                        <div class="upload-zone">
                            <div class="upload-head">
                                <i class="bi bi-camera"></i>
                                Upload Foto Bukti Nota/Kegiatan
                                <span class="badge bg-light text-dark ms-auto">Maks 2MB/foto</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="file-item">
                                        <label class="form-label small mb-2">Foto Event 1 <span class="req-badge">WAJIB</span></label>
                                        <input type="file" name="foto_event_1" class="form-control" accept="image/*" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="file-item">
                                        <label class="form-label small mb-2">Foto Event 2 <span class="opt-badge">OPSIONAL</span></label>
                                        <input type="file" name="foto_event_2" class="form-control" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="file-item">
                                        <label class="form-label small mb-2">Foto Event 3 <span class="opt-badge">OPSIONAL</span></label>
                                        <input type="file" name="foto_event_3" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" id="btn-submit" class="btn btn-submit-event">
                            <i class="bi bi-save me-2"></i> Simpan Data Event Dalam Kota
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
            btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sedang Menyimpan...';
        });

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
        });
    </script>
</body>
</html>
