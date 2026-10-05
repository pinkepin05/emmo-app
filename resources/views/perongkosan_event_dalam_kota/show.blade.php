<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Detail Event Dalam Kota - {{ $event->transaksi_id }}</title>
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

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-primary); }
        .page-header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; padding: 2rem 0; margin-bottom: 2rem; }
        .page-header h1 { font-weight: 600; margin: 0; font-size: 1.75rem; }
        
        .btn-modern { border-radius: 6px; padding: 0.5rem 1rem; font-weight: 500; border: none; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; }
        .btn-ghost { background: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255, 255, 255, 0.3); }
        .btn-ghost:hover { background: rgba(255, 255, 255, 0.3); color: white; }
        
        .card-modern { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem; }
        .card-header-modern { background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
        .card-header-modern h5 { margin: 0; font-weight: 600; color: var(--text-primary); font-size: 1rem; }

        .detail-label { font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
        .detail-value { font-size: 0.95rem; font-weight: 500; color: var(--text-primary); }
        
        .form-control, .form-select { border: 1px solid var(--border-color); border-radius: 6px; padding: 0.625rem 0.75rem; font-size: 0.875rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        
        .foto-preview-detail { width: 100%; height: 150px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color); }
        .badge-soft-success { background: #d1fae5; color: var(--success-color); padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .badge-soft-danger { background: #fee2e2; color: var(--danger-color); padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }
        .badge-soft-info { background: #dbeafe; color: var(--primary-color); padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }

        .stat-box { background: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 1rem; text-align: center; }
        .stat-box small { color: var(--text-secondary); font-weight: 500; font-size: 0.75rem; text-transform: uppercase; }
        .stat-box .value { font-size: 1.125rem; font-weight: 600; margin-top: 0.25rem; display: block; }
    </style>
    @include('partials.mobile-assets')
</head>
<body>
    @include('partials.application-time')
    <div class="page-header">
        <div class="container" style="max-width: 900px;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1><i class="bi bi-receipt me-2"></i>Detail Transaksi</h1>
                    <p class="mb-0">ID: <strong>{{ $event->transaksi_id }}</strong></p>
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
        @include('partials.event-confirmation', ['eventType' => 'EDK'])
        @if(session('success'))
            <div class="alert alert-success mb-4" style="border-radius: 6px; border: none;">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- ========================================== -->
        <!-- 🎯 MODE 1: TAMPILAN DETAIL (Default)       -->
        <!-- ========================================== -->
        <div id="view-mode">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5><i class="bi bi-info-circle me-2"></i>Informasi Transaksi</h5>
                    <button onclick="toggleEditMode()" class="btn btn-modern" style="background: var(--warning-color); color: white;">
                        <i class="bi bi-pencil-square"></i> Edit Data
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="detail-label">Tanggal Event</div>
                            <div class="detail-value"><i class="bi bi-calendar3 me-2 text-muted"></i>{{ \Carbon\Carbon::parse($event->tanggal)->format('d F Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">User (Karyawan)</div>
                            <div class="detail-value"><i class="bi bi-person me-2 text-muted"></i>{{ $event->user->nama ?? $event->user->name ?? 'Unknown' }} (NIK: {{ $event->user->NIK ?? '-' }})</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Hari Libur?</div>
                            <div class="detail-value">{{ $event->is_libur ? 'Ya (Hari Libur)' : 'Tidak (Hari Kerja Reguler)' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Jam Kerja Event</div>
                            <div class="detail-value">{{ $event->jamKerja->name ?? $event->jamKerja->nama ?? $event->jamKerja->code ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Nominal Pengeluaran</div>
                            <div class="detail-value" style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                                Rp {{ number_format($event->nominal, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Status Plafon</div>
                            <div class="detail-value">
                                @if($event->is_over_plafon)
                                    <span class="badge-soft-danger">⚠️ OVER PLAFON (Selisih: Rp {{ number_format($event->selisih_plafon, 0, ',', '.') }})</span>
                                @elseif($event->plafon_value)
                                    <span class="badge-soft-success">✓ Dalam Plafon (Sisa: Rp {{ number_format($event->plafon_value - $event->nominal, 0, ',', '.') }})</span>
                                @else
                                    <span class="badge-soft-info">- Tidak ada data plafon</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="detail-label">Deskripsi Kegiatan</div>
                            <div class="detail-value p-3" style="background: #f8fafc; border-radius: 6px; border: 1px solid var(--border-color);">
                                {{ $event->description }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="detail-label mb-3">Foto Bukti</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                @if($event->foto_event_1)
                                    <a href="{{ asset('storage/' . $event->foto_event_1) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $event->foto_event_1) }}" class="foto-preview-detail" alt="Foto 1">
                                    </a>
                                    <div class="text-center mt-2"><small class="text-muted">Foto 1 (Wajib)</small></div>
                                @else
                                    <div class="text-center p-4 border rounded">Tidak ada foto</div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                @if($event->foto_event_2)
                                    <a href="{{ asset('storage/' . $event->foto_event_2) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $event->foto_event_2) }}" class="foto-preview-detail" alt="Foto 2">
                                    </a>
                                    <div class="text-center mt-2"><small class="text-muted">Foto 2</small></div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                @if($event->foto_event_3)
                                    <a href="{{ asset('storage/' . $event->foto_event_3) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $event->foto_event_3) }}" class="foto-preview-detail" alt="Foto 3">
                                    </a>
                                    <div class="text-center mt-2"><small class="text-muted">Foto 3</small></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 🎯 MODE 2: FORM EDIT (Tersembunyi Default) -->
        <!-- ========================================== -->
        <div id="edit-mode" style="display: none;">
            <!-- Info Plafon Real-time (Akan muncul saat edit) -->
            <div class="card-modern" id="card-info-plafon-event" style="display:none;">
                <div class="card-header-modern">
                    <h5><i class="bi bi-bar-chart"></i> Informasi Plafon Event</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center">
                        <div class="col-md-4"><div class="stat-box"><small class="d-block">Plafon</small><span id="info-plafon-event-total" class="value">Rp 0</span></div></div>
                        <div class="col-md-4"><div class="stat-box"><small class="d-block">Nominal Input</small><span id="info-nominal-event" class="value">Rp 0</span></div></div>
                        <div class="col-md-4"><div class="stat-box"><small class="d-block">Sisa</small><span id="info-sisa-event" class="value">Rp 0</span></div></div>
                    </div>
                    <div id="plafon-event-status" class="mt-3 text-center"></div>
                </div>
            </div>

            <div class="card-modern">
                <div class="card-header-modern" style="background: #fffbeb; border-bottom-color: #fcd34d;">
                    <h5><i class="bi bi-pencil-square me-2"></i>Mode Edit Data</h5>
                </div>
                <div class="card-body p-4">
                    <form id="form-event" action="{{ route('perongkosan_event_dalam_kota.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">ID Transaksi</label>
                                <input type="text" class="form-control" value="{{ $event->transaksi_id }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Event <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', $event->tanggal) }}" required>
                            </div>
                        </div>

                        <div class="row g-4 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">User (Karyawan)</label>
                                <input type="text" class="form-control" value="{{ $event->user->nama ?? $event->user->name }} (NIK: {{ $event->user->NIK ?? '-' }})" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="nominal" id="nominal" class="form-control" placeholder="0" min="1" value="{{ old('nominal', $event->nominal) }}" required>
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
                                        <option value="{{ $jk->id }}" {{ old('jam_kerja_event_id', $event->jam_kerja_event_id) == $jk->id ? 'selected' : '' }}>
                                            {{ $jk->name ?? $jk->nama ?? $jk->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" required>{{ old('description', $event->description) }}</textarea>
                        </div>

                        <div class="mt-4">
                            <div class="detail-label mb-3">Update Foto Bukti (Opsional)</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <label class="form-label small mb-2">Foto 1 <span class="badge bg-danger">Wajib jika diganti</span></label>
                                        @if($event->foto_event_1)
                                            <img src="{{ asset('storage/' . $event->foto_event_1) }}" class="foto-preview-detail mb-2" style="height: 100px;">
                                        @endif
                                        <input type="file" name="foto_event_1" class="form-control form-control-sm" accept="image/*">
                                        <small class="text-muted" style="font-size: 0.7rem;">Biarkan kosong jika tidak ingin mengganti</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <label class="form-label small mb-2">Foto 2</label>
                                        @if($event->foto_event_2)
                                            <img src="{{ asset('storage/' . $event->foto_event_2) }}" class="foto-preview-detail mb-2" style="height: 100px;">
                                        @endif
                                        <input type="file" name="foto_event_2" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <label class="form-label small mb-2">Foto 3</label>
                                        @if($event->foto_event_3)
                                            <img src="{{ asset('storage/' . $event->foto_event_3) }}" class="foto-preview-detail mb-2" style="height: 100px;">
                                        @endif
                                        <input type="file" name="foto_event_3" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" id="btn-submit" class="btn btn-modern" style="background: var(--primary-color); color: white;">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                            <button type="button" onclick="toggleEditMode()" class="btn btn-modern" style="background: var(--secondary-color); color: white;">
                                <i class="bi bi-x-lg"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 🎯 Logika Toggle View/Edit Mode
        function toggleEditMode() {
            const viewMode = document.getElementById('view-mode');
            const editMode = document.getElementById('edit-mode');
            
            if (viewMode.style.display === 'none') {
                // Kembali ke mode View
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Masuk ke mode Edit
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Trigger cek plafon saat masuk mode edit
                ambilInfoPlafonEvent();
            }
        }

        // 🎯 Logika Disable Tombol Submit
        document.getElementById('form-event')?.addEventListener('submit', function() {
            var btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sedang Menyimpan...';
        });

        // 🎯 Logika Plafon Real-time (Sama seperti sebelumnya)
        $(document).ready(function() {
            let plafonEvent = 0;
            function formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); }

            function renderPlafonEvent() {
                if (plafonEvent <= 0) { $('#card-info-plafon-event').hide(); return; }
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
                    }
                });
            }

            $('#tanggal, #is_libur, #jam_kerja_event_id').on('change', ambilInfoPlafonEvent);
            $('#nominal').on('input change', renderPlafonEvent);
        });
    </script>
</body>
</html>
