# Kode existing: tampilan-transaksi-dan-finance, bagian 2

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: resources/views/transaksi_perjalanan_dinas/create.blade.php

SHA-256: `ad59b25922adf5fb74c4a55bdeba07ec773acf1f3ef1ddb4aa9a009c225827a8`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Perjalanan Dinas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
:root {
--primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
--success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
--danger-gradient: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
--info-gradient: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
--primary-color: #667eea;
--success-color: #11998e;
--info-color: #0099e5;
--bg-color: #f0f4f8;
}
body {
font-family: 'Plus Jakarta Sans', sans-serif;
background: var(--bg-color);
background-image: radial-gradient(#667eea 0.5px, transparent 0.5px), radial-gradient(#667eea 0.5px, var(--bg-color) 0.5px);
background-size: 20px 20px;
background-position: 0 0, 10px 10px;
min-height: 100vh;
}
.text-success { color: #11998e !important; }
.text-danger  { color: #e11d48 !important; }
.text-primary { color: #667eea !important; }
.text-info    { color: #0099e5 !important; }
.text-muted   { color: #718096 !important; }
.bg-success   { background-color: #11998e !important; }
.bg-danger    { background-color: #e11d48 !important; }
.bg-warning   { background-color: #f59e0b !important; }
.page-header { background: var(--primary-gradient); color: white; padding: 2rem 0; margin-bottom: 2rem; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3); }
.page-header h1 { font-weight: 800; margin: 0; font-size: 2rem; }
.page-header p  { margin: 0.5rem 0 0 0; opacity: 0.9; }
.card-modern { background: white; border: none; border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); overflow: hidden; margin-bottom: 2rem; }
.card-header-modern { background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-bottom: 2px solid rgba(102, 126, 234, 0.1); padding: 1.5rem 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
.card-header-modern.success { background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%); border-bottom-color: rgba(17, 153, 142, 0.2); }
.card-header-modern.info { background: linear-gradient(135deg, rgba(0, 198, 251, 0.1) 0%, rgba(0, 91, 234, 0.1) 100%); border-bottom-color: rgba(0, 153, 229, 0.2); }
.card-header-modern h5 { margin: 0; font-weight: 700; color: #2d3748; display: flex; align-items: center; gap: 0.75rem; }
.card-header-modern h5 i { font-size: 1.5rem; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.card-header-modern.success h5 i { background: var(--success-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.card-header-modern.info h5 i { background: var(--info-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.form-label { font-weight: 600; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.9rem; display: flex; align-items: center; flex-wrap: wrap; gap: 0.4rem; }
.form-control, .form-select { border: 2px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.95rem; transition: border-color 0.3s ease, box-shadow 0.3s ease; }
.form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1); }
.form-control:disabled, .form-select:disabled { background: #f7fafc; border-color: #e2e8f0; opacity: 0.8; }
textarea.form-control { border-radius: 15px; }
.input-group-text { background: white; border: 2px solid #e2e8f0; color: var(--primary-color); font-weight: 600; }
.input-with-icon .input-group-text { border-right: none; border-radius: 12px 0 0 12px; }
.input-with-icon .form-control     { border-radius: 0 12px 12px 0; }
.input-suffix .input-group-text    { border-left: none; border-radius: 0 12px 12px 0; background: #f8fafc; color: #4a5568; }
.input-suffix .form-control        { border-radius: 12px 0 0 12px; }
.helper-text { font-size: 0.78rem; color: #718096; margin-top: 0.4rem; display: block; }
.rule-badge { font-size: 0.7rem; font-weight: 700; padding: 0.35rem 0.7rem; border-radius: 20px; letter-spacing: 0.3px; }
.rule-badge.lumpsum { background: rgba(0, 198, 251, 0.12); color: #0077b6; border: 1px solid rgba(0, 153, 229, 0.35); }
.rule-badge.hotel   { background: rgba(245, 158, 11, 0.12); color: #92400e; border: 1px solid rgba(245, 158, 11, 0.35); }
.status-info { min-height: 1.3rem; margin-top: 0.4rem; font-size: 0.82rem; font-weight: 600; }
.btn-modern { border-radius: 12px; padding: 0.75rem 1.5rem; font-weight: 600; border: none; display: inline-flex; align-items: center; gap: 0.5rem; }
.btn-primary-modern { background: var(--primary-gradient); color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); border-radius: 15px; font-weight: 700; letter-spacing: 0.3px; }
.btn-primary-modern:hover, .btn-primary-modern:focus, .btn-primary-modern:active { background: var(--primary-gradient); color: white; transform: none; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
.btn-success-modern { background: var(--success-gradient); color: white; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4); transition: all 0.3s ease; }
.btn-success-modern:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(17, 153, 142, 0.5); color: white; }
.btn-danger-modern { background: var(--danger-gradient); color: white; box-shadow: 0 4px 15px rgba(238, 9, 121, 0.4); transition: all 0.3s ease; }
.btn-danger-modern:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(238, 9, 121, 0.5); color: white; }
.btn-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
.preview-box { border: none; border-radius: 15px; padding: 1rem 1.5rem; font-weight: 500; display: flex; align-items: center; gap: 0.6rem; }
.preview-box.alert-info    { background: linear-gradient(135deg, rgba(0,198,251,.12), rgba(0,91,234,.06));  color: #0077b6; border-left: 4px solid #0099e5; }
.preview-box.alert-success { background: linear-gradient(135deg, rgba(17,153,142,.12), rgba(56,239,125,.06)); color: #0f766e; border-left: 4px solid #11998e; }
.preview-box.alert-warning { background: linear-gradient(135deg, rgba(245,158,11,.12), rgba(245,158,11,.05)); color: #92400e; border-left: 4px solid #f59e0b; }
.stat-box { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 15px; padding: 1.25rem; text-align: center; }
.stat-box small { color: #718096; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.72rem; }
.stat-box .value { font-size: 1.4rem; font-weight: 800; margin-top: 0.25rem; display: block; }
#info-plafon-total   { color: #667eea !important; }
#info-total-input    { color: #11998e !important; }
#info-sisa-anggaran  { color: #0099e5 !important; }
#info-sisa-anggaran.text-danger { color: #e11d48 !important; }
#plafon-status-message .badge { padding: 0.6rem 1.25rem; border-radius: 30px; font-weight: 700; font-size: 0.85rem; }
.table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }
.table-modern thead th { background: linear-gradient(135deg, rgba(17, 153, 142, 0.06) 0%, rgba(56, 239, 125, 0.06) 100%); border-bottom: 2px solid #e2e8f0; padding: 1rem; font-weight: 700; color: #2d3748; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
.table-modern tbody td { padding: 1rem; border-bottom: 1px solid #f0f4f8; vertical-align: middle; }
.table-modern tbody tr:hover { background: rgba(17, 153, 142, 0.02); }
.total-badge { background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%); border: 2px solid var(--success-color); border-radius: 15px; padding: 0.75rem 1.5rem; text-align: right; }
.total-badge .amount { font-size: 1.6rem; font-weight: 800; line-height: 1.2; }
#total-rincian.text-success { color: #11998e !important; }
#total-rincian.text-danger  { color: #e11d48 !important; }
.info-box { background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%); border-left: 4px solid var(--primary-color); border-radius: 12px; padding: 1rem 1.5rem; margin-top: 0.75rem; }
.alert-custom { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); padding: 1rem 1.5rem; }
#plafon-over-warning { border-radius: 15px; border: none; border-left: 5px solid #f59e0b; background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(245, 158, 11, 0.05)); color: #92400e; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); }
.badge-category { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
.section-divider { height: 2px; background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.2), transparent); margin: 1.5rem 0; }
@keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to   { opacity: 1; transform: translateY(0); } }
.animate-slide-in { animation: slideIn 0.5s ease; }
/* 🎯 Tambahan: Input nominal format Rupiah */
.nominal-formatted { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; letter-spacing: 0.5px; }
@media (max-width: 768px) {
.page-header h1 { font-size: 1.5rem; }
.card-header-modern { flex-direction: column; text-align: center; }
.total-badge { text-align: center; }
}
</style>
</head>
<body>
    @include('partials.application-time')
<div class="page-header">
<div class="container">
<div class="row align-items-center">
<div class="col-md-8">
<h1><i class="bi bi-airplane me-3"></i>Perjalanan Dinas</h1>
<p class="mb-0">Input transaksi perjalanan dinas dengan validasi plafon real-time</p>
</div>
<div class="col-md-4 text-md-end mt-3 mt-md-0">
<a href="{{ route('dashboard.transaksi') }}" class="btn btn-light btn-modern">
<i class="bi bi-arrow-left"></i> Kembali
</a>
</div>
</div>
</div>
</div>
<div class="container" style="max-width: 1100px; margin-bottom: 3rem;">
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
<strong>Gagal Menyimpan:</strong>
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
@if(!auth()->user()->hasActiveCompany())
<div class="alert alert-warning alert-custom mb-4">
<i class="bi bi-exclamation-triangle-fill me-2"></i>
Company Anda (<strong>{{ auth()->user()->company->code ?? '-' }}</strong>) sedang <strong>tidak aktif</strong>.
Form tetap dapat diisi, namun transaksi akan <strong>ditolak</strong> saat disimpan.
</div>
@endif
<form action="{{ route('transaksi_perjalanan_dinas.store') }}" method="POST" id="form-perjalanan-dinas">
@csrf
<input type="hidden" name="tujuan_dinas_id" id="tujuan_dinas_id" value="{{ old('tujuan_dinas_id') }}" required>
<div class="card-modern animate-slide-in">
<div class="card-header-modern">
<h5><i class="bi bi-signpost-2"></i> Header Perjalanan Dinas</h5>
<span class="badge bg-primary bg-opacity-10 text-primary">Step 1 dari 2</span>
</div>
<div class="card-body p-4">
<div class="row g-4">
<div class="col-md-3">
<label class="form-label"><i class="bi bi-calendar-event text-primary"></i>Tanggal Dinas <span class="text-danger">*</span></label>
<div class="input-group input-with-icon">
<span class="input-group-text"><i class="bi bi-calendar"></i></span>
<input type="text" id="tanggal_picker" name="tanggal" class="form-control" value="{{ old('tanggal', \App\Support\AppTime::now()->toDateString()) }}" placeholder="Pilih tanggal..." required autocomplete="off">
</div>
<div id="periodWarning" class="alert alert-danger d-none mt-2" style="border-radius:12px;">
<i class="bi bi-lock-fill me-2"></i><span id="periodWarningText"></span>
</div>
</div>
<div class="col-md-4">
<label class="form-label"><i class="bi bi-person-badge text-primary"></i>User (Karyawan)</label>
<div class="input-group input-with-icon">
<span class="input-group-text"><i class="bi bi-lock"></i></span>
<input type="text" class="form-control bg-light" value="{{ Auth::user()->nama ?? Auth::user()->name }} (NIK: {{ Auth::user()->NIK ?? '-' }})" readonly disabled>
</div>
<small class="helper-text"><i class="bi bi-info-circle me-1"></i>Transaksi otomatis dicatat atas nama akun Anda</small>
</div>
<div class="col-md-5">
<label class="form-label"><i class="bi bi-map text-primary"></i>Territory <span class="text-danger">*</span></label>
<select name="teritory" id="teritory-select" class="form-select" required>
<option value="">-- Pilih Territory --</option>
<option value="Jawa" {{ old('teritory') == 'Jawa' ? 'selected' : '' }}>Jawa (Pulau Jawa)</option>
<option value="Luar Jawa" {{ old('teritory') == 'Luar Jawa' ? 'selected' : '' }}>Luar Jawa</option>
</select>
</div>
</div>
<div class="row g-4 mt-1">
<div class="col-md-6">
<label class="form-label"><i class="bi bi-geo text-primary"></i>Provinsi Tujuan <span class="text-danger">*</span></label>
<select id="provinsi-select" class="form-select" required disabled>
<option value="">-- Pilih Territory Terlebih Dahulu --</option>
</select>
<small class="helper-text">*Provinsi akan memfilter pilihan Kota/Kabupaten</small>
</div>
<div class="col-md-6">
<label class="form-label"><i class="bi bi-building text-primary"></i>Kota/Kabupaten Tujuan <span class="text-danger">*</span></label>
<select id="kota-select" class="form-select" required disabled>
<option value="">-- Pilih Provinsi Terlebih Dahulu --</option>
</select>
<small class="helper-text">*Pilih Kota/Kabupaten spesifik sebagai tujuan dinas</small>
</div>
</div>
<div class="mt-4">
<label class="form-label"><i class="bi bi-geo-alt-fill text-primary"></i>Tujuan Dinas Terpilih</label>
<div class="alert alert-info mb-0 preview-box" id="preview-tujuan">
<i class="bi bi-geo-alt fs-5"></i>
<span id="preview-text">Belum ada wilayah yang dipilih.</span>
</div>
</div>
<div class="section-divider"></div>
<div class="row g-4">
<div class="col-md-6">
<label class="form-label"><i class="bi bi-signpost-split text-primary"></i>Jarak Perjalanan (KM) <span class="rule-badge lumpsum">&gt;200 KM = Dapat Lumpsum</span></label>
<div class="input-group input-suffix">
<input type="number" name="jarak_km" id="jarak_km" class="form-control" placeholder="0" value="{{ old('jarak_km', 0) }}" min="0" step="0.1" required>
<span class="input-group-text">KM</span>
</div>
<div id="lumpsum-info" class="status-info"></div>
</div>
<div class="col-md-6">
<label class="form-label"><i class="bi bi-clock-history text-primary"></i>Durasi Perjalanan (Jam) <span class="rule-badge hotel">&gt;8 Jam = Dapat Hotel</span></label>
<div class="input-group input-suffix">
<input type="number" name="durasi_jam" id="durasi_jam" class="form-control" placeholder="Contoh: 12.5" value="{{ old('durasi_jam', 0) }}" min="0" max="24" step="0.5" required>
<span class="input-group-text">Jam</span>
</div>
<div id="hotel-info" class="status-info"></div>
</div>
</div>
<div class="mt-3">
<label class="form-label"><i class="bi bi-file-text text-primary"></i>Deskripsi Perjalanan / Tugas <span class="text-danger">*</span></label>
<textarea name="description" class="form-control" rows="3" placeholder="Contoh: Kunjungan Cabang Surabaya dan Audit" required>{{ old('description') }}</textarea>
</div>
<div class="section-divider"></div>
<div>
    @include('partials.transaction-funding', ['fundingType' => 'perjalanan_dinas'])
    <div id="bs-warning-info" class="alert alert-warning mt-3 d-none">Total rincian biaya melebihi saldo BS gabungan yang tersedia.</div>
</div>
</div>
</div>
<div class="card-modern animate-slide-in" id="card-info-plafon" style="display:none;">
<div class="card-header-modern info">
<h5><i class="bi bi-bar-chart-line"></i> Informasi Plafon Perjalanan Dinas</h5>
<span class="badge bg-info bg-opacity-10 text-info">Real-time</span>
</div>
<div class="card-body p-4">
<div class="row g-3 text-center">
<div class="col-md-4">
<div class="stat-box">
<small class="d-block">Plafon Total</small>
<span id="info-plafon-total" class="value">Rp 0</span>
</div>
</div>
<div class="col-md-4">
<div class="stat-box">
<small class="d-block">Total Input</small>
<span id="info-total-input" class="value">Rp 0</span>
</div>
</div>
<div class="col-md-4">
<div class="stat-box">
<small class="d-block">Sisa Anggaran</small>
<span id="info-sisa-anggaran" class="value">Rp 0</span>
</div>
</div>
</div>
<div id="plafon-status-message" class="mt-3 text-center"></div>
</div>
</div>
<div class="card-modern animate-slide-in" style="animation-delay: 0.1s">
<div class="card-header-modern success">
<h5><i class="bi bi-receipt-cutoff"></i> Rincian Biaya (Detail)</h5>
<div class="d-flex align-items-center gap-3 flex-wrap">
<div class="total-badge mb-0">
<small class="text-muted d-block">Total Rincian</small>
<span id="total-rincian" class="amount text-success">Rp 0</span>
</div>
<button type="button" class="btn btn-success-modern btn-sm" id="btn-tambah-baris">
<i class="bi bi-plus-lg"></i> Tambah Baris
</button>
</div>
</div>
<div class="card-body p-0">
<div class="table-responsive">
<table class="table-modern" id="tabel-detail">
<thead>
<tr>
<th width="30%"><i class="bi bi-tag me-2"></i>Cost Category <span class="text-danger">*</span></th>
<th width="40%"><i class="bi bi-chat-left-text me-2"></i>Remark (Keterangan)</th>
<th width="20%"><i class="bi bi-currency-dollar me-2"></i>Nominal (Rp) <span class="text-danger">*</span></th>
<th width="10%" class="text-center">Aksi</th>
</tr>
</thead>
<tbody>
@php
$oldDetails = array_values(old('details', [[]]));
@endphp
@foreach($oldDetails as $idx => $oldDetail)
<tr id="baris-{{ $idx }}" class="animate-slide-in">
<td>
<select name="details[{{ $idx }}][cost_category_id]" class="form-select cost-category-select" required>
<option value="">-- Pilih Cost Category --</option>
@foreach($cost_categories as $cc)
@php
$categoryName = strtolower($cc->name ?? $cc->description ?? $cc->code ?? '');
$type = 'umum';
if (str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku')) $type = 'lumpsum';
elseif (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi') || str_contains($categoryName, 'menginap')) $type = 'hotel';
$oldCatId = $oldDetail['cost_category_id'] ?? '';
@endphp
<option value="{{ $cc->id }}" data-type="{{ $type }}" {{ $oldCatId == $cc->id ? 'selected' : '' }}>
{{ $cc->name ?? $cc->description ?? $cc->code }}
</option>
@endforeach
</select>
</td>
<td>
<input type="text" name="details[{{ $idx }}][remark]" class="form-control" placeholder="Opsional (Misal: Tiket Kereta)" value="{{ $oldDetail['remark'] ?? '' }}">
</td>
<td>
@php $oldNominal = $oldDetail['nominal'] ?? ''; @endphp
<input type="text" class="form-control nominal-input nominal-formatted" placeholder="0" inputmode="decimal" autocomplete="off" value="{{ $oldNominal !== '' ? number_format((int)$oldNominal, 0, ',', '.') : '' }}">
<input type="hidden" name="details[{{ $idx }}][nominal]" class="nominal-real" value="{{ $oldNominal }}">
<div id="cost-info-{{ $idx }}" class="mt-1 small"></div>
</td>
<td class="text-center align-middle">
<button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus" data-id="{{ $idx }}"><i class="bi bi-trash"></i></button>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
</div>
<div id="plafon-over-warning" class="alert alert-warning d-none mb-4 p-4">
<h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Peringatan Plafon</h6>
<p class="mb-0" id="plafon-over-message"></p>
</div>
<p class="text-muted small">Draft dapat diedit dan belum mengurangi saldo BS. Konfirmasi saldo sendiri membukukan pemakaian BS dan mengunci transaksi.</p>
<div class="d-grid d-sm-flex gap-2 animate-slide-in" style="animation-delay: 0.2s">
<button type="submit" name="save_action" value="draft" class="btn btn-outline-secondary btn-lg py-3 flex-fill">
<i class="bi bi-save me-2 fs-5"></i> Simpan Draft
</button>
<button type="submit" name="save_action" value="confirm" class="btn btn-primary-modern btn-lg py-3 flex-fill">
<i class="bi bi-check-circle me-2 fs-5"></i> <span data-bs-confirm-label>Simpan &amp; Konfirmasi</span>
</button>
</div>
</form>
</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
let plafonTotal = 0;
let checkPlafonTimeout = null;
let rowIndex = {{ count($oldDetails) }}; // 🎯 Mulai index dari jumlah old details

// 🎯 FORMAT RUPIAH HELPER
function formatRupiah(angka) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka); }
function formatRupiahInput(angka) {
if (angka === 0 || angka === '' || angka === null || isNaN(angka)) return '';
return new Intl.NumberFormat('id-ID').format(angka);
}
function unformatRupiah(str) {
if (!str) return 0;
return parseInt(String(str).replace(/\./g, '').replace(/[^0-9]/g, '')) || 0;
}

// 🎯 HITUNG TOTAL - baca dari hidden input .nominal-real
function hitungTotal() {
let total = 0;
$('.nominal-real').each(function() { total += parseFloat($(this).val()) || 0; });
$('#total-rincian').html(formatRupiah(total));
$('#info-total-input').html(formatRupiah(total));
if (plafonTotal > 0) {
const sisa = plafonTotal - total;
$('#info-sisa-anggaran').html(formatRupiah(sisa));
if (sisa < 0) {
$('#info-sisa-anggaran').removeClass('text-info text-success').addClass('text-danger');
$('#plafon-over-warning').removeClass('d-none');
$('#plafon-over-message').html(`Total rincian <strong>${formatRupiah(total)}</strong> melebihi plafon <strong>${formatRupiah(plafonTotal)}</strong>. Selisih: <strong>${formatRupiah(Math.abs(sisa))}</strong>. Transaksi tetap dapat disimpan (Soft Control), namun atasan akan dinotifikasi.`);
} else {
$('#info-sisa-anggaran').removeClass('text-danger').addClass('text-info');
$('#plafon-over-warning').addClass('d-none');
}
}
const sisa = getSisaSaldoBs();
if (sisa !== null && total > sisa) {
$('#bs-warning-info').removeClass('d-none');
$('#total-rincian').removeClass('text-success').addClass('text-danger');
} else {
$('#bs-warning-info').addClass('d-none');
$('#total-rincian').removeClass('text-danger').addClass('text-success');
}
return total;
}
function cekPlafonRealtime() {
const tujuanDinasId = $('#tujuan_dinas_id').val();
const totalNominal = hitungTotal();
const tanggal = $('#tanggal_picker').val() || '{{ \App\Support\AppTime::now()->toDateString() }}';
if (!tujuanDinasId) return;
$.ajax({
url: '{{ route("api.check_plafon_realtime") }}', type: 'POST',
data: { _token: '{{ csrf_token() }}', tujuan_dinas_id: tujuanDinasId, total_nominal: totalNominal, tanggal: tanggal },
success: function(response) {
if (response.plafon_value) {
plafonTotal = response.plafon_value;
$('#info-plafon-total').html(formatRupiah(plafonTotal));
$('#info-sisa-anggaran').html(formatRupiah(plafonTotal - totalNominal));
if (response.is_over) {
$('#plafon-status-message').html(`<span class="badge bg-danger fs-6">⚠️ OVER PLAFON - Selisih: ${formatRupiah(response.selisih)}</span>`);
} else {
$('#plafon-status-message').html(`<span class="badge bg-success fs-6">✅ Dalam Plafon - Sisa: ${formatRupiah(response.sisa || 0)}</span>`);
}
} else {
$('#plafon-status-message').html(`<span class="badge bg-warning text-dark fs-6">${response.message}</span>`);
}
}
});
}
function debounceCekPlafon() { clearTimeout(checkPlafonTimeout); checkPlafonTimeout = setTimeout(cekPlafonRealtime, 500); }
function ambilInfoPlafon() {
const tujuanDinasId = $('#tujuan_dinas_id').val();
const tanggal = $('#tanggal_picker').val() || '{{ \App\Support\AppTime::now()->toDateString() }}';
if (!tujuanDinasId) { $('#card-info-plafon').slideUp(); return; }
$.ajax({
url: '{{ route("api.get_plafon_info") }}', type: 'POST',
data: { _token: '{{ csrf_token() }}', tujuan_dinas_id: tujuanDinasId, tanggal: tanggal },
success: function(response) {
if (response.found) {
plafonTotal = response.plafon_value;
$('#info-plafon-total').html(formatRupiah(plafonTotal));
$('#card-info-plafon').slideDown();
hitungTotal();
} else {
plafonTotal = 0;
$('#card-info-plafon').slideUp();
}
}
});
}
function getSisaSaldoBs() {
    const value = document.getElementById('bs-saldo-info')?.dataset.balance;
    return value === undefined || value === '' ? null : Number(value);
}
document.addEventListener('bs-balance-updated', hitungTotal);
$('#teritory-select').on('change', function() {
var teritory = $(this).val();
var provinsiSelect = $('#provinsi-select');
var kotaSelect = $('#kota-select');
provinsiSelect.prop('disabled', true).html('<option value="">-- Loading Provinsi... --</option>');
kotaSelect.prop('disabled', true).html('<option value="">-- Pilih Provinsi Terlebih Dahulu --</option>');
resetTujuanDinas();
if (teritory) {
$.ajax({
url: '{{ route("api.wilayah.provinsi") }}', type: 'GET', data: { teritory: teritory }, dataType: 'json',
success: function(response) {
provinsiSelect.prop('disabled', false).html('<option value="">-- Pilih Provinsi --</option>');
if (response.length > 0) { $.each(response, function(key, value) { provinsiSelect.append('<option value="' + value.id + '">' + value.nama + '</option>'); }); }
}
});
}
});
$('#provinsi-select').on('change', function() {
var provinsiId = $(this).val();
var kotaSelect = $('#kota-select');
kotaSelect.prop('disabled', true).html('<option value="">-- Loading Kota/Kabupaten... --</option>');
resetTujuanDinas();
if (provinsiId) {
$.ajax({
url: '{{ route("api.wilayah.kota") }}', type: 'GET', data: { provinsi_id: provinsiId }, dataType: 'json',
success: function(response) {
kotaSelect.prop('disabled', false).html('<option value="">-- Pilih Kota/Kabupaten --</option>');
if (response.length > 0) { $.each(response, function(key, value) { kotaSelect.append('<option value="' + value.id + '">' + value.nama + '</option>'); }); }
}
});
}
});
$('#kota-select').on('change', function() {
var kotaId = $(this).val();
var provinsiText = $('#provinsi-select option:selected').text();
var kotaText = $(this).val() ? $(this).find('option:selected').text() : '';
if (kotaId) {
$('#tujuan_dinas_id').val(kotaId);
$('#preview-text').html('<strong>' + kotaText + '</strong>, ' + provinsiText);
$('#preview-tujuan').removeClass('alert-warning alert-info').addClass('alert-success');
ambilInfoPlafon();
} else { resetTujuanDinas(); }
});
function resetTujuanDinas() {
$('#tujuan_dinas_id').val('');
$('#preview-text').text('Belum ada wilayah yang dipilih.');
$('#preview-tujuan').removeClass('alert-info alert-success').addClass('alert-warning');
$('#card-info-plafon').slideUp();
plafonTotal = 0;
}
@if(old('teritory'))
$('#teritory-select').trigger('change');
setTimeout(function() {
@if(old('provinsi_id'))
$('#provinsi-select').val('{{ old("provinsi_id") }}').trigger('change');
setTimeout(function() {
@if(old('tujuan_dinas_id'))
$('#kota-select').val('{{ old("tujuan_dinas_id") }}').trigger('change');
@endif
}, 500);
@endif
}, 500);
@endif
function updateCostCategoryVisibility() {
const jarakKm = parseFloat($('#jarak_km').val()) || 0;
const durasiJam = parseFloat($('#durasi_jam').val()) || 0;
const canLumpsum = jarakKm > 200;
const canHotel = durasiJam > 8;
$('#lumpsum-info').html(canLumpsum ? '<span class="text-success fw-bold">✅ Dapat Lumpsum (Jarak > 200 KM)</span>' : '<span class="text-danger fw-bold">❌ Tidak Dapat Lumpsum (Jarak ≤ 200 KM)</span>');
$('#hotel-info').html(canHotel ? '<span class="text-success fw-bold">✅ Dapat Hotel (Durasi > 8 Jam)</span>' : '<span class="text-danger fw-bold">❌ Tidak Dapat Hotel (Durasi ≤ 8 Jam)</span>');
$('.cost-category-select').each(function() {
const $select = $(this);
const currentVal = $select.val();
$select.find('option[data-type="lumpsum"]').each(function() {
if (canLumpsum) { $(this).prop('disabled', false).show(); }
else { $(this).prop('disabled', true).hide(); if ($(this).val() === currentVal) $select.val('').trigger('change'); }
});
$select.find('option[data-type="hotel"]').each(function() {
if (canHotel) { $(this).prop('disabled', false).show(); }
else { $(this).prop('disabled', true).hide(); if ($(this).val() === currentVal) $select.val('').trigger('change'); }
});
$select.find('option[data-type="umum"]').prop('disabled', false).show();
});
}
$('#jarak_km, #durasi_jam').on('input change', function() { updateCostCategoryVisibility(); });

const costCategoryOptions = `
<option value="">-- Pilih Cost Category --</option>
@foreach($cost_categories as $cc)
@php
$categoryName = strtolower($cc->name ?? $cc->description ?? $cc->code ?? '');
$type = 'umum';
if (str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku')) $type = 'lumpsum';
elseif (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi') || str_contains($categoryName, 'menginap')) $type = 'hotel';
@endphp
<option value="{{ $cc->id }}" data-type="{{ $type }}">{{ $cc->name ?? $cc->description ?? $cc->code }}</option>
@endforeach
`;

// 🎯 TAMBAH BARIS BARU - dengan input nominal terformat
$('#btn-tambah-baris').click(function() {
let barisBaru = `
<tr id="baris-${rowIndex}" class="animate-slide-in">
<td><select name="details[${rowIndex}][cost_category_id]" class="form-select cost-category-select" required>${costCategoryOptions}</select></td>
<td><input type="text" name="details[${rowIndex}][remark]" class="form-control" placeholder="Opsional"></td>
<td>
<input type="text" class="form-control nominal-input nominal-formatted" placeholder="0" inputmode="decimal" autocomplete="off">
<input type="hidden" name="details[${rowIndex}][nominal]" class="nominal-real" value="0">
<div id="cost-info-${rowIndex}" class="mt-1 small"></div>
</td>
<td class="text-center align-middle"><button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus" data-id="${rowIndex}"><i class="bi bi-trash"></i></button></td>
</tr>`;
$('#tabel-detail tbody').append(barisBaru);
updateCostCategoryVisibility();
rowIndex++;
});

// 🎯 HAPUS BARIS - bekerja untuk SEMUA baris (termasuk baris awal)
$(document).on('click', '.btn-hapus', function() {
let idBaris = $(this).data('id');
$('#baris-' + idBaris).fadeOut(300, function() {
$(this).remove();
hitungTotal();
debounceCekPlafon();
// Cek jika tidak ada baris sama sekali
if ($('#tabel-detail tbody tr').length === 0) {
// Tambahkan baris kosong baru otomatis
$('#btn-tambah-baris').click();
}
});
});

// 🎯 FORMAT INPUT NOMINAL - event listener untuk semua input nominal (existing + baru)
$(document).on('input', '.nominal-formatted', function() {
let $input = $(this);
let raw = $input.val().replace(/\./g, '').replace(/[^0-9]/g, '');
let num = parseInt(raw) || 0;
$input.val(formatRupiahInput(num));
$input.closest('td').find('.nominal-real').val(num);
hitungTotal();
debounceCekPlafon();
});

// Saat blur, pastikan format konsisten
$(document).on('blur', '.nominal-formatted', function() {
let $input = $(this);
let raw = $input.val().replace(/\./g, '').replace(/[^0-9]/g, '');
let num = parseInt(raw) || 0;
$input.val(formatRupiahInput(num));
$input.closest('td').find('.nominal-real').val(num);
});

const tglInput  = document.getElementById('tanggal_picker');
const warning   = document.getElementById('periodWarning');
const submitButtons = document.querySelectorAll('#form-perjalanan-dinas button[name="save_action"]');
const openPeriods = @json($openPeriods);
if (tglInput && typeof flatpickr !== 'undefined') {
flatpickr(tglInput, {
dateFormat: "Y-m-d", altInput: true, altFormat: "d F Y", locale: "id", maxDate: "today",
defaultDate: tglInput.value || new Date(),
disable: [function(date) {
const y = date.getFullYear(); const m = String(date.getMonth() + 1).padStart(2, '0');
const periodeStr = `${y}-${m}`;
return !openPeriods.includes(periodeStr);
}],
onChange: function(selectedDates, dateStr, instance) {
validatePeriod(dateStr);
if ($('#tujuan_dinas_id').val()) { ambilInfoPlafon(); }
}
});
}
async function validatePeriod(dateStr) {
if (!dateStr) return;
const periode = dateStr.slice(0, 7);
if (!openPeriods.includes(periode)) {
document.getElementById('periodWarningText').textContent = `Periode ${periode} tidak aktif atau sudah DITUTUP. Pilih tanggal di bulan yang berjalan/OPEN.`;
warning.classList.remove('d-none');
submitButtons.forEach(button => button.disabled = true);
return;
}
try {
const data = await (await fetch(`/api/periode/status?periode=${periode}`)).json();
if (!data.open) {
document.getElementById('periodWarningText').textContent = `Periode ${periode} sudah DITUTUP. Transaksi akan ditolak sistem — pilih tanggal di periode OPEN.`;
warning.classList.remove('d-none');
submitButtons.forEach(button => button.disabled = true);
} else {
warning.classList.add('d-none');
submitButtons.forEach(button => button.disabled = false);
}
} catch (err) {
console.warn('Gagal cek status periode:', err);
warning.classList.add('d-none');
submitButtons.forEach(button => button.disabled = false);
}
}
if (tglInput && tglInput._flatpickr && tglInput._flatpickr.selectedDates.length > 0) {
validatePeriod(tglInput._flatpickr.selectedDates[0].toISOString().slice(0, 10));
} else if (tglInput && tglInput.value) {
validatePeriod(tglInput.value);
}
$('#form-perjalanan-dinas').on('submit', function(e) {
if (!$('#tujuan_dinas_id').val()) {
e.preventDefault();
alert('⚠️ Silakan pilih Kota/Kabupaten tujuan terlebih dahulu!');
$('#kota-select').focus();
return false;
}
// 🎯 Validasi: minimal 1 baris dengan nominal > 0
let validDetails = 0;
$('.nominal-real').each(function() {
if (parseFloat($(this).val()) > 0) validDetails++;
});
if (validDetails === 0) {
e.preventDefault();
alert('⚠️ Minimal 1 rincian biaya dengan nominal harus diisi!');
return false;
}
const sisa = getSisaSaldoBs();
const total = hitungTotal();
// Saldo diperiksa di server saat Konfirmasi.

});
updateCostCategoryVisibility();
$('#jarak_km').trigger('input');
$('#durasi_jam').trigger('input');
hitungTotal();
});
</script>
</body>
</html>

~~~~

## FILE: resources/views/transaksi_perjalanan_dinas/index.blade.php

SHA-256: `d010538465516b006aef4a5163c0cd6a9d473ece39146c835130a4910254b51a`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Perjalanan Dinas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    {{-- 🎯 Font: Plus Jakarta Sans (konsisten dengan halaman lain) --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            --info-gradient: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
            --primary-color: #667eea;
            --bg-color: #f0f4f8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            background-image: radial-gradient(#667eea 0.5px, transparent 0.5px), radial-gradient(#667eea 0.5px, var(--bg-color) 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            min-height: 100vh;
        }

        .text-muted { color: #718096 !important; }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }

        .page-header h1 { font-weight: 800; margin: 0; font-size: 2rem; }
        .page-header p  { margin: 0.5rem 0 0 0; opacity: 0.9; }

        /* 🎯 Tombol di atas gradient header */
        .btn-modern {
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-light-header {
            background: white;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(4px);
        }
        .btn-ghost:hover { background: rgba(255, 255, 255, 0.25); color: white; }

        /* 🎯 Card statis — TANPA hover effect */
        .card-modern {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header-modern {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
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
        }

        .card-header-modern h5 i {
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .count-badge {
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.45rem 0.9rem;
            border-radius: 20px;
        }

        /* 🎯 Toolbar filter export */
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
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
            transition: all 0.3s ease;
        }
        .btn-success-modern:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(17, 153, 142, 0.5); color: white; }

        /* 🎯 Tabel modern */
        .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }

        .table-modern thead th {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
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

        .table-modern tbody tr:hover { background: rgba(102, 126, 234, 0.03); }
        .table-modern tbody tr:last-child td { border-bottom: none; }

        /* ID transaksi sebagai code badge */
        .code-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            font-size: 0.88rem;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        /* Avatar inisial user */
        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cell-icon { color: #a0aec0; margin-right: 0.4rem; }

        .desc-cell {
            max-width: 280px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* 🎯 Tombol aksi kecil */
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
        }

        .btn-action.detail {
            background: var(--info-gradient);
            box-shadow: 0 3px 10px rgba(0, 91, 234, 0.3);
        }
        .btn-action.detail:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 91, 234, 0.4); color: white; }

        .btn-action.hapus {
            background: var(--danger-gradient);
            box-shadow: 0 3px 10px rgba(238, 9, 121, 0.3);
        }
        .btn-action.hapus:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(238, 9, 121, 0.4); color: white; }

        /* 🎯 Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
        }
        .empty-state .icon-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .empty-state .icon-circle i {
            font-size: 2.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .empty-state h6 { font-weight: 700; color: #2d3748; }

        /* 🎯 Pagination custom */
        .pagination { gap: 4px; }
        .page-link {
            border: none;
            border-radius: 10px;
            color: #667eea;
            font-weight: 600;
            margin: 0;
            padding: 0.5rem 0.85rem;
        }
        .page-link:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .page-item.active .page-link {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.4);
        }
        .page-item.disabled .page-link { color: #a0aec0; background: transparent; }

        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            .desc-cell { max-width: 180px; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1><i class="bi bi-airplane me-3"></i>Perjalanan Dinas</h1>
                    <p class="mb-0">Daftar seluruh transaksi perjalanan dinas Anda</p>
                </div>
                <div class="col-lg-6 d-flex justify-content-lg-end gap-2 flex-wrap mt-3 mt-lg-0">
                    <a href="{{ route('transaksi_perjalanan_dinas.create') }}" class="btn btn-light-header btn-modern">
                        <i class="bi bi-plus-lg"></i> Transaksi Baru
                    </a>
                    <a href="{{ route('dashboard.transaksi') }}" class="btn btn-ghost btn-modern">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 1200px; margin-bottom: 3rem;">
        @if(session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                    <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card-modern animate-slide-in">
            <div class="card-header-modern">
                <h5>
                    <i class="bi bi-list-check"></i> Daftar Transaksi Perjalanan Dinas
                    <span class="count-badge">{{ $transaksis->total() }} Transaksi</span>
                </h5>
                
                {{-- 🎯 EXPORT FORM --}}
                <form method="GET" action="{{ route('transaksi_perjalanan_dinas.export') }}" class="d-inline-flex gap-2 align-items-center flex-wrap">
                    <select name="periode" class="form-select form-select-sm filter-select">
                        <option value="">Semua Periode</option>
                        @foreach(\App\Models\Periode::orderBy('periode', 'desc')->get() as $p)
                            <option value="{{ $p->periode }}">{{ $p->periode }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-success-modern btn-modern">
                        <i class="bi bi-download"></i> Export
                    </button>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-2"></i>ID Transaksi</th>
                                <th><i class="bi bi-calendar-event me-2"></i>Tanggal</th>
                                <th><i class="bi bi-person me-2"></i>User</th>
                                <th><i class="bi bi-geo-alt me-2"></i>Tujuan</th>
                                <th><i class="bi bi-file-text me-2"></i>Deskripsi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksis as $t)
                            <tr>
                                <td><span class="code-badge">{{ $t->transaksi_id }}</span><div class="small mt-1">{{ $t->status_label }}</div></td>
                                <td>
                                    <i class="bi bi-calendar3 cell-icon"></i>{{ \Carbon\Carbon::parse($t->tanggal)->format('d M Y') }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ strtoupper(substr($t->user->nama ?? '-', 0, 1)) }}</span>
                                        <span class="fw-semibold">{{ $t->user->nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td><i class="bi bi-geo-alt cell-icon"></i>{{ $t->tujuanDinas->nama ?? '-' }}</td>
                                <td><div class="desc-cell" title="{{ $t->description }}">{{ $t->description }}</div></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('transaksi_perjalanan_dinas.show', $t->transaksi_id) }}" class="btn-action detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        @if($t->status === 'DRAFT' && auth()->user()->canAccessTransaction($t) && app(\App\Services\PeriodService::class)->isOpen($t->periode))
                                        <form action="{{ route('transaksi_perjalanan_dinas.destroy', $t->transaksi_id) }}" method="POST" class="d-inline">
                                            @csrf 
                                            @method('DELETE')
                                            <button class="btn-action hapus" onclick="return confirm('Yakin hapus draft dinas ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="icon-circle">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h6>Belum ada data transaksi</h6>
                                        <p class="text-muted mb-3">Mulai buat transaksi perjalanan dinas pertama Anda.</p>
                                        <a href="{{ route('transaksi_perjalanan_dinas.create') }}" class="btn btn-success-modern btn-modern">
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
            <div class="card-footer bg-white border-0 d-flex justify-content-end px-4 pb-4">
                {{ $transaksis->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/transaksi_perjalanan_dinas/show.blade.php

SHA-256: `0bd4e39280885788616ee28248e61341e9cb339b5c392583d0c65a1f7b42f935`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Perjalanan Dinas - {{ $transaksi->transaksi_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        @endphp
        @include('partials.travel-confirmation')
        @include('bs-usage-approvals.actions')
        @include('bs-usage-approvals.history')
        <div id="view-mode">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5><i class="bi bi-info-circle me-2"></i>Informasi Transaksi</h5>
                    <button @disabled(!$canEdit) onclick="toggleEditMode()" class="btn btn-modern" style="background: var(--warning-color); color: white;">
                        <i class="bi bi-pencil-square"></i> Edit Data
                    </button>
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
                                    <th style="width: 10%;" class="text-center">Aksi</th>
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
                                        <td class="text-center">
                                            <button @disabled(!$canEdit) class="btn-action-sm btn-hapus-detail" style="background: var(--danger-color); color: white;" data-id="{{ $d->id }}" data-nominal="{{ number_format($d->nominal, 0, ',', '.') }}" data-remark="{{ $d->remark ?? 'Tanpa Keterangan' }}">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada rincian biaya.</td></tr>
                                @endforelse
                            </tbody>
                            @if($total > 0)
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">TOTAL</td>
                                    <td class="text-end" style="color: var(--primary-color);">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Card Tambah Rincian Baru (View Mode) -->
            <div class="card-modern" @if(!$canEdit) hidden @endif>
                <div class="card-header-modern" style="background: #f0fdf4; border-bottom-color: #bbf7d0;">
                    <h5 style="color: var(--success-color);"><i class="bi bi-plus-circle me-2"></i>Tambah Rincian Biaya Baru</h5>
                    <button type="button" class="btn btn-modern" style="background: var(--success-color); color: white;" id="btn-tambah-baris-view">
                        <i class="bi bi-plus-lg"></i> Tambah Baris
                    </button>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('transaksi_perjalanan_dinas.store_detail', $transaksi->transaksi_id) }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table-modern" id="tabel-detail-view">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Cost Category</th>
                                        <th style="width: 40%;">Remark (Keterangan)</th>
                                        <th style="width: 20%;">Nominal (Rp)</th>
                                        <th style="width: 10%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="baris-view-0" class="animate-slide-in">
                                        <td>
                                            <select name="details[0][cost_category_id]" class="form-select" required>
                                                <option value="">-- Pilih Cost Category --</option>
                                                @foreach($cost_categories as $cc)
                                                    @php $name = strtolower($cc->name ?? $cc->description ?? ''); @endphp
                                                    <option value="{{ $cc->id }}" data-type="{{ str_contains($name, 'lumpsum') ? 'lumpsum' : (str_contains($name, 'hotel') ? 'hotel' : 'umum') }}">
                                                        {{ $cc->name ?? $cc->description ?? $cc->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="details[0][remark]" class="form-control" placeholder="Keterangan" required></td>
                                        <td>
                                            <input type="text" class="form-control nominal-input-view nominal-formatted" placeholder="0" inputmode="decimal" autocomplete="off">
                                            <input type="hidden" name="details[0][nominal]" class="nominal-real" value="0">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn-action-sm btn-hapus-view" style="background: var(--danger-color); color: white;"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-modern" style="background: var(--primary-color); color: white;">
                                <i class="bi bi-save"></i> Simpan Rincian Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 🎯 MODE 2: FORM EDIT (Tersembunyi Default) -->
        <!-- ========================================== -->
        <div id="edit-mode" style="display: none;">
            <form action="{{ route('transaksi_perjalanan_dinas.update', $transaksi->transaksi_id) }}" method="POST" id="form-edit-pd">
                @csrf
                @method('PUT')

                <div class="card-modern">
                    <div class="card-header-modern" style="background: #fffbeb; border-bottom-color: #fcd34d;">
                        <h5 style="color: var(--warning-color);"><i class="bi bi-pencil-square me-2"></i>Mode Edit Data</h5>
                        <button type="button" onclick="toggleEditMode()" class="btn btn-modern" style="background: var(--secondary-color); color: white;">
                            <i class="bi bi-x-lg"></i> Batal
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">ID Transaksi</label>
                                <input type="text" class="form-control" value="{{ $transaksi->transaksi_id }}" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tanggal Dinas <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" value="{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                @include('partials.transaction-funding', ['fundingType' => 'perjalanan_dinas', 'fundingDocument' => $transaksi])
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jarak (KM) <span class="text-danger">*</span></label>
                                <input type="number" name="jarak_km" id="edit-jarak" class="form-control" value="{{ $transaksi->jarak_km }}" step="0.1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Durasi (Jam) <span class="text-danger">*</span></label>
                                <input type="number" name="durasi_jam" id="edit-durasi" class="form-control" value="{{ $transaksi->durasi_jam }}" step="0.5" required>
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
                                        <option value="{{ $c->id }}" {{ $transaksi->tujuan_dinas_id == $c->id ? 'selected' : '' }}>{{ $c->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="3" required>{{ $transaksi->description }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Edit Rincian -->
                <div class="card-modern">
                    <div class="card-header-modern" style="background: #fffbeb; border-bottom-color: #fcd34d;">
                        <h5 style="color: var(--warning-color);"><i class="bi bi-list-ul me-2"></i>Edit Rincian Biaya</h5>
                        <button type="button" class="btn btn-modern" style="background: var(--success-color); color: white;" id="btn-add-row-edit">
                            <i class="bi bi-plus-lg"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Kategori</th>
                                        <th style="width: 35%;">Keterangan</th>
                                        <th style="width: 20%;">Nominal</th>
                                        <th style="width: 15%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-edit">
                                    @foreach($transaksi->details as $d)
                                    <tr class="detail-row" data-id="{{ $d->id }}">
                                        <td>
                                            <select name="details[{{ $d->id }}][cost_category_id]" class="form-select form-select-sm cat-select" required>
                                                @foreach($cost_categories as $cc)
                                                    @php $name = strtolower($cc->name ?? $cc->description ?? ''); @endphp
                                                    <option value="{{ $cc->id }}" data-type="{{ str_contains($name, 'lumpsum') ? 'lumpsum' : (str_contains($name, 'hotel') ? 'hotel' : 'umum') }}" {{ $d->cost_category_id == $cc->id ? 'selected' : '' }}>
                                                        {{ $cc->name ?? $cc->description }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="details[{{ $d->id }}][remark]" value="{{ $d->remark }}" class="form-control form-control-sm"></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm nominal-input-edit nominal-formatted" value="{{ number_format($d->nominal, 0, ',', '.') }}" inputmode="decimal" autocomplete="off">
                                            <input type="hidden" name="details[{{ $d->id }}][nominal]" class="nominal-real" value="{{ $d->nominal }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="delete_details[]" value="{{ $d->id }}" class="delete-check" style="display:none;">
                                            <button type="button" class="btn-action-sm btn-toggle-delete" style="background: var(--danger-color); color: white;"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-modern" style="background: var(--primary-color); color: white;">
                        <i class="bi bi-save"></i> Simpan Semua Perubahan
                    </button>
                    <button type="button" onclick="toggleEditMode()" class="btn btn-modern" style="background: var(--secondary-color); color: white;">
                        <i class="bi bi-x-lg"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 🎯 Toggle View/Edit Mode
        function toggleEditMode() {
            if (!@json($canEdit)) return;
            const viewMode = document.getElementById('view-mode');
            const editMode = document.getElementById('edit-mode');
            
            if (viewMode.style.display === 'none') {
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // 🎯 FORMAT RUPIAH HELPER
        function formatRupiah(angka) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka); }
        function formatRupiahInput(angka) {
            if (angka === 0 || angka === '' || angka === null || isNaN(angka)) return '';
            return new Intl.NumberFormat('id-ID').format(angka);
        }
        function unformatRupiah(str) {
            if (!str) return 0;
            return parseInt(String(str).replace(/\./g, '').replace(/[^0-9]/g, '')) || 0;
        }

        $(document).ready(function() {
            // ==========================================
            // 🎯 FORMAT INPUT NOMINAL - untuk semua input nominal (view & edit)
            // ==========================================
            $(document).on('input', '.nominal-formatted', function() {
                let $input = $(this);
                let raw = $input.val().replace(/\./g, '').replace(/[^0-9]/g, '');
                let num = parseInt(raw) || 0;
                $input.val(formatRupiahInput(num));
                $input.closest('td').find('.nominal-real').val(num);
            });

            $(document).on('blur', '.nominal-formatted', function() {
                let $input = $(this);
                let raw = $input.val().replace(/\./g, '').replace(/[^0-9]/g, '');
                let num = parseInt(raw) || 0;
                $input.val(formatRupiahInput(num));
                $input.closest('td').find('.nominal-real').val(num);
            });

            // ==========================================
            // 🎯 VIEW MODE: Tambah Baris & Hapus Detail
            // ==========================================
            let rowIndexView = 1;
            const costCategoryOptions = `
                <option value="">-- Pilih Cost Category --</option>
                @foreach($cost_categories as $cc)
                    @php $name = strtolower($cc->name ?? $cc->description ?? ''); @endphp
                    <option value="{{ $cc->id }}" data-type="{{ str_contains($name, 'lumpsum') ? 'lumpsum' : (str_contains($name, 'hotel') ? 'hotel' : 'umum') }}">{{ $cc->name ?? $cc->description ?? $cc->code }}</option>
                @endforeach
            `;

            $('#btn-tambah-baris-view').click(function() {
                const html = `
                    <tr id="baris-view-${rowIndexView}" class="animate-slide-in">
                        <td><select name="details[${rowIndexView}][cost_category_id]" class="form-select" required>${costCategoryOptions}</select></td>
                        <td><input type="text" name="details[${rowIndexView}][remark]" class="form-control" placeholder="Keterangan" required></td>
                        <td>
                            <input type="text" class="form-control nominal-input-view nominal-formatted" placeholder="0" inputmode="decimal" autocomplete="off">
                            <input type="hidden" name="details[${rowIndexView}][nominal]" class="nominal-real" value="0">
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn-action-sm btn-hapus-view" style="background: var(--danger-color); color: white;"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#tabel-detail-view tbody').append(html);
                rowIndexView++;
            });

            $(document).on('click', '.btn-hapus-view', function() {
                $(this).closest('tr').fadeOut(300, function() {
                    $(this).remove();
                    // Auto-add baris jika kosong
                    if ($('#tabel-detail-view tbody tr').length === 0) {
                        $('#btn-tambah-baris-view').click();
                    }
                });
            });

            // SweetAlert Delete Detail (untuk row yang sudah ada di DB)
            $('.btn-hapus-detail').click(function() {
                const id = $(this).data('id');
                const nominal = $(this).data('nominal');
                const remark = $(this).data('remark');

                Swal.fire({
                    title: 'Hapus Detail Biaya?',
                    html: `<p class="mb-3">Anda akan menghapus: <strong>${remark}</strong> (Rp ${nominal})</p>
                           <textarea id="swal-reason" class="swal2-textarea" placeholder="Wajib isi alasan (misal: Input duplikat)" rows="3"></textarea>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const reason = document.getElementById('swal-reason').value;
                        if (!reason || reason.trim() === '') {
                            Swal.showValidationMessage('Alasan penghapusan wajib diisi!');
                            return false;
                        }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                        fetch(`/transaksi-perjalanan-dinas/detail/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            body: JSON.stringify({ reason: result.value })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false })
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Gagal!', data.message, 'error');
                            }
                        })
                        .catch(err => Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error'));
                    }
                });
            });

            // ==========================================
            // 🎯 EDIT MODE: Toggle Delete, Add Row, Cascading
            // ==========================================
            // Toggle delete
            $(document).on('click', '.btn-toggle-delete', function() {
                const row = $(this).closest('tr');
                const check = row.find('.delete-check');
                check.prop('checked', !check.prop('checked'));
                row.toggleClass('to-delete');
                row.find('input, select').not('.delete-check').prop('disabled', check.prop('checked'));
            });

            // Add new row di edit mode
            let newRowIdx = 1000;
            $('#btn-add-row-edit').click(function() {
                const html = `
                    <tr class="detail-row new-row animate-slide-in">
                        <td><select name="new_details[${newRowIdx}][cost_category_id]" class="form-select form-select-sm cat-select" required>${costCategoryOptions}</select></td>
                        <td><input type="text" name="new_details[${newRowIdx}][remark]" class="form-control form-control-sm" required></td>
                        <td>
                            <input type="text" class="form-control form-control-sm nominal-input-edit nominal-formatted" placeholder="0" inputmode="decimal" autocomplete="off">
                            <input type="hidden" name="new_details[${newRowIdx}][nominal]" class="nominal-real" value="0">
                        </td>
                        <td class="text-center"><button type="button" class="btn-action-sm btn-remove-new" style="background: var(--danger-color); color: white;"><i class="bi bi-trash"></i></button></td>
                    </tr>
                `;
                $('#tbody-edit').append(html);
                newRowIdx++;
                updateCategoryRules();
            });

            $(document).on('click', '.btn-remove-new', function() { $(this).closest('tr').remove(); });

            // Cascading Location
            $('#edit-teritory').change(function() {
                const ter = $(this).val();
                $('#edit-provinsi').html('<option>Loading...</option>').prop('disabled', true);
                $('#edit-kota').html('<option>Pilih Provinsi Dulu</option>').prop('disabled', true);
                $.get('/api/wilayah-provinsi?teritory=' + ter, function(res) {
                    let opts = '<option value="">-- Pilih Provinsi --</option>';
                    res.forEach(p => opts += `<option value="${p.id}">${p.nama}</option>`);
                    $('#edit-provinsi').html(opts).prop('disabled', false);
                });
            });
            $('#edit-provinsi').change(function() {
                const prov = $(this).val();
                $('#edit-kota').html('<option>Loading...</option>').prop('disabled', true);
                $.get('/api/wilayah-kota?provinsi_id=' + prov, function(res) {
                    let opts = '<option value="">-- Pilih Kota --</option>';
                    res.forEach(c => opts += `<option value="${c.id}">${c.nama}</option>`);
                    $('#edit-kota').html(opts).prop('disabled', false);
                });
            });

            // Category Rules (Lumpsum > 200km, Hotel > 8hr)
            function updateCategoryRules() {
                const jarak = parseFloat($('#edit-jarak').val()) || 0;
                const durasi = parseFloat($('#edit-durasi').val()) || 0;
                $('.cat-select').each(function() {
                    $(this).find('option').each(function() {
                        const type = $(this).data('type');
                        let disabled = false;
                        if (type === 'lumpsum' && jarak <= 200) disabled = true;
                        if (type === 'hotel' && durasi <= 8) disabled = true;
                        $(this).prop('disabled', disabled);
                        if (disabled && $(this).is(':selected')) $(this).parent().val('');
                    });
                });
            }
            $('#edit-jarak, #edit-durasi').on('input change', updateCategoryRules);
            updateCategoryRules();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

~~~~
