<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Event Luar Kota — EMMO APP</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --border-strong: #cbd5e1;
            --input-bg: #f8fafc;
            --accent-primary: #3b82f6;
            --accent-success: #10b981;
            --accent-info: #0ea5e9;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            background-image:
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.05) 0px, transparent 50%);
        }

        /* ---------- Navbar Modern ---------- */
        .navbar-custom {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--text-primary) !important;
            letter-spacing: -0.025em;
            font-size: 1.25rem;
            text-decoration: none;
        }
        .navbar-brand i { color: var(--accent-primary); }
        .btn-nav {
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-nav-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        .btn-nav-outline:hover {
            background-color: #f1f5f9;
            color: var(--text-primary);
            border-color: var(--border-strong);
        }
        .btn-nav-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #ef4444;
        }
        .btn-nav-danger:hover {
            background-color: #fee2e2;
            border-color: #fca5a5;
            color: #dc2626;
        }

        /* ---------- Page Header ---------- */
        .page-header {
            padding: 2.5rem 0 1.5rem;
        }
        .page-title {
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            margin-bottom: 0.5rem;
        }
        .page-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 400;
            margin: 0;
        }
        .page-breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 50px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .page-breadcrumb .current { color: var(--accent-primary); font-weight: 600; }

        /* ---------- Form Card ---------- */
        .form-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px -12px rgba(0,0,0,0.06);
            position: relative;
        }
        .form-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-success), var(--accent-primary));
        }
        .form-card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }
        .form-card-header .title-wrap {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }
        .form-icon-wrapper {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: #ecfdf5;
            color: var(--accent-success);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
        }
        .form-title {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.02em;
        }
        .form-card-body { padding: 2rem; }

        /* ---------- Form Controls ---------- */
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .form-label .required { color: var(--accent-danger); }
        .form-label small { color: var(--text-muted); font-weight: 400; font-size: 0.75rem; }

        .form-control, .form-select {
            font-family: 'Plus Jakarta Sans', sans-serif;
            border: 1px solid var(--border-color);
            background-color: var(--input-bg);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 0.625rem 0.875rem;
            font-size: 0.925rem;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-primary);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            outline: none;
        }
        .form-control.is-disabled, .form-control[readonly], .form-control[disabled],
        .form-select[disabled] {
            background-color: #f1f5f9;
            color: var(--text-muted);
            cursor: not-allowed;
            border-color: var(--border-color);
        }
        .form-control::placeholder { color: var(--text-muted); }

        /* File input modern */
        .file-input-wrap {
            position: relative;
        }
        .file-input-wrap input[type=file] {
            padding: 0.5rem;
        }

        /* ---------- Textarea ---------- */
        textarea.form-control {
            min-height: 90px;
            resize: vertical;
        }

        /* ---------- Info Alert (Preview Lokasi) ---------- */
        .location-preview {
            background: #eff6ff;
            border: 1px dashed #bfdbfe;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            color: #1e40af;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .location-preview.success {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .location-preview i { font-size: 1.1rem; }

        /* ---------- Upload Card ---------- */
        .upload-section {
            background: #f8fafc;
            border: 1px dashed var(--border-strong);
            border-radius: 14px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        .upload-section h6 {
            font-weight: 700;
            color: var(--text-primary);
            display: flex; align-items: center; gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .upload-section h6 i { color: var(--accent-primary); }
        .upload-item {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .upload-item label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-weight: 500;
            margin: 0 0 0.25rem 0.25rem;
        }

        /* ---------- Alerts (success/error) ---------- */
        .alert-custom-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-custom-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        .alert-custom-danger strong { font-weight: 700; }
        .alert-custom-danger ul { margin: 0.25rem 0 0 0; padding-left: 1.25rem; font-weight: 500; }

        /* ---------- Plafon Info Card ---------- */
        .plafon-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 10px 30px -12px rgba(14, 165, 233, 0.15);
        }
        .plafon-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--accent-info);
        }
        .plafon-card .card-header {
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-bottom: 1px solid #bae6fd;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .plafon-card .card-header h6 {
            margin: 0;
            font-weight: 700;
            color: #0c4a6e;
            font-size: 0.95rem;
        }
        .plafon-card .card-header i { color: var(--accent-info); }
        .plafon-card .card-body { padding: 1.5rem; }
        .plafon-metric {
            text-align: center;
            padding: 0.5rem;
        }
        .plafon-metric small {
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            display: block;
            margin-bottom: 0.35rem;
        }
        .plafon-metric .value {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .plafon-metric .value.primary { color: var(--accent-primary); }
        .plafon-metric .value.success { color: var(--accent-success); }
        .plafon-metric .value.info    { color: var(--accent-info); }
        .plafon-metric .value.danger  { color: var(--accent-danger); }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.875rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-badge.badge-success { background: #dcfce7; color: #166534; }
        .status-badge.badge-danger  { background: #fee2e2; color: #991b1b; }

        /* ---------- Submit Button ---------- */
        .btn-submit {
            background: var(--text-primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: -0.01em;
            width: 100%;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .btn-submit:hover:not(:disabled) {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.35);
            color: white;
        }
        .btn-submit:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            opacity: 0.9;
        }

        .btn-back {
            background: white;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            border-radius: 10px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn-back:hover {
            background: #f1f5f9;
            color: var(--text-primary);
            border-color: var(--border-strong);
        }

        /* Helper */
        .section-divider {
            height: 1px;
            background: var(--border-color);
            margin: 1.75rem 0;
        }
        .user-readbox {
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.625rem 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.925rem;
        }
        .user-readbox i { color: var(--accent-primary); }

        /* Responsive */
        @media (max-width: 768px) {
            .form-card-body { padding: 1.25rem; }
            .form-card-header { padding: 1rem 1.25rem; }
            .plafon-metric .value { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')

    <!-- Modern Clean Navbar (sama persis dengan portal) -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard.transaksi') }}">
                <i class="bi bi-hexagon-fill me-2"></i> EMMO APP
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a href="{{ route('profile.index') }}" class="btn btn-nav btn-nav-outline">
                            <i class="bi bi-person-circle"></i> Profil Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-nav btn-nav-danger">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 960px;">

        <!-- Page Header -->
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="display-6 page-title">Form Event Luar Kota</h1>
                <p class="page-subtitle">Catat pengeluaran kegiatan event di luar kota dengan plafon biaya real-time.</p>
            </div>
            <div class="page-breadcrumb">
                <i class="bi bi-house-door"></i>
                <a href="{{ route('dashboard.transaksi') }}" class="text-decoration-none text-secondary">Transaksi</a>
                <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                <span class="current">Event Luar Kota</span>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert-custom-success mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert-custom-danger mb-3">
                <div>
                    <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal Menyimpan Data</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- 🎯 CARD INFO PLAFON EVENT (Real-time) -->
        <div class="plafon-card mb-4" id="card-info-plafon-event" style="display:none;">
            <div class="card-header">
                <i class="bi bi-graph-up-arrow"></i>
                <h6>Informasi Plafon Event</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="plafon-metric">
                            <small>Plafon Tersedia</small>
                            <div class="value primary" id="info-plafon-event-total">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="plafon-metric">
                            <small>Nominal Input</small>
                            <div class="value success" id="info-nominal-event">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="plafon-metric">
                            <small>Sisa Plafon</small>
                            <div class="value info" id="info-sisa-event">Rp 0</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3" id="plafon-event-status"></div>
            </div>
        </div>

        <!-- 🎯 FORM CARD -->
        <div class="form-card mb-5">
            <div class="form-card-header">
                <div class="title-wrap">
                    <div class="form-icon-wrapper">
                        <i class="bi bi-airplane-engines"></i>
                    </div>
                    <h5 class="form-title">Perongkosan Event Luar Kota</h5>
                </div>
                <a href="{{ route('dashboard.transaksi') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="form-card-body">
                <form id="form-event-luar" action="{{ route('perongkosan_event_luar_kota.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="lokasi_event_id" id="lokasi_event_id" value="{{ old('lokasi_event_id') }}" required>

                    <!-- Row 1: ID & Tanggal -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ID Transaksi</label>
                            <input type="text" class="form-control is-disabled" value="Otomatis (EV-LKR-YYMMDD-XXX)" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Tanggal Event <span class="required">*</span>
                            </label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', \App\Support\AppTime::now()->toDateString()) }}" required>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Section: Lokasi -->
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                            <i class="bi bi-geo-alt-fill"></i> Lokasi Event
                        </label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                Territory <span class="required">*</span>
                            </label>
                            <select name="teritory" id="teritory-select" class="form-select" required>
                                <option value="">-- Pilih Territory --</option>
                                <option value="Jawa" {{ old('teritory') == 'Jawa' ? 'selected' : '' }}>Jawa (Pulau Jawa)</option>
                                <option value="Luar Jawa" {{ old('teritory') == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Provinsi <span class="required">*</span>
                            </label>
                            <select id="provinsi-select" class="form-select" required disabled>
                                <option value="">-- Pilih Territory --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Kota/Kabupaten <small class="text-muted">(Opsional)</small>
                            </label>
                            <select id="kota-select" class="form-select" disabled>
                                <option value="">-- Pilih Provinsi --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 mb-2">
                        <div class="location-preview" id="preview-lokasi">
                            <i class="bi bi-geo-alt"></i>
                            <span id="preview-text">Belum ada lokasi yang dipilih.</span>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Section: Detail Transaksi -->
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                            <i class="bi bi-receipt"></i> Detail Transaksi
                        </label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">User (Karyawan)</label>
                            <div class="user-readbox">
                                <i class="bi bi-person-badge-fill"></i>
                                <span>{{ Auth::user()->nama ?? Auth::user()->name }} <span class="text-muted">(NIK: {{ Auth::user()->NIK ?? '-' }})</span></span>
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.8em;">*Transaksi otomatis dicatat atas nama akun Anda.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Nominal Pengeluaran (Rp) <span class="required">*</span>
                            </label>
                            <input type="number" name="nominal" id="nominal" class="form-control" placeholder="0" min="1" value="{{ old('nominal') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">
                                Hari Libur? <span class="required">*</span>
                            </label>
                            <select name="is_libur" id="is_libur" class="form-select" required>
                                <option value="0" {{ old('is_libur') == '0' ? 'selected' : '' }}>Bukan Hari Libur (N)</option>
                                <option value="1" {{ old('is_libur') == '1' ? 'selected' : '' }}>Hari Libur (Y)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Jam Kerja Event <span class="required">*</span>
                            </label>
                            <select name="jam_kerja_event_id" id="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($jam_kerja as $jk)
                                    <option value="{{ $jk->id }}" {{ old('jam_kerja_event_id') == $jk->id ? 'selected' : '' }}>
                                        {{ $jk->name ?? $jk->nama ?? $jk->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">
                            Deskripsi Kegiatan <span class="required">*</span>
                        </label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Tulis rincian deskripsi kegiatan event luar kota..." required>{{ old('description') }}</textarea>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Section: Upload -->
                    <div class="upload-section">
                        <h6>
                            <i class="bi bi-camera-fill"></i>
                            Upload Foto Bukti Nota/Kegiatan
                            <small class="text-muted fw-normal ms-auto" style="font-size: 0.8rem;">Maks 2MB / foto</small>
                        </h6>

                        <div class="upload-item">
                            <label>Foto Event 1 <span class="required">*</span></label>
                            <input type="file" name="foto_event_1" class="form-control" accept="image/*" required>
                        </div>
                        <div class="upload-item">
                            <label>Foto Event 2 <span class="text-muted">(Opsional)</span></label>
                            <input type="file" name="foto_event_2" class="form-control" accept="image/*">
                        </div>
                        <div class="upload-item mb-0">
                            <label>Foto Event 3 <span class="text-muted">(Opsional)</span></label>
                            <input type="file" name="foto_event_3" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" id="btn-submit-luar" class="btn-submit">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        Simpan Data Event Luar Kota
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 🎯 SCRIPT JAVASCRIPT (LOGIKA TETAP SAMA, HANYA UI UPDATE) -->
    <script>
        $(document).ready(function() {

            // ===== 1. CASCADE WILAYAH =====
            $('#teritory-select').on('change', function() {
                var teritory = $(this).val();
                var provinsiSelect = $('#provinsi-select');
                var kotaSelect = $('#kota-select');

                provinsiSelect.prop('disabled', true).html('<option value="">-- Loading Provinsi... --</option>');
                kotaSelect.prop('disabled', true).html('<option value="">-- Pilih Provinsi --</option>');
                resetLokasi();

                if (teritory) {
                    $.ajax({
                        url: '{{ route("api.event_luar.wilayah.provinsi") }}',
                        type: 'GET',
                        data: { teritory: teritory },
                        dataType: 'json',
                        success: function(response) {
                            provinsiSelect.prop('disabled', false).html('<option value="">-- Pilih Provinsi --</option>');
                            if (response.length > 0) {
                                $.each(response, function(key, value) {
                                    provinsiSelect.append('<option value="' + value.id + '">' + value.nama + '</option>');
                                });
                            }
                        }
                    });
                }
            });

            $('#provinsi-select').on('change', function() {
                var provinsiId = $(this).val();
                var kotaSelect = $('#kota-select');

                $('#lokasi_event_id').val(provinsiId || '');

                if (provinsiId) {
                    kotaSelect.prop('disabled', false).html('<option value="">-- Loading Kota/Kabupaten... --</option>');
                    $.ajax({
                        url: '{{ route("api.event_luar.wilayah.kota") }}',
                        type: 'GET',
                        data: { provinsi_id: provinsiId },
                        dataType: 'json',
                        success: function(response) {
                            kotaSelect.html('<option value="">-- Kota/Kabupaten (opsional) --</option>');
                            if (response.length > 0) {
                                $.each(response, function(key, value) {
                                    kotaSelect.append('<option value="' + value.id + '">' + value.nama + '</option>');
                                });
                            }
                            updatePreview();
                        }
                    });
                } else {
                    kotaSelect.prop('disabled', true).html('<option value="">-- Pilih Provinsi --</option>');
                    resetLokasi();
                }
            });

            $('#kota-select').on('change', function() { updatePreview(); });

            function updatePreview() {
                if (!$('#lokasi_event_id').val()) { resetLokasi(); return; }

                var provinsiText = $('#provinsi-select option:selected').text();
                var kotaText = $('#kota-select').val() ? $('#kota-select option:selected').text() : null;

                var html = '<strong>' + provinsiText + '</strong>';
                if (kotaText) html += ' — ' + kotaText;

                $('#preview-text').html(html);
                $('#preview-lokasi').addClass('success');
            }

            function resetLokasi() {
                $('#lokasi_event_id').val('');
                $('#preview-text').text('Belum ada lokasi yang dipilih.');
                $('#preview-lokasi').removeClass('success');
            }

            // ===== 2. SUBMIT HANDLING =====
            $('#form-event-luar').on('submit', function(e) {
                if (!$('#lokasi_event_id').val()) {
                    e.preventDefault();
                    alert('⚠️ Silakan pilih PROVINSI lokasi event terlebih dahulu!');
                    $('#provinsi-select').focus();
                    return false;
                }
                $('#btn-submit-luar')
                    .attr('disabled', true)
                    .html('<i class="bi bi-hourglass-split"></i> Sedang Menyimpan Data & Upload Foto...');
            });

            // ===== 3. RESTORE OLD INPUT =====
            @if(old('teritory'))
                $('#teritory-select').trigger('change');
                setTimeout(function() {
                    @if(old('provinsi_id'))
                        $('#provinsi-select').val('{{ old("provinsi_id") }}').trigger('change');
                        setTimeout(function() {
                            @if(old('lokasi_event_id'))
                                $('#kota-select').val('{{ old("lokasi_event_id") }}').trigger('change');
                            @endif
                        }, 500);
                    @endif
                }, 500);
            @endif

            // ===== 4. LOGIKA PLAFON REAL-TIME =====
            let plafonEvent = 0;

            function formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n)); }

            function renderPlafonEvent() {
                if (plafonEvent <= 0) {
                    $('#card-info-plafon-event').slideUp();
                    return;
                }
                const nominal = parseFloat($('#nominal').val()) || 0;
                const sisa = plafonEvent - nominal;

                $('#info-plafon-event-total').text(formatRupiah(plafonEvent));
                $('#info-nominal-event').text(formatRupiah(nominal));

                const sisaEl = $('#info-sisa-event');
                sisaEl.text(formatRupiah(Math.abs(sisa)));

                if (sisa < 0) {
                    sisaEl.removeClass('info success').addClass('danger');
                    $('#plafon-event-status').html(
                        '<span class="status-badge badge-danger">' +
                        '<i class="bi bi-exclamation-triangle-fill"></i> OVER PLAFON — Selisih: ' + formatRupiah(Math.abs(sisa)) +
                        '</span>'
                    );
                } else {
                    sisaEl.removeClass('danger').addClass('info');
                    $('#plafon-event-status').html(
                        '<span class="status-badge badge-success">' +
                        '<i class="bi bi-check-circle-fill"></i> Dalam Plafon — Sisa: ' + formatRupiah(sisa) +
                        '</span>'
                    );
                }
            }

            function ambilInfoPlafonEvent() {
                const tanggal = $('#tanggal').val();
                const isLibur = $('#is_libur').val();
                const jamId   = $('#jam_kerja_event_id').val();

                if (!tanggal || isLibur === '' || isLibur === null || !jamId) {
                    plafonEvent = 0;
                    $('#card-info-plafon-event').slideUp();
                    return;
                }

                $.ajax({
                    url: '{{ route("api.event_luar_kota.plafon_info") }}',
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
                            $('#card-info-plafon-event').slideDown();
                            renderPlafonEvent();
                        } else {
                            plafonEvent = 0;
                            $('#card-info-plafon-event').slideUp();
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
