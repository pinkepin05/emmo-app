<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
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
    @include('partials.mobile-assets')
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
