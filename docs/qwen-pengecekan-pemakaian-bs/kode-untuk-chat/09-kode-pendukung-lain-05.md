# Kode existing: kode-pendukung-lain, bagian 5

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: resources/views/perongkosan_event_luar_kota/create.blade.php

SHA-256: `99427393681985b3339bf1a90a050a47ba5c74790462835523e9bbcab7df0c83`

~~~~php
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

~~~~

## FILE: resources/views/perongkosan_event_luar_kota/edit.blade.php

SHA-256: `763a85945916e72b770b551637655386adfb757360dd53d752262d46bd37a6c8`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event Luar Kota — EMMO APP</title>

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
            --accent-warning: #f59e0b; /* Amber untuk Edit */
            --accent-danger: #ef4444;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            background-image:
                radial-gradient(at 0% 0%, rgba(245, 158, 11, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.04) 0px, transparent 50%);
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
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .page-title .edit-tag {
            font-size: 0.7rem;
            font-weight: 700;
            background: #fef3c7;
            color: #92400e;
            padding: 0.25rem 0.625rem;
            border-radius: 50px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
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
        .page-breadcrumb .current { color: var(--accent-warning); font-weight: 600; }

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
            background: linear-gradient(90deg, var(--accent-warning), #fbbf24);
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
            background: #fef3c7;
            color: var(--accent-warning);
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
            border-color: var(--accent-warning);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
            outline: none;
        }
        .form-control.is-disabled, .form-control[readonly], .form-control[disabled],
        .form-select[disabled] {
            background-color: #f1f5f9;
            color: var(--text-muted);
            cursor: not-allowed;
            border-color: var(--border-color);
        }
        textarea.form-control { min-height: 90px; resize: vertical; }

        /* ---------- Location Preview ---------- */
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
            border-style: solid;
        }
        .location-preview i { font-size: 1.1rem; }

        /* ---------- Alerts ---------- */
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

        /* ---------- User Readbox ---------- */
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
        .user-readbox i { color: var(--accent-warning); }

        /* ---------- Upload / Photo Section (khas edit) ---------- */
        .photo-section {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border: 1px dashed var(--accent-warning);
            border-radius: 14px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        .photo-section h6 {
            font-weight: 700;
            color: #78350f;
            display: flex; align-items: center; gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .photo-section h6 i { color: var(--accent-warning); }
        .photo-section .subtitle {
            font-size: 0.8rem;
            color: #92400e;
            font-weight: 500;
            margin-left: auto;
        }
        .photo-item {
            background: white;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 0.875rem 1rem;
            margin-bottom: 0.625rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .photo-item:last-child { margin-bottom: 0; }
        .photo-item .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .photo-item label {
            font-size: 0.875rem;
            color: var(--text-primary);
            font-weight: 600;
            margin: 0;
        }
        .existing-photo-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            color: var(--accent-primary);
            text-decoration: none;
            background: #eff6ff;
            padding: 0.25rem 0.625rem;
            border-radius: 50px;
            border: 1px solid #dbeafe;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .existing-photo-link:hover {
            background: #dbeafe;
            color: #1e40af;
        }
        .existing-photo-link i { font-size: 0.75rem; }

        /* ---------- Buttons ---------- */
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

        .section-divider {
            height: 1px;
            background: var(--border-color);
            margin: 1.75rem 0;
        }

        .section-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .section-label i { color: var(--accent-warning); }

        /* Transaction ID badge */
        .trx-id-box {
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.625rem 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.925rem;
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, monospace;
            letter-spacing: 0.02em;
        }
        .trx-id-box i { color: var(--accent-warning); }

        @media (max-width: 768px) {
            .form-card-body { padding: 1.25rem; }
            .form-card-header { padding: 1rem 1.25rem; }
            .photo-item .top-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')

    <!-- Modern Clean Navbar -->
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

        @php
            // 🎯 LOGIKA PRE-FILL: Deteksi hierarchy dari data yang sudah ada
            $w = $event->wilayah;
            $currentTeritory = $w->teritory ?? '';
            $isProvince = $w->parent_id === null;
            $currentProvinsiId = $isProvince ? $w->id : $w->parent_id;
            $currentKotaId = $isProvince ? null : $w->id;
        @endphp

        <!-- Page Header -->
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="display-6 page-title">
                    Edit Event Luar Kota
                    <span class="edit-tag">Update</span>
                </h1>
                <p class="page-subtitle">Perbarui data pengeluaran event luar kota — {{ $event->transaksi_id }}</p>
            </div>
            <div class="page-breadcrumb">
                <i class="bi bi-house-door"></i>
                <a href="{{ route('dashboard.transaksi') }}" class="text-decoration-none text-secondary">Transaksi</a>
                <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                <a href="{{ route('perongkosan_event_luar_kota.index') }}" class="text-decoration-none text-secondary">Event Luar</a>
                <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                <span class="current">Edit</span>
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
                    <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal Memperbarui Data</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- 🎯 FORM CARD -->
        <div class="form-card mb-5">
            <div class="form-card-header">
                <div class="title-wrap">
                    <div class="form-icon-wrapper">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <h5 class="form-title">Perbarui Data Perongkosan</h5>
                </div>
                <a href="{{ route('perongkosan_event_luar_kota.index') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="form-card-body">
                <form id="form-edit-event" action="{{ route('perongkosan_event_luar_kota.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- 🎯 HIDDEN INPUT: lokasi_event_id = PROVINSI ID (konsisten dengan create) -->
                    <input type="hidden" name="lokasi_event_id" id="lokasi_event_id" value="{{ $currentProvinsiId }}" required>

                    <!-- Section: Identitas Transaksi -->
                    <div class="section-label">
                        <i class="bi bi-hash"></i> Identitas Transaksi
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ID Transaksi</label>
                            <div class="trx-id-box">
                                <i class="bi bi-receipt"></i>
                                <span>{{ $event->transaksi_id }}</span>
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.8em;">*ID transaksi tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Tanggal Event <span class="required">*</span>
                            </label>
                            <input type="date" name="tanggal" class="form-control" value="{{ $event->tanggal }}" required>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Section: Lokasi Event -->
                    <div class="section-label">
                        <i class="bi bi-geo-alt-fill"></i> Lokasi Event
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                Territory <span class="required">*</span>
                            </label>
                            <select name="teritory" id="teritory-select" class="form-select" required>
                                <option value="">-- Pilih Territory --</option>
                                <option value="Jawa" {{ $currentTeritory == 'Jawa' ? 'selected' : '' }}>Jawa (Pulau Jawa)</option>
                                <option value="Luar Jawa" {{ $currentTeritory == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Provinsi <span class="required">*</span>
                            </label>
                            <select id="provinsi-select" class="form-select" required disabled>
                                <option value="">-- Loading... --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Kota/Kabupaten <small>(Opsional)</small>
                            </label>
                            <select id="kota-select" class="form-select" disabled>
                                <option value="">-- Loading... --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Preview Lokasi -->
                    <div class="mt-3 mb-2">
                        <div class="location-preview success" id="preview-lokasi">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span id="preview-text">
                                <strong>{{ $w->nama }}</strong> 
                                <small style="opacity: 0.8;">(Territory: {{ $currentTeritory }})</small>
                            </span>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Section: Detail Transaksi -->
                    <div class="section-label">
                        <i class="bi bi-cash-stack"></i> Detail Transaksi
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">User (Karyawan)</label>
                            <div class="user-readbox">
                                <i class="bi bi-person-badge-fill"></i>
                                <span>{{ $event->user->nama ?? $event->user->name }} <span class="text-muted">(NIK: {{ $event->user->NIK ?? '-' }})</span></span>
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.8em;">*User tidak dapat diubah saat edit.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Nominal (Rp) <span class="required">*</span>
                            </label>
                            <input type="number" name="nominal" class="form-control" value="{{ $event->nominal }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">
                                Jam Kerja Event <span class="required">*</span>
                            </label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                @foreach($jam_kerja as $jk)
                                    <option value="{{ $jk->id }}" {{ $event->jam_kerja_event_id == $jk->id ? 'selected' : '' }}>
                                        {{ $jk->name ?? $jk->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Hari Libur? <span class="required">*</span>
                            </label>
                            <select name="is_libur" class="form-select" required>
                                <option value="0" {{ $event->is_libur == 0 ? 'selected' : '' }}>Bukan Hari Libur (N)</option>
                                <option value="1" {{ $event->is_libur == 1 ? 'selected' : '' }}>Hari Libur (Y)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">
                            Deskripsi Kegiatan <span class="required">*</span>
                        </label>
                        <textarea name="description" class="form-control" rows="2" required>{{ $event->description }}</textarea>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Section: Upload Foto (Ganti) -->
                    <div class="photo-section">
                        <h6>
                            <i class="bi bi-image-fill"></i>
                            Ganti Foto Bukti
                            <span class="subtitle">Kosongkan jika tidak ingin mengubah foto</span>
                        </h6>

                        @foreach(['foto_event_1' => 'Foto Event 1', 'foto_event_2' => 'Foto Event 2', 'foto_event_3' => 'Foto Event 3'] as $field => $label)
                            <div class="photo-item">
                                <div class="top-row">
                                    <label>{{ $label }}</label>
                                    @if($event->$field)
                                        <a href="{{ asset('storage/'.$event->$field) }}" target="_blank" class="existing-photo-link">
                                            <i class="bi bi-eye-fill"></i> Lihat Foto Lama
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size: 0.8rem;"><i class="bi bi-dash-circle"></i> Belum ada foto</span>
                                    @endif
                                </div>
                                <input type="file" name="{{ $field }}" class="form-control" accept="image/*">
                            </div>
                        @endforeach
                    </div>

                    <!-- Submit -->
                    <button type="submit" id="btn-submit-edit" class="btn-submit">
                        <i class="bi bi-check2-circle"></i>
                        Perbarui Data Event
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            const currentTeritory = '{{ $currentTeritory }}';
            const currentProvinsiId = '{{ $currentProvinsiId }}';
            const currentKotaId = '{{ $currentKotaId }}';

            // ===== 1. LOAD PROVINSI (dengan pre-select untuk edit) =====
            function loadProvinsi(teritory, selectedId = null) {
                if (!teritory) return;
                $.ajax({
                    url: '{{ route("api.event_luar.wilayah.provinsi") }}',
                    type: 'GET',
                    data: { teritory: teritory },
                    dataType: 'json',
                    success: function(response) {
                        let provinsiSelect = $('#provinsi-select');
                        provinsiSelect.prop('disabled', false).html('<option value="">-- Pilih Provinsi --</option>');
                        if (response.length > 0) {
                            $.each(response, function(key, value) {
                                let isSelected = (selectedId && value.id == selectedId) ? 'selected' : '';
                                provinsiSelect.append('<option value="' + value.id + '" ' + isSelected + '>' + value.nama + '</option>');
                            });
                        }
                        if (currentKotaId) {
                            loadKota(currentProvinsiId, currentKotaId);
                        }
                    }
                });
            }

            // ===== 2. LOAD KOTA (dengan pre-select untuk edit) =====
            function loadKota(provinsiId, selectedId = null) {
                if (!provinsiId) return;
                $.ajax({
                    url: '{{ route("api.event_luar.wilayah.kota") }}',
                    type: 'GET',
                    data: { provinsi_id: provinsiId },
                    dataType: 'json',
                    success: function(response) {
                        let kotaSelect = $('#kota-select');
                        kotaSelect.prop('disabled', false).html('<option value="">-- Kota/Kabupaten (opsional) --</option>');
                        if (response.length > 0) {
                            $.each(response, function(key, value) {
                                let isSelected = (selectedId && value.id == selectedId) ? 'selected' : '';
                                kotaSelect.append('<option value="' + value.id + '" ' + isSelected + '>' + value.nama + '</option>');
                            });
                        }
                    }
                });
            }

            // ===== 3. PRE-FILL saat halaman dimuat =====
            if (currentTeritory) {
                $('#lokasi_event_id').val(currentProvinsiId); // ✅ Tetap PROVINSI ID
                loadProvinsi(currentTeritory, currentProvinsiId);
            }

            // ===== 4. Territory berubah → reset cascade =====
            $('#teritory-select').on('change', function() {
                let teritory = $(this).val();
                $('#provinsi-select').prop('disabled', true).html('<option value="">-- Loading Provinsi... --</option>');
                $('#kota-select').prop('disabled', true).html('<option value="">-- Pilih Provinsi --</option>');
                $('#lokasi_event_id').val('');
                resetPreview();
                if (teritory) loadProvinsi(teritory);
            });

            // ===== 5. Provinsi berubah → simpan PROVINSI ID =====
            $('#provinsi-select').on('change', function() {
                let provinsiId = $(this).val();
                $('#lokasi_event_id').val(provinsiId); // ✅ PROVINSI ID
                $('#kota-select').prop('disabled', true).html('<option value="">-- Loading Kota/Kabupaten... --</option>');
                resetPreview();
                if (provinsiId) loadKota(provinsiId);
            });

            // ===== 6. Kota berubah → HANYA update preview (konsisten dengan create.blade) =====
            $('#kota-select').on('change', function() {
                updatePreview();
            });

            // ===== 7. Helper: update preview lokasi =====
            function updatePreview() {
                if (!$('#lokasi_event_id').val()) { resetPreview(); return; }

                var provinsiText = $('#provinsi-select option:selected').text();
                var kotaText = $('#kota-select').val() ? $('#kota-select option:selected').text() : null;

                var html = '<strong>' + provinsiText + '</strong>';
                if (kotaText) html += ' — ' + kotaText;
                html += ' <small style="opacity: 0.75;">(tersimpan: Provinsi)</small>';

                $('#preview-text').html(html);
                $('#preview-lokasi').addClass('success');
            }

            function resetPreview() {
                $('#preview-text').html('<em>Memuat lokasi...</em>');
                $('#preview-lokasi').removeClass('success');
            }

            // ===== 8. Submit validation =====
            $('#form-edit-event').on('submit', function(e) {
                if (!$('#lokasi_event_id').val()) {
                    e.preventDefault();
                    alert('⚠️ Silakan pilih PROVINSI lokasi event terlebih dahulu!');
                    $('#provinsi-select').focus();
                    return false;
                }
                $('#btn-submit-edit')
                    .attr('disabled', true)
                    .html('<i class="bi bi-hourglass-split"></i> Sedang Memperbarui Data...');
            });
        });
    </script>
</body>
</html>

~~~~

## FILE: resources/views/perongkosan_event_luar_kota/index.blade.php

SHA-256: `54b22543e3985a6d7f62aec64b6f5208120e2ffd86998d59974ff7b0385fb98f`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Event Luar Kota — EMMO APP</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --border-strong: #cbd5e1;
            --accent-primary: #3b82f6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --accent-info: #0ea5e9;
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
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .page-title .count-tag {
            font-size: 0.8rem;
            font-weight: 700;
            background: #dcfce7;
            color: #166534;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            letter-spacing: 0;
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
        .page-breadcrumb a { text-decoration: none; color: var(--text-secondary); }
        .page-breadcrumb a:hover { color: var(--text-primary); }
        .page-breadcrumb .current { color: var(--accent-success); font-weight: 600; }

        /* ---------- Alerts ---------- */
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

        /* ---------- Stats Cards ---------- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
            border-color: transparent;
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .stat-icon.green  { background: #dcfce7; color: #16a34a; }
        .stat-icon.blue   { background: #dbeafe; color: #2563eb; }
        .stat-icon.amber  { background: #fef3c7; color: #d97706; }
        .stat-icon.red    { background: #fee2e2; color: #dc2626; }
        .stat-info .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-bottom: 0.125rem;
        }
        .stat-info .value {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }
        .stat-info .value.small-text {
            font-size: 1rem;
            font-weight: 600;
        }

        /* ---------- Table Card ---------- */
        .table-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px -12px rgba(0,0,0,0.06);
            position: relative;
        }
        .table-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-success), var(--accent-primary));
        }

        /* Action Bar */
        .action-bar {
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            background: white;
        }
        .action-bar-left {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }
        .action-icon-wrapper {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: #dcfce7;
            color: var(--accent-success);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .action-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.01em;
        }
        .action-subtitle {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin: 0;
        }

        .action-bar-right {
            display: flex;
            gap: 0.625rem;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Export form */
        .export-form {
            display: inline-flex;
            gap: 0.5rem;
            align-items: center;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.375rem 0.375rem 0.375rem 0.875rem;
        }
        .export-form .form-select {
            border: none;
            background: transparent;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            padding: 0.25rem 1.75rem 0.25rem 0;
            min-width: 140px;
        }
        .export-form .form-select:focus {
            box-shadow: none;
            outline: none;
        }

        /* Buttons */
        .btn-primary-action {
            background: var(--text-primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.625rem 1.125rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
        }
        .btn-primary-action:hover {
            background: #1e293b;
            transform: translateY(-1px);
            color: white;
            box-shadow: 0 6px 15px -3px rgba(15, 23, 42, 0.3);
        }
        .btn-success-action {
            background: var(--accent-success);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .btn-success-action:hover {
            background: #059669;
            color: white;
        }
        .btn-back {
            background: white;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            border-radius: 10px;
            padding: 0.625rem 1.125rem;
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

        /* ---------- Table ---------- */
        .table-wrapper {
            overflow-x: auto;
        }
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }
        .modern-table thead th {
            background: #f8fafc;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.25rem;
            border: none;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }
        .modern-table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 0.9rem;
            vertical-align: middle;
            background: white;
            transition: background 0.15s ease;
        }
        .modern-table tbody tr:hover td {
            background: #f8fafc;
        }
        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        .trx-id {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-primary);
            letter-spacing: 0.01em;
        }
        .date-cell {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            white-space: nowrap;
        }
        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        .user-name { font-weight: 600; color: var(--text-primary); font-size: 0.875rem; }
        .location-cell {
            display: flex; align-items: center; gap: 0.5rem;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
        }
        .location-cell i { color: var(--accent-success); }
        .desc-cell {
            color: var(--text-secondary);
            font-size: 0.875rem;
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .nominal-cell {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
            white-space: nowrap;
        }

        /* Over Plafon Badge */
        .badge-over {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
            margin-top: 0.25rem;
            border: 1px solid #fde68a;
        }
        .badge-over i { font-size: 0.65rem; }

        /* Photo Pills */
        .photo-pills {
            display: flex;
            gap: 0.35rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .photo-pill {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid;
        }
        .photo-pill.p1 { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .photo-pill.p1:hover { background: #dbeafe; transform: translateY(-2px); }
        .photo-pill.p2 { background: #ecfeff; border-color: #a5f3fc; color: #0e7490; }
        .photo-pill.p2:hover { background: #cffafe; transform: translateY(-2px); }
        .photo-pill.p3 { background: #f1f5f9; border-color: #cbd5e1; color: #475569; }
        .photo-pill.p3:hover { background: #e2e8f0; transform: translateY(-2px); }
        .no-photo {
            color: var(--text-muted);
            font-size: 0.8rem;
            font-style: italic;
        }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 0.35rem;
        }
        .btn-icon-action {
            width: 34px; height: 34px;
            border-radius: 8px;
            border: 1px solid;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s ease;
            background: white;
            cursor: pointer;
            padding: 0;
        }
        .btn-edit {
            border-color: #fde68a;
            color: #92400e;
            background: #fffbeb;
        }
        .btn-edit:hover {
            background: #fef3c7;
            color: #78350f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px -2px rgba(245, 158, 11, 0.2);
        }
        .btn-delete {
            border-color: #fecaca;
            color: #991b1b;
            background: #fef2f2;
        }
        .btn-delete:hover {
            background: #fee2e2;
            color: #7f1d1d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px -2px rgba(239, 68, 68, 0.2);
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }
        .empty-icon {
            width: 80px; height: 80px;
            border-radius: 20px;
            background: #f1f5f9;
            color: var(--text-muted);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }
        .empty-title {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 1.125rem;
        }
        .empty-subtitle {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        /* Pagination Wrapper */
        .pagination-wrapper {
            padding: 1.25rem 1.75rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafbfc;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .pagination-info {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .pagination-info strong { color: var(--text-primary); font-weight: 700; }
        .pagination .page-link {
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.4rem 0.85rem;
            border-radius: 8px !important;
            margin: 0 2px;
            background: white;
        }
        .pagination .page-link:hover {
            background: #f1f5f9;
            color: var(--text-primary);
            border-color: var(--border-strong);
        }
        .pagination .page-item.active .page-link {
            background: var(--text-primary);
            border-color: var(--text-primary);
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: var(--text-muted);
            background: #f8fafc;
            border-color: var(--border-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .action-bar { padding: 1rem 1.25rem; }
            .action-bar-right { width: 100%; justify-content: flex-start; }
            .export-form { width: 100%; }
            .export-form .form-select { flex-grow: 1; }
            .modern-table thead th,
            .modern-table tbody td { padding: 0.875rem 0.875rem; }
            .desc-cell { max-width: 150px; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')

    <!-- Modern Clean Navbar -->
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

    <div class="container" style="max-width: 1280px;">

        <!-- Page Header -->
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="display-6 page-title">
                    Daftar Event Luar Kota
                    @if(isset($events) && method_exists($events, 'total'))
                        <span class="count-tag">{{ $events->total() }} Transaksi</span>
                    @elseif(isset($events))
                        <span class="count-tag">{{ $events->count() }} Transaksi</span>
                    @endif
                </h1>
                <p class="page-subtitle">Pantau dan kelola seluruh transaksi perongkosan event luar kota.</p>
            </div>
            <div class="page-breadcrumb">
                <i class="bi bi-house-door"></i>
                <a href="{{ route('dashboard.transaksi') }}">Transaksi</a>
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

        @if(session('error'))
            <div class="alert-custom-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- 🎯 STATS OVERVIEW -->
        @if(isset($events) && (method_exists($events, 'count') ? $events->count() : count($events)) > 0)
        @php
            $collection = method_exists($events, 'getCollection') ? $events->getCollection() : $events;
            $totalNominal = $collection->sum('nominal');
            $overCount = $collection->where('is_over_plafon', true)->count();
        @endphp
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-receipt-cutoff"></i></div>
                <div class="stat-info">
                    <div class="label">Total Transaksi</div>
                    <div class="value">{{ method_exists($events, 'total') ? $events->total() : $events->count() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-info">
                    <div class="label">Nominal Halaman Ini</div>
                    <div class="value small-text">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <div class="label">Over Plafon</div>
                    <div class="value" style="color: {{ $overCount > 0 ? '#d97706' : 'var(--text-primary)' }}">{{ $overCount }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- 🎯 TABLE CARD -->
        <div class="table-card">

            <!-- Action Bar -->
            <div class="action-bar">
                <div class="action-bar-left">
                    <div class="action-icon-wrapper">
                        <i class="bi bi-list-ul"></i>
                    </div>
                    <div>
                        <h5 class="action-title">Data Transaksi</h5>
                        <p class="action-subtitle">
                            @if(isset($events) && method_exists($events, 'count'))
                                Menampilkan {{ $events->count() }} dari {{ method_exists($events, 'total') ? $events->total() : $events->count() }} data
                            @else
                                Semua transaksi tercatat
                            @endif
                        </p>
                    </div>
                </div>
                <div class="action-bar-right">
                    {{-- Export Form --}}
                    <form method="GET" action="{{ route('perongkosan_event_luar_kota.export') }}" class="export-form">
                        <i class="bi bi-file-earmark-spreadsheet" style="color: var(--accent-success);"></i>
                        <select name="periode" class="form-select">
                            <option value="">Semua Periode</option>
                            @foreach(\App\Models\Periode::orderBy('periode', 'desc')->get() as $p)
                                <option value="{{ $p->periode }}">{{ $p->periode }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-success-action">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </form>
                    <a href="{{ route('perongkosan_event_luar_kota.create') }}" class="btn-primary-action">
                        <i class="bi bi-plus-lg"></i> Tambah Event
                    </a>
                    <a href="{{ route('dashboard.transaksi') }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Table Content -->
            <div class="table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>Karyawan</th>
                            <th>Lokasi</th>
                            <th>Deskripsi</th>
                            <th>Nominal</th>
                            <th style="text-align: center;">Foto Bukti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td>
                                <span class="trx-id">{{ $event->transaksi_id }}</span>
                            </td>
                            <td>
                                <span class="date-cell">
                                    <i class="bi bi-calendar-event" style="color: var(--accent-primary); margin-right: 0.35rem;"></i>
                                    {{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}
                                </span>
                            </td>
                            <td>
                                <div class="user-cell">
                                    @php
                                        $userName = $event->user->name ?? $event->user->nama ?? 'Unknown';
                                        $initials = collect(explode(' ', $userName))
                                            ->take(2)
                                            ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                                            ->join('');
                                    @endphp
                                    <div class="user-avatar">{{ $initials ?: '??' }}</div>
                                    <span class="user-name">{{ $userName }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="location-cell">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span>{{ $event->wilayah->nama ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="desc-cell" title="{{ $event->description }}">{{ $event->description }}</div>
                            </td>
                            <td>
                                <div class="nominal-cell">Rp {{ number_format($event->nominal, 0, ',', '.') }}</div>
                                @if($event->is_over_plafon)
                                    <div class="badge-over">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        Over Plafon
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($event->foto_event_1 || $event->foto_event_2 || $event->foto_event_3)
                                    <div class="photo-pills">
                                        @if($event->foto_event_1)
                                            <a href="{{ asset('storage/' . $event->foto_event_1) }}" target="_blank" class="photo-pill p1" title="Lihat Foto 1">
                                                <i class="bi bi-camera-fill"></i>
                                            </a>
                                        @endif
                                        @if($event->foto_event_2)
                                            <a href="{{ asset('storage/' . $event->foto_event_2) }}" target="_blank" class="photo-pill p2" title="Lihat Foto 2">
                                                <i class="bi bi-camera-fill"></i>
                                            </a>
                                        @endif
                                        @if($event->foto_event_3)
                                            <a href="{{ asset('storage/' . $event->foto_event_3) }}" target="_blank" class="photo-pill p3" title="Lihat Foto 3">
                                                <i class="bi bi-camera-fill"></i>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="no-photo">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="{{ route('perongkosan_event_luar_kota.edit', $event->id) }}" class="btn-icon-action btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('perongkosan_event_luar_kota.destroy', $event->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus transaksi {{ $event->transaksi_id }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon-action btn-delete" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="bi bi-inbox"></i>
                                    </div>
                                    <div class="empty-title">Belum Ada Transaksi</div>
                                    <div class="empty-subtitle">Mulai catat event luar kota pertama Anda untuk melihat data di sini.</div>
                                    <a href="{{ route('perongkosan_event_luar_kota.create') }}" class="btn-primary-action">
                                        <i class="bi bi-plus-lg"></i> Tambah Event Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($events) && method_exists($events, 'links') && $events->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Menampilkan <strong>{{ $events->firstItem() }}</strong> sampai <strong>{{ $events->lastItem() }}</strong> dari <strong>{{ $events->total() }}</strong> transaksi
                    </div>
                    <div>
                        {{ $events->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>

        <div style="height: 3rem;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

~~~~

## FILE: resources/views/perongkosan_event_luar_kota/show.blade.php

SHA-256: `2ee47366c3bd43804f6a1056d9fb3e1cb3dedb3fac391f8b2802c74bbe66a6ac`

~~~~php
@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Detail Event Luar Kota')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Detail Event Luar Kota</h1>
        <p class="text-muted mb-0">{{ $event->transaksi_id ?: 'Event #'.$event->id }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('akses.cash-flow')
            <a href="{{ route('cash-flow.show', \Carbon\Carbon::parse($event->tanggal)->format('Ym')) }}" class="btn btn-outline-primary">Lihat Cash Flow</a>
        @endcan
        <a href="{{ route('perongkosan_event_luar_kota.index') }}" class="btn btn-outline-secondary">Daftar Event Luar Kota</a>
    </div>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
    <dl class="row mb-0">
        <dt class="col-sm-4">Tanggal</dt><dd class="col-sm-8">{{ \Carbon\Carbon::parse($event->tanggal)->translatedFormat('d F Y') }}</dd>
        <dt class="col-sm-4">Karyawan</dt><dd class="col-sm-8">{{ $event->user?->nama ?? '-' }}</dd>
        <dt class="col-sm-4">Lokasi Event</dt><dd class="col-sm-8">{{ $event->wilayah?->nama ?? '-' }}</dd>
        <dt class="col-sm-4">Kelompok Jam Kerja</dt><dd class="col-sm-8">{{ $event->jamKerja?->code ?? '-' }}</dd>
        <dt class="col-sm-4">Jarak</dt><dd class="col-sm-8">{{ number_format($event->jarak_km, 2, ',', '.') }} km</dd>
        <dt class="col-sm-4">Durasi</dt><dd class="col-sm-8">{{ number_format($event->durasi_jam, 2, ',', '.') }} jam</dd>
        <dt class="col-sm-4">Hari Libur</dt><dd class="col-sm-8">{{ $event->is_libur ? 'Ya' : 'Tidak' }}</dd>
        <dt class="col-sm-4">Keterangan</dt><dd class="col-sm-8 text-break" style="white-space: pre-wrap;">{{ $event->description ?? '-' }}</dd>
        <dt class="col-sm-4">Nominal</dt><dd class="col-sm-8 fw-bold">Rp {{ number_format($event->nominal, 2, ',', '.') }}</dd>
        @if($event->plafon_value !== null)
            <dt class="col-sm-4">Plafon</dt><dd class="col-sm-8">Rp {{ number_format($event->plafon_value, 2, ',', '.') }}</dd>
        @endif
        @if($event->is_over_plafon)
            <dt class="col-sm-4">Kelebihan Plafon</dt><dd class="col-sm-8 text-danger">Rp {{ number_format($event->selisih_plafon, 2, ',', '.') }}</dd>
        @endif
    </dl>
</div></div>
<div class="card border-0 shadow-sm"><div class="card-body p-4">
    <h2 class="h5 mb-3">Foto Bukti Event</h2>
    <div class="row g-3">
        @forelse(collect([$event->foto_event_1, $event->foto_event_2, $event->foto_event_3])->filter()->values() as $photo)
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ asset('storage/'.$photo) }}" target="_blank" rel="noopener" class="d-block border rounded p-2 text-center">
                    <img src="{{ asset('storage/'.$photo) }}" alt="Bukti event {{ $loop->iteration }}" class="img-fluid rounded" style="height: 180px; width: 100%; object-fit: contain;" loading="lazy">
                    <span class="d-block mt-2">Lihat foto {{ $loop->iteration }}</span>
                </a>
            </div>
        @empty
            <p class="text-muted mb-0">Tidak ada foto bukti event yang dilampirkan.</p>
        @endforelse
    </div>
</div></div>
@endsection

~~~~

## FILE: resources/views/plafon_event_dalam_kota/create.blade.php

SHA-256: `bd3c7245f64e75ed437aca973c151d5f43bb7ac9e1bce7b5e8a5c22c40ed5d62`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Plafon Event Dalam Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container-fluid px-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-geo me-2"></i>Form Tambah Master Plafon Event Dalam Kota
                </h4>
                <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Gagal Menyimpan:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('plafon-event-dalam-kota.store') }}" method="POST">
                    @csrf 

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tanggal Berlaku <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_berlaku" class="form-control" value="{{ old('tanggal_berlaku') }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Level <span class="text-danger">*</span></label>
                            <select name="level_id" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" {{ old('level_id') == $lvl->id ? 'selected' : '' }}>
                                        {{ $lvl->description }} ({{ $lvl->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Kelompok Hari Kerja <span class="text-danger">*</span></label>
                            <select name="kelompok_hari_kerja_id" class="form-select" required>
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($weekdayGroups as $wg)
                                    <option value="{{ $wg->id }}" {{ old('kelompok_hari_kerja_id') == $wg->id ? 'selected' : '' }}>
                                        {{ $wg->description ?? $wg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hari Event (ENUM)</label>
                            <select name="hari_event" class="form-select">
                                <option value="">-- Pilih Hari --</option>
                                <option value="Reguler" {{ old('hari_event') == 'Reguler' ? 'selected' : '' }}>Reguler</option>
                                <option value="Sabtu" {{ old('hari_event') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                                <option value="Libur" {{ old('hari_event') == 'Libur' ? 'selected' : '' }}>Libur</option>
                            </select>
                            <small class="text-muted">Kosongkan jika berlaku untuk semua hari</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Jam Kerja Event <span class="text-danger">*</span></label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($eventHourGroups as $ehg)
                                    <option value="{{ $ehg->id }}" {{ old('jam_kerja_event_id') == $ehg->id ? 'selected' : '' }}>
                                        {{ $ehg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Nilai Plafon (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="default_value" class="form-control" 
                                   value="{{ old('default_value') }}" placeholder="Contoh: 150000" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Deskripsi <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" 
                                   value="{{ old('description') }}" 
                                   placeholder="Contoh: Plafon Marketing Dalam Kota (Reguler)" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Simpan Plafon Event
                    </button>
                </form>

                <hr class="my-5">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-list-ul me-2"></i>Daftar Plafon Event Dalam Kota
                </h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mt-3 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>Tgl Berlaku</th>
                                <th>Level</th>
                                <th>Kelompok Hari</th>
                                <th>Hari Event</th>
                                <th>Jam Event</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Nilai Plafon</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plafons as $plafon)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($plafon->tanggal_berlaku)->format('d M Y') }}</td>
                                    <td>{{ $plafon->level->nama ?? '-' }}</td>
                                    <td>{{ $plafon->weekdayGroup->description ?? ($plafon->weekdayGroup->code ?? '-') }}</td>
                                    <td>
                                        @if($plafon->hari_event)
                                            <span class="badge 
                                                @if($plafon->hari_event == 'Reguler') bg-primary 
                                                @elseif($plafon->hari_event == 'Sabtu') bg-warning text-dark 
                                                @else bg-danger @endif">
                                                {{ $plafon->hari_event }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Semua Hari</span>
                                        @endif
                                    </td>
                                    <td>{{ $plafon->eventHourGroup->code ?? '-' }}</td>
                                    <td>{{ $plafon->description }}</td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($plafon->default_value, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('plafon-event-dalam-kota.edit', $plafon->id) }}" 
                                           class="btn btn-sm btn-primary mb-1">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('plafon-event-dalam-kota.destroy', $plafon->id) }}" 
                                              method="POST" style="display:inline;"
                                              onsubmit="return confirm('Yakin ingin menghapus plafon ini?\n\nLevel: {{ $plafon->level->description ?? '-' }}\nPlafon: Rp {{ number_format($plafon->default_value, 0, ',', '.') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Belum ada data plafon event dalam kota.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

~~~~

## FILE: resources/views/plafon_event_dalam_kota/edit.blade.php

SHA-256: `2feb9066b053558c0f99d416f4f0d7b171154f7b751eeb9a3a9dc2e71e01b0ea`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Plafon Event Dalam Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container-fluid px-5">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Master Plafon Event Dalam Kota</h4>
                <a href="{{ route('plafon-event-dalam-kota.create') }}" class="btn btn-sm btn-light fw-bold text-warning">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
            <div class="card-body">
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Gagal Memperbarui:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('plafon-event-dalam-kota.update', $plafon->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tanggal Berlaku <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_berlaku" class="form-control" 
                                   value="{{ old('tanggal_berlaku', $plafon->tanggal_berlaku) }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Level <span class="text-danger">*</span></label>
                            <select name="level_id" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" 
                                        {{ old('level_id', $plafon->level_id) == $lvl->id ? 'selected' : '' }}>
                                        {{ $lvl->description }} ({{ $lvl->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Kelompok Hari Kerja <span class="text-danger">*</span></label>
                            <select name="kelompok_hari_kerja_id" class="form-select" required>
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($weekdayGroups as $wg)
                                    <option value="{{ $wg->id }}" 
                                        {{ old('kelompok_hari_kerja_id', $plafon->kelompok_hari_kerja_id) == $wg->id ? 'selected' : '' }}>
                                        {{ $wg->description ?? $wg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hari Event (ENUM)</label>
                            <select name="hari_event" class="form-select">
                                <option value="">-- Pilih Hari --</option>
                                <option value="Reguler" {{ old('hari_event', $plafon->hari_event) == 'Reguler' ? 'selected' : '' }}>Reguler</option>
                                <option value="Sabtu" {{ old('hari_event', $plafon->hari_event) == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                                <option value="Libur" {{ old('hari_event', $plafon->hari_event) == 'Libur' ? 'selected' : '' }}>Libur</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Jam Kerja Event <span class="text-danger">*</span></label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($eventHourGroups as $ehg)
                                    <option value="{{ $ehg->id }}" 
                                        {{ old('jam_kerja_event_id', $plafon->jam_kerja_event_id) == $ehg->id ? 'selected' : '' }}>
                                        {{ $ehg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Nilai Plafon (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="default_value" class="form-control" 
                                   value="{{ old('default_value', $plafon->default_value) }}" 
                                   placeholder="Contoh: 150000" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Deskripsi <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" 
                                   value="{{ old('description', $plafon->description) }}" 
                                   placeholder="Contoh: Plafon Marketing Dalam Kota (Reguler)" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning fw-bold flex-grow-1">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('plafon-event-dalam-kota.create') }}" class="btn btn-outline-secondary fw-bold">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

~~~~

## FILE: resources/views/plafon_event_luar_kota/create.blade.php

SHA-256: `095ab121103329997c28a326caf055f451a8aa7923c9f666ba36378c34334989`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Plafon Event Luar Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container-fluid px-5">
        <div class="card shadow">
           <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Plafon Event Luar Kota</h4>
    
    <a href="{{ route(auth()->user()->dashboardRouteName()) }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('plafon-event-luar-kota.store') }}" method="POST">
                    @csrf 

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_berlaku" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Level</label>
                            <select name="level_id" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}">{{ $lvl->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kelompok Hari Kerja</label>
                            <select name="kelompok_hari_kerja_id" class="form-select" required>
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($weekdayGroups as $wg)
                                    <option value="{{ $wg->id }}">{{ $wg->description ?? $wg->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                           <label class="form-label">Hari Event <span class="text-danger">*</span></label>
                           <select name="hari_event" class="form-select" required>
    <option value="">-- Pilih Hari --</option>
    <option value="Reguler" {{ old('hari_event') == 'Reguler' ? 'selected' : '' }}>Reguler</option>
    <option value="Sabtu" {{ old('hari_event') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
    <option value="Libur" {{ old('hari_event') == 'Libur' ? 'selected' : '' }}>Libur</option>
</select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jam Kerja Event</label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($eventHourGroups as $ehg)
                                    <option value="{{ $ehg->id }}">{{ $ehg->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nilai Plafon (Rp)</label>
                            <input type="number" name="default_value" class="form-control" placeholder="Contoh: 300000" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" name="description" class="form-control" placeholder="Contoh: Plafon Luar Kota (Reguler)" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">Simpan Plafon Event Luar Kota</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Plafon Event Luar Kota</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mt-3 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>Tgl Berlaku</th>
                                <th>Level</th>
                                <th>Kelompok Hari</th>
                                <th>Hari Event</th>
                                <th>Jam Event</th>
                                <th>Deskripsi</th>
                                <th>Nilai Plafon</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plafons as $plafon)
                                <tr>
                                    <td>{{ $plafon->tanggal_berlaku }}</td>
                                    <td>{{ $plafon->level->nama ?? '-' }}</td>
                                    <td>{{ $plafon->weekdayGroup->description ?? ($plafon->weekdayGroup->code ?? '-') }}</td>
                                    <td>{{ $plafon->hari_event ?? '-' }}</td>
                                    <td>{{ $plafon->eventHourGroup->code ?? '-' }}</td>
                                    <td>{{ $plafon->description }}</td>
                                    <td>Rp {{ number_format($plafon->default_value, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('plafon-event-luar-kota.edit', $plafon->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>
                                        <form action="{{ route('plafon-event-luar-kota.destroy', $plafon->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Hapus plafon luar kota ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/plafon_event_luar_kota/edit.blade.php

SHA-256: `c8a48bbfd5a5712f802e4fa2c4ae926101c1129183368ea758957ac75ebd748a`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Plafon Event Luar Kota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container-fluid px-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Form Edit Setting Plafon Event (Luar Kota)</h4>
            </div>
            <div class="card-body">
                
                <form action="{{ route('plafon-event-luar-kota.update', $plafon->id) }}" method="POST">
                    @csrf 
                    @method('PUT') 

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_berlaku" class="form-control" value="{{ old('tanggal_berlaku', $plafon->tanggal_berlaku) }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Level</label>
                            <select name="level_id" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" {{ old('level_id', $plafon->level_id) == $lvl->id ? 'selected' : '' }}>
                                        {{ $lvl->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kelompok Hari Kerja</label>
                            <select name="kelompok_hari_kerja_id" class="form-select" required>
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($weekdayGroups as $wg)
                                    <option value="{{ $wg->id }}" {{ old('kelompok_hari_kerja_id', $plafon->kelompok_hari_kerja_id) == $wg->id ? 'selected' : '' }}>
                                        {{ $wg->description ?? $wg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hari Event (ENUM)</label>
                            <select name="hari_event" class="form-select" required>
    <option value="">-- Pilih Hari --</option>
    <option value="Reguler" {{ old('hari_event', $plafon->hari_event) == 'Reguler' ? 'selected' : '' }}>Reguler</option>
    <option value="Sabtu" {{ old('hari_event', $plafon->hari_event) == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
    <option value="Libur" {{ old('hari_event', $plafon->hari_event) == 'Libur' ? 'selected' : '' }}>Libur</option>
</select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jam Kerja Event</label>
                            <select name="jam_kerja_event_id" class="form-select" required>
                                <option value="">-- Pilih Jam Kerja --</option>
                                @foreach($eventHourGroups as $ehg)
                                    <option value="{{ $ehg->id }}" {{ old('jam_kerja_event_id', $plafon->jam_kerja_event_id) == $ehg->id ? 'selected' : '' }}>
                                        {{ $ehg->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nilai Plafon (Rp)</label>
                            <input type="number" name="default_value" class="form-control" value="{{ old('default_value', $plafon->default_value) }}" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', $plafon->description) }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('plafon-event-luar-kota.create') }}" class="btn btn-secondary">Batal / Kembali</a>
                        <button type="submit" class="btn btn-primary">Update Plafon Event Luar Kota</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/profile/index.blade.php

SHA-256: `965b3ef8ac841038f685ae159a39af2faf77c0d64ef7e26237a30fef636de33d`

~~~~php
@extends('layouts.app-with-sidebar', ['panel' => 'profile'])

@section('title', 'Profil Saya - EMMO App')

@push('styles')
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --accent-primary: #3b82f6;
            --accent-success: #10b981;
            --accent-purple: #8b5cf6;
            --accent-warning: #f59e0b;
            --accent-pink: #ec4899;
            --accent-indigo: #6366f1;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.04) 0px, transparent 50%);
        }

        /* Hero Profile Card */
        .profile-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: 20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .profile-hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .avatar {
            width: 88px;
            height: 88px;
            border-radius: 24px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            flex-shrink: 0;
            backdrop-filter: blur(10px);
        }

        .profile-hero-info h2 {
            font-weight: 800;
            font-size: 1.75rem;
            margin: 0 0 0.25rem 0;
            letter-spacing: -0.02em;
        }

        .profile-hero-info .username {
            opacity: 0.85;
            font-size: 0.9375rem;
            font-family: 'SF Mono', Monaco, monospace;
            margin-bottom: 0.75rem;
        }

        .profile-chips {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .chip {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Section Card */
        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            box-shadow: 0 8px 24px -8px rgba(0,0,0,0.06);
        }

        .info-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #fafbfc;
        }

        .info-card-header .icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .info-card-header .icon-box.bg-blue { background: #dbeafe; color: #2563eb; }
        .info-card-header .icon-box.bg-purple { background: #ede9fe; color: #7c3aed; }
        .info-card-header .icon-box.bg-green { background: #dcfce7; color: #16a34a; }
        .info-card-header .icon-box.bg-orange { background: #fed7aa; color: #ea580c; }

        .info-card-header h3 {
            font-size: 0.9375rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .info-card-header p {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .info-card-body {
            padding: 1.5rem;
        }

        /* Info Rows */
        .info-row {
            display: flex;
            align-items: flex-start;
            padding: 0.875rem 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .info-row:last-child { border-bottom: none; }

        .info-row .label {
            flex: 0 0 140px;
            font-size: 0.8125rem;
            color: var(--text-secondary);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-row .label i {
            font-size: 0.875rem;
            color: var(--text-secondary);
            opacity: 0.6;
        }

        .info-row .value {
            flex: 1;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .info-row .value.empty {
            color: var(--text-secondary);
            font-style: italic;
            font-weight: 400;
        }

        /* Badges */
        .badge-role {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .badge-active {
            background: #dcfce7;
            color: #16a34a;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Tunjangan Grid */
        .tunjangan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }

        .tunjangan-item {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s ease;
        }

        .tunjangan-item:hover {
            background: white;
            border-color: var(--accent-primary);
            transform: translateY(-2px);
        }

        .tunjangan-item .icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 1.125rem;
        }

        .tunjangan-item .label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .tunjangan-item .value {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .tunjangan-item .value.rupiah {
            color: var(--accent-success);
        }

        .tunjangan-item .value.empty {
            color: var(--text-secondary);
            font-weight: 400;
            font-style: italic;
            font-size: 0.8125rem;
        }

        /* Atasan Card */
        .atasan-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #fcd34d;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .atasan-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: white;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .atasan-card .info .label {
            font-size: 0.6875rem;
            color: #92400e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.125rem;
        }

        .atasan-card .info .name {
            font-size: 1rem;
            font-weight: 700;
            color: #78350f;
            margin: 0;
        }

        .atasan-empty {
            background: #f8fafc;
            border: 1px dashed var(--border-color);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Daftar bawahan */
        .profile-subordinates { scroll-margin-top: 5rem; }
        .profile-subordinates .info-card-header { flex-wrap: wrap; }
        .profile-subordinates__heading { flex: 1; min-width: 150px; }
        .profile-subordinates__count {
            padding: .35rem .7rem;
            border-radius: 8px;
            background: #ede9fe;
            color: #6d28d9;
            font-size: .75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .profile-subordinates__scroll { overflow-x: auto; }
        .profile-subordinates__table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
        .profile-subordinates__table th {
            padding: .85rem 1.25rem;
            background: #f8fafc;
            color: var(--text-secondary);
            font-size: .72rem;
            font-weight: 600;
            text-align: left;
        }
        .profile-subordinates__table td { padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); }
        .profile-subordinates__table td:first-child { font-weight: 600; }
        .profile-subordinates__table td:last-child { white-space: nowrap; }
        .profile-subordinates__table td span { overflow-wrap: anywhere; }
        .profile-subordinates__table tbody tr:hover { background: #fafbfc; }
        .profile-subordinates__link { display: inline-block; margin-top: .5rem; font-size: .75rem; font-weight: 600; }
        .profile-subordinates__empty { margin: 0; padding: 2rem 1.5rem; text-align: center; color: var(--text-secondary); font-size: .875rem; }
        @media (max-width: 767.98px) {
            .profile-subordinates__table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
            .profile-subordinates__table tbody, .profile-subordinates__table tr { display: block; }
            .profile-subordinates__table tr { padding: .75rem 1.25rem; border-top: 1px solid var(--border-color); }
            .profile-subordinates__table tr:first-child { border-top: 0; }
            .profile-subordinates__table td { display: flex; justify-content: space-between; gap: 1rem; padding: .4rem 0; border: 0; }
            .profile-subordinates__table td::before { content: attr(data-label); flex: 0 0 70px; color: var(--text-secondary); font-weight: 500; }
            .profile-subordinates__table td span { min-width: 0; text-align: right; }
        }

        /* Footer Action */
        .footer-action {
            text-align: center;
            padding: 1rem 0 2rem;
        }

        .btn-back {
            background: var(--text-primary);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9375rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #334155;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 575.98px) {
            .profile-hero { padding: 1.75rem; }
            .profile-hero-content { flex-direction: column; text-align: center; }
            .profile-chips { justify-content: center; }
            .info-row { flex-direction: column; gap: 0.25rem; }
            .info-row .label { flex: none; }
            .avatar { width: 72px; height: 72px; font-size: 1.75rem; }
        }
    </style>
@endpush

@section('content')
    @php
        $breadcrumbs = [
            (auth()->user()->canAccessPortal() ? 'Portal' : 'Transaksi') => route(auth()->user()->dashboardRouteName()),
            'Profil Saya' => route('profile.index'),
        ];
    @endphp

        {{-- ========================================= --}}
        {{-- HERO PROFILE CARD                         --}}
        {{-- ========================================= --}}
        @php
            $name = $user->nama ?? 'User';
            $parts = explode(' ', $name);
            $initials = mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1);
            $initials = strtoupper($initials);
            $isActive = (bool) $user->is_active;
        @endphp

        <div class="profile-hero">
            <div class="profile-hero-content">
                <div class="avatar">{{ $initials }}</div>
                <div class="profile-hero-info">
                    <h2>{{ $user->nama ?? 'Pengguna' }}</h2>
                    <div class="username">@ {{ $user->username ?? 'username' }}</div>
                    <div class="profile-chips">
                        <span class="chip">
                            <i class="bi bi-shield-check"></i>
                            {{ $user->role->nama ?? 'User' }}
                        </span>
                        @if($user->company)
                            <span class="chip">
                                <i class="bi bi-building"></i>
                                {{ $user->company->description ?? $user->company->code ?? '-' }}
                            </span>
                        @endif
                        @if($isActive)
                            <span class="chip" style="background: rgba(74,222,128,0.3);">
                                <i class="bi bi-check-circle-fill"></i>
                                Akun Aktif
                            </span>
                        @else
                            <span class="chip" style="background: rgba(239,68,68,0.3);">
                                <i class="bi bi-x-circle-fill"></i>
                                Nonaktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            {{-- ========================================= --}}
            {{-- KOLOM KIRI: Data Pribadi & Organisasi     --}}
            {{-- ========================================= --}}
            <div class="col-lg-7">
                
                {{-- Data Pribadi --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-blue">
                            <i class="bi bi-person-vcard"></i>
                        </div>
                        <div>
                            <h3>Data Pribadi</h3>
                            <p>Informasi identitas karyawan</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-credit-card-2-front"></i> NIK
                            </div>
                            <div class="value {{ !$user->NIK ? 'empty' : '' }}">
                                {{ $user->NIK ?? 'Belum diisi' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-person-fill"></i> Nama Lengkap
                            </div>
                            <div class="value">{{ $user->nama ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-at"></i> Username
                            </div>
                            <div class="value" style="font-family: 'SF Mono', Monaco, monospace;">
                                {{ $user->username ?? '-' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-toggle-on"></i> Status Akun
                            </div>
                            <div class="value">
                                @if($isActive)
                                    <span class="badge-active">Aktif</span>
                                @else
                                    <span class="badge-inactive">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Organisasi --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-purple">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <div>
                            <h3>Data Organisasi</h3>
                            <p>Posisi dalam struktur perusahaan</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-building"></i> Perusahaan
                            </div>
                            <div class="value {{ !$user->company ? 'empty' : '' }}">
                                {{ $user->company->description ?? $user->company->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-shield-check"></i> Hak Akses
                            </div>
                            <div class="value">
                                <span class="badge-role">{{ $user->role->nama ?? 'User Biasa' }}</span>
                                @if($user->role)
                                    <span class="text-secondary" style="font-size: 0.75rem; margin-left: 0.5rem;">
                                        ({{ $user->role->code }})
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-bar-chart-steps"></i> Level
                            </div>
                            <div class="value {{ !$user->level ? 'empty' : '' }}">
                                {{ $user->level->description ?? $user->level->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-briefcase"></i> Posisi
                            </div>
                            <div class="value {{ !$user->position ? 'empty' : '' }}">
                                {{ $user->position->description ?? $user->position->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-geo-alt"></i> Homebase
                            </div>
                            <div class="value {{ !$user->homebase ? 'empty' : '' }}">
                                {{ $user->homebase->nama ?? $user->homebase->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="label">
                                <i class="bi bi-calendar-week"></i> Hari Kerja
                            </div>
                            <div class="value {{ !$user->weekdayGroup ? 'empty' : '' }}">
                                {{ $user->weekdayGroup->description ?? $user->weekdayGroup->code ?? 'Belum diatur' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================= --}}
            {{-- KOLOM KANAN: Atasan & Tunjangan           --}}
            {{-- ========================================= --}}
            <div class="col-lg-5">

                {{-- Atasan --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-orange">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div>
                            <h3>Atasan Langsung</h3>
                            <p>Penanggung jawab approval Anda</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        @if($user->atasan)
                            <div class="atasan-card">
                                <div class="icon">
                                    <i class="bi bi-person-check-fill"></i>
                                </div>
                                <div class="info">
                                    <div class="label">Atasan</div>
                                    <p class="name">{{ $user->atasan->nama }}</p>
                                    @if($user->atasan->position)
                                        <div style="font-size: 0.75rem; color: #92400e; margin-top: 0.125rem;">
                                            {{ $user->atasan->position->description ?? $user->atasan->position->code ?? '' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="atasan-empty">
                                <i class="bi bi-info-circle me-1"></i>
                                Belum ada atasan yang diatur
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tunjangan --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-green">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h3>Tunjangan Karyawan</h3>
                            <p>Komponen tunjangan yang Anda terima</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        <div class="tunjangan-grid">
                            <div class="tunjangan-item">
                                <div class="icon">
                                    <i class="bi bi-house-door"></i>
                                </div>
                                <div class="label">Tunjangan Kost</div>
                                @if($user->kost)
                                    <div class="value rupiah">
                                        Rp {{ number_format($user->kost, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div class="value empty">Tidak ada</div>
                                @endif
                            </div>
                            <div class="tunjangan-item">
                                <div class="icon" style="background: #fed7aa; color: #ea580c;">
                                    <i class="bi bi-fuel-pump"></i>
                                </div>
                                <div class="label">Tunjangan BBM</div>
                                @if($user->bbm)
                                    <div class="value rupiah">
                                        Rp {{ number_format($user->bbm, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div class="value empty">Tidak ada</div>
                                @endif
                            </div>
                            <div class="tunjangan-item">
                                <div class="icon" style="background: #fef3c7; color: #ca8a04;">
                                    <i class="bi bi-egg-fried"></i>
                                </div>
                                <div class="label">Uang Makan</div>
                                @if($user->uang_makan)
                                    <div class="value rupiah">
                                        Rp {{ number_format($user->uang_makan, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div class="value empty">Tidak ada</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="icon-box bg-blue">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <h3>Aktivitas Saya</h3>
                            <p>Ringkasan aktivitas Anda</p>
                        </div>
                    </div>
                    <div class="info-card-body">
                        <div class="tunjangan-grid">
                            <div class="tunjangan-item">
                                <div class="icon">
                                    <i class="bi bi-file-earmark-plus"></i>
                                </div>
                                <div class="label">Pengajuan BS</div>
                                <div class="value">
                                    {{ $user->pengajuanBs()->count() }} data
                                </div>
                            </div>
                            <div class="tunjangan-item">
                                <div class="icon" style="background: #ede9fe; color: #7c3aed;">
                                    <i class="bi bi-person-check"></i>
                                </div>
                                <div class="label">Bawahan</div>
                                <div class="value">
                                    {{ $bawahan->count() }} orang
                                </div>
                                <a href="#daftar-bawahan" class="profile-subordinates__link">Lihat daftar bawahan <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <section class="info-card profile-subordinates" id="daftar-bawahan" aria-labelledby="bawahan-title">
            <div class="info-card-header">
                <div class="icon-box bg-purple"><i class="bi bi-people" aria-hidden="true"></i></div>
                <div class="profile-subordinates__heading">
                    <h3 id="bawahan-title">Bawahan Saya</h3>
                    <p>Pengguna yang terdaftar dengan Anda sebagai atasan langsung.</p>
                </div>
                <span class="profile-subordinates__count">{{ $bawahan->count() }} orang</span>
            </div>
            @if($bawahan->isEmpty())
                <p class="profile-subordinates__empty">Belum ada pengguna yang terdaftar sebagai bawahan Anda.</p>
            @else
                <div class="profile-subordinates__scroll">
                    <table class="profile-subordinates__table" aria-labelledby="bawahan-title">
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">NIK</th>
                                <th scope="col">Posisi</th>
                                <th scope="col">Homebase</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bawahan as $anggota)
                                <tr>
                                    <td data-label="Nama"><span>{{ $anggota->nama }}</span></td>
                                    <td data-label="NIK"><span>{{ $anggota->NIK ?: '-' }}</span></td>
                                    <td data-label="Posisi"><span>{{ $anggota->position->nama ?? $anggota->position->description ?? $anggota->position->code ?? 'Belum diatur' }}</span></td>
                                    <td data-label="Homebase"><span>{{ $anggota->homebase->nama ?? $anggota->homebase->code ?? 'Belum diatur' }}</span></td>
                                    <td data-label="Status"><span class="{{ $anggota->isActive() ? 'badge-active' : 'badge-inactive' }}">{{ $anggota->isActive() ? 'Aktif' : 'Nonaktif' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

@endsection

~~~~

## FILE: resources/views/roles/create.blade.php

SHA-256: `d754e7f8dcaf90c150813146cb110ad2c6234fe85ff0a79a3fd3bb98bcc24059`

~~~~php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Master Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 800px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Form Tambah Master Role</h4>
    
    <a href="{{ route('dashboard.master_data') }}" class="btn btn-sm btn-light fw-bold text-primary">
        ← Kembali ke Dashboard
    </a>
</div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf 

                    <div class="mb-3">
                        <label for="code" class="form-label">Role Code</label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: ADMIN, STAFF, SUPER_USER">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <textarea name="nama" id="nama" rows="3" class="form-control @error('nama') is-invalid @enderror" placeholder="Keterangan mengenai role ini...">{{ old('nama') }}</textarea>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                        <label class="form-check-label" for="is_active">Set Aktif (Is Active)</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan Master Role</button>
                </form>

                <hr class="my-5">

                <h5>Daftar Master Role</h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th style="min-width: 120px;">Aksi</th> 
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>{{ $role->code }}</td>
                                    <td>{{ $role->nama }}</td>
                                    <td>
                                        <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $role->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-primary mb-1">Edit</a>
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/roles/edit.blade.php

SHA-256: `43014998a16f1174b9cf0a0cb6846e895fb7662dd9ca8147edb99452f23c8347`

~~~~php
<h2>Form Edit Master Role</h2>

<form action="{{ route('roles.update', $role->id) }}" method="POST">
    @csrf
    
    @method('PUT') 

    <div>
        <label>Role Code:</label><br>
        <input type="text" name="code" value="{{ $role->code }}" readonly>
    </div>

    <div>
        <label>nama:</label><br>
        <textarea name="nama" required>{{ $role->nama }}</textarea>
    </div>

    <div>
        <label>
            <input type="checkbox" name="is_active" value="1" {{ $role->is_active ? 'checked' : '' }}>
            Set Aktif (Is Active)
        </label>
    </div>

    <button type="submit">Update Master Role</button>
</form>
~~~~
