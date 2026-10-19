<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Buat Pengajuan BS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root { --bg-body: #f8fafc; --bg-card: #ffffff; --text-primary: #0f172a; --text-secondary: #64748b; --border-color: #e2e8f0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-primary); min-height: 100vh; }
.page-header { padding: 2rem 0 1rem; }
.card-custom { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
.btn-nav { background-color: white; border: 1px solid var(--border-color); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; }
.btn-primary-custom { background-color: var(--text-primary); color: white; border-radius: 10px; padding: 0.75rem 1.25rem; font-weight: 600; border: none; width: 100%; }
.btn-primary-custom:hover { background-color: #334155; color: white; }
.form-label { font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem; }
.form-control, .form-select { border-radius: 10px; border: 1px solid var(--border-color); padding: 0.6rem 1rem; }
.form-control:focus, .form-select:focus { border-color: #94a3b8; box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.1); }
.tipe-card { display: flex; align-items: center; height: 100%; padding: 1rem; background: #fff; border: 2px solid var(--border-color); border-radius: 12px; cursor: pointer; transition: all 0.2s ease; margin-bottom: 0; }
.tipe-card:hover { border-color: #94a3b8; background-color: #f8fafc; }
.tipe-card input[type="radio"] { width: 1.2em; height: 1.2em; margin-right: 0.85rem; cursor: pointer; flex-shrink: 0; }
.tipe-card .tipe-icon { font-size: 1.6rem; margin-right: 0.85rem; flex-shrink: 0; }
.tipe-card.active { border-color: #3b82f6; background-color: #eff6ff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12); }
.member-card { display: flex; align-items: center; padding: 0.75rem 1rem; margin-bottom: 0.5rem; background: white; border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer; transition: all 0.2s ease; }
.member-card:hover { border-color: #94a3b8; background-color: #f8fafc; }
.member-card:has(input:checked) { border-color: #3b82f6; background-color: #eff6ff; }
.member-card input[type="checkbox"] { width: 1.2em; height: 1.2em; margin-right: 1rem; cursor: pointer; flex-shrink: 0; }
#section-anggota-dinas { display: none; }
#section-anggota-dinas.show { display: block; animation: fadeIn 0.25s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
/* 🎯 Attachment Section */
.attachment-dropzone {
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    background: #f8fafc;
    transition: all 0.2s ease;
    cursor: pointer;
}
.attachment-dropzone:hover, .attachment-dropzone.dragover {
    border-color: #3b82f6;
    background: #eff6ff;
}
.attachment-dropzone.has-file {
    border-color: #10b981;
    background: #ecfdf5;
}
.attachment-dropzone:focus-within { outline: 2px solid #3b82f6; outline-offset: 3px; }
.attachment-dropzone i.drop-icon { font-size: 2rem; color: #94a3b8; }
.attachment-dropzone.has-file i.drop-icon { color: #10b981; }
.attachment-preview { display: none; align-items: center; gap: 0.75rem; }
.attachment-preview.show { display: flex; }
.attachment-preview .file-info { flex: 1; text-align: left; }
.attachment-preview .file-name { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; word-break: break-all; }
.attachment-preview .file-size { color: var(--text-secondary); font-size: 0.8rem; }
.attachment-preview .btn-remove { background: #fee2e2; color: #dc2626; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.attachment-preview .btn-remove:hover { background: #fecaca; }
</style>
@include('partials.mobile-assets')
</head>
<body class="py-4 py-md-5">
@include('partials.application-time')
<div class="container" style="max-width: 700px;">
<div class="page-header d-flex justify-content-between align-items-center mb-4">
<h3 class="fw-bold mb-0">Form Pengajuan BS</h3>
<a href="{{ route('dashboard.transaksi') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">✅ {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
<strong>Gagal:</strong>
<ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="card-custom mb-4">
<div class="alert alert-info bg-opacity-10 border-0 text-primary fw-semibold mb-4" style="border-radius: 10px;">
<i class="bi bi-info-circle me-2"></i> Periode: {{ $periode }} | Sisa Saldo BS: Rp {{ number_format($sisaSaldo, 0, ',', '.') }}
</div>

<form action="{{ route('pengajuan-bs.store') }}" method="POST" id="form-pengajuan" enctype="multipart/form-data">
@csrf

{{-- ================= TIPE PENGAJUAN ================= --}}
<div class="mb-4">
<label class="form-label">Tipe Pengajuan <span class="text-danger">*</span></label>
<div class="row g-3">
<div class="col-md-6">
<label class="tipe-card" for="tipe_operasional">
<input type="radio" name="tipe_pengajuan" id="tipe_operasional"
value="operasional_sales"
{{ old('tipe_pengajuan', 'operasional_sales') === 'operasional_sales' ? 'checked' : '' }}>
<i class="bi bi-briefcase-fill tipe-icon" style="color: #3b82f6;"></i>
<div>
<strong class="d-block" style="font-size: 0.95rem;">Operasional Sales</strong>
<span class="text-muted" style="font-size: 0.78rem;">Biaya operasional kegiatan sales </span>
</div>
</label>
</div>
<div class="col-md-6">
<label class="tipe-card" for="tipe_dinas">
<input type="radio" name="tipe_pengajuan" id="tipe_dinas"
value="perjalanan_dinas"
{{ old('tipe_pengajuan') === 'perjalanan_dinas' ? 'checked' : '' }}>
<i class="bi bi-airplane-fill tipe-icon" style="color: #10b981;"></i>
<div>
<strong class="d-block" style="font-size: 0.95rem;">Perjalanan Dinas</strong>
<span class="text-muted" style="font-size: 0.78rem;">Perjalanan dinas perseorangan dan grup</span>
</div>
</label>
</div>
</div>
@error('tipe_pengajuan')
<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
@enderror
</div>

{{-- ================= NOMINAL PENGAJUAN ================= --}}
<div class="mb-4">
<label class="form-label">Nominal Pengajuan <span class="text-danger">*</span></label>
<div class="input-group">
<span class="input-group-text bg-white border-end-0">Rp</span>
<input type="text"
id="nominal-display"
inputmode="numeric"
autocomplete="off"
class="form-control form-control-lg border-start-0"
placeholder="0"
required
value="{{ old('nominal_pengajuan') ? number_format(old('nominal_pengajuan'), 0, ',', '.') : '' }}">
<input type="hidden" name="nominal_pengajuan" id="nominal-raw" value="{{ old('nominal_pengajuan') }}">
</div>
</div>

{{-- ================= ANGGOTA GRUP DINAS (CONDITIONAL) ================= --}}
<div class="mb-4" id="section-anggota-dinas">
<div class="d-flex justify-content-between align-items-center mb-2">
<label class="form-label mb-0">Anggota Grup Dinas (Opsional)</label>
<span id="member-count" class="badge rounded-pill text-bg-primary d-none">0 dipilih</span>
</div>
<p class="text-muted small mb-3">
Centang rekan kerja yang akan <strong>ikut dalam perjalanan dinas</strong>. Setelah pencairan, anggota dapat menggunakan saldo BS gabungan Anda khusus untuk realisasi Perjalanan Dinas.
</p>
<div class="position-relative mb-3">
<i class="bi bi-search position-absolute text-secondary" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.9rem;"></i>
<input type="text" id="member-search" class="form-control" style="padding-left: 2.4rem;"
placeholder="Cari nama atau NIK rekan kerja..." autocomplete="off">
<button type="button" id="member-search-clear" class="btn btn-link btn-sm position-absolute text-secondary p-1 d-none"
style="right: 8px; top: 50%; transform: translateY(-50%); text-decoration: none;" title="Bersihkan pencarian">
<i class="bi bi-x-circle-fill"></i>
</button>
</div>
<div id="member-list" style="max-height: 280px; overflow-y: auto; padding-right: 5px;">
@forelse($members as $m)
<label class="member-card" for="member_{{ $m->id }}" data-search="{{ strtolower($m->nama . ' ' . $m->NIK) }}">
<input type="checkbox" name="member_ids[]" value="{{ $m->id }}" id="member_{{ $m->id }}"
{{ in_array($m->id, old('member_ids', [])) ? 'checked' : '' }}>
<div>
<strong class="d-block" style="font-size: 0.95rem; color: var(--text-primary);">{{ $m->nama }}</strong>
<span class="text-muted" style="font-size: 0.8rem;">NIK: {{ $m->NIK }}</span>
</div>
</label>
@empty
<p class="text-muted mb-0 small text-center py-3 bg-light rounded">Tidak ada rekan kerja aktif lainnya.</p>
@endforelse
@if($members->isNotEmpty())
<p id="member-empty" class="text-muted small text-center py-3 bg-light rounded d-none mb-0">
<i class="bi bi-emoji-frown me-1"></i> Tidak ada hasil yang cocok.
</p>
@endif
</div>
<small class="text-muted mt-2 d-block">
<i class="bi bi-info-circle me-1"></i> Anggota yang <strong>sudah dicentang tetap tampil</strong> meski tidak cocok dengan kata kunci. Kosongkan centang jika pengajuan hanya untuk Anda sendiri (Perorangan).
</small>
</div>

{{-- ================= 🎯 LAMPIRAN (CONDITIONAL - PERJALANAN DINAS) ================= --}}
<div class="mb-4" id="section-attachment" style="display: none;">
    <label class="form-label" for="attachment">
        Lampiran (Laporan HRIS / Izin Perjalanan Dinas) <span class="text-danger">*</span>
    </label>

    <div class="attachment-dropzone" id="attachment-dropzone">
        <label for="attachment" id="attachment-placeholder" style="cursor: pointer; display: block;">
            <i class="bi bi-cloud-arrow-up drop-icon d-block mb-2"></i>
            <div class="fw-semibold" style="font-size: 0.92rem;">Klik untuk pilih file atau seret ke sini</div>
            <div class="text-muted small mt-1">Format: <strong>PDF, JPG</strong> • Maksimal 2MB</div>
        </label>
        <div class="attachment-preview" id="attachment-preview" aria-live="polite">
            <i class="bi bi-file-earmark-fill drop-icon"></i>
            <div class="file-info">
                <div class="file-name" id="attachment-filename">-</div>
                <div class="file-size" id="attachment-filesize">-</div>
            </div>
            <button type="button"
                    class="btn-remove"
                    id="attachment-remove"
                    title="Hapus file" aria-label="Hapus lampiran">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <input type="file"
               name="attachment"
               id="attachment"
               class="form-control mt-3"
               accept=".pdf,.jpg,.jpeg"
               aria-describedby="attachment-help attachment-error"
               data-max-size="2097152">
    </div>

    <small class="text-muted mt-2 d-block" id="attachment-help">
        <i class="bi bi-info-circle me-1"></i> Unggah <strong>Laporan HRIS</strong> atau <strong>Surat Izin Perjalanan Dinas</strong> sebagai dokumen pendukung.
    </small>
    <div class="text-danger small mt-2" id="attachment-error" role="alert">@error('attachment'){{ $message }}@enderror</div>
</div>

{{-- ================= DESKRIPSI ================= --}}
<div class="mb-4">
<label class="form-label">Deskripsi / Keperluan</label>
<textarea name="description" class="form-control" rows="3" placeholder="Contoh: Biaya operasional area Semarang bulan Agustus">{{ old('description') }}</textarea>
</div>

<button type="submit" class="btn btn-primary-custom fs-5">
<i class="bi bi-send-check me-2"></i> Ajukan ke Atasan
</button>
</form>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
/* ============ 1. FORMAT RUPIAH NOMINAL (LIVE) ============ */
const display = document.getElementById('nominal-display');
const raw     = document.getElementById('nominal-raw');
const formatRibuan = (d) => d.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
const bersih = (v) => String(v).replace(/\D/g, '').replace(/^0+/, '').slice(0, 12);
display.addEventListener('input', function () {
const digitSebelumKaret = this.value.slice(0, this.selectionStart).replace(/\D/g, '').length;
const digits = bersih(this.value);
this.value   = digits ? formatRibuan(digits) : '';
let hitung = 0, pos = 0;
for (let i = 0; i < this.value.length; i++) {
if (hitung >= digitSebelumKaret) break;
if (this.value[i] >= '0' && this.value[i] <= '9') hitung++;
pos = i + 1;
}
this.setSelectionRange(pos, pos);
raw.value = digits;
});
display.addEventListener('paste', (e) => {
e.preventDefault();
const digits  = bersih((e.clipboardData || window.clipboardData).getData('text'));
display.value = digits ? formatRibuan(digits) : '';
raw.value     = digits;
});
display.form.addEventListener('submit', () => {
raw.value = bersih(display.value);
});

/* ============ 2. SEARCH ANGGOTA GRUP DINAS ============ */
const searchInput = document.getElementById('member-search');
const clearBtn    = document.getElementById('member-search-clear');
const memberList  = document.getElementById('member-list');
const memberCards = memberList.querySelectorAll('.member-card');
const emptyState  = document.getElementById('member-empty');
const countBadge  = document.getElementById('member-count');
function refreshMemberList() {
const q = searchInput.value.trim().toLowerCase();
clearBtn.classList.toggle('d-none', q === '');
let visible = 0;
memberCards.forEach(card => {
const checked = card.querySelector('input').checked;
const match   = (card.dataset.search || '').includes(q);
const show    = checked || match;
card.classList.toggle('d-none', !show);
if (show) visible++;
});
if (emptyState) emptyState.classList.toggle('d-none', visible > 0);
const n = memberList.querySelectorAll('input:checked').length;
countBadge.textContent = n + ' dipilih';
countBadge.classList.toggle('d-none', n === 0);
}
searchInput.addEventListener('input', refreshMemberList);
memberList.addEventListener('change', refreshMemberList);
clearBtn.addEventListener('click', () => {
searchInput.value = '';
refreshMemberList();
searchInput.focus();
});
searchInput.addEventListener('keydown', (e) => {
if (e.key === 'Enter') e.preventDefault();
});

/* ============ 3. TOGGLE TIPE PENGAJUAN → SECTION ANGGOTA & ATTACHMENT ============ */
const tipeRadios         = document.querySelectorAll('input[name="tipe_pengajuan"]');
const sectionAnggota     = document.getElementById('section-anggota-dinas');
const sectionAttachment  = document.getElementById('section-attachment');
const attachmentInput    = document.getElementById('attachment');

function syncTipe() {
const terpilih = document.querySelector('input[name="tipe_pengajuan"]:checked');
document.querySelectorAll('.tipe-card').forEach(c => c.classList.remove('active'));
if (terpilih) terpilih.closest('.tipe-card').classList.add('active');

if (terpilih && terpilih.value === 'perjalanan_dinas') {
sectionAnggota.classList.add('show');
sectionAttachment.style.display = 'block';
attachmentInput.disabled = false;
attachmentInput.setAttribute('required', 'required');
} else {
sectionAnggota.classList.remove('show');
sectionAnggota.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
refreshMemberList();
sectionAttachment.style.display = 'none';
attachmentInput.removeAttribute('required');
attachmentInput.disabled = true;
resetAttachment();
}
}
/* ============ 4. 🎯 ATTACHMENT HANDLER ============ */
const dropzone      = document.getElementById('attachment-dropzone');
const placeholder   = document.getElementById('attachment-placeholder');
const preview       = document.getElementById('attachment-preview');
const filenameEl    = document.getElementById('attachment-filename');
const filesizeEl    = document.getElementById('attachment-filesize');
const removeBtn     = document.getElementById('attachment-remove');
const errorEl       = document.getElementById('attachment-error');
const maxSize       = parseInt(attachmentInput.dataset.maxSize, 10);

function formatSize(bytes) {
if (bytes < 1024) return bytes + ' B';
if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
return (bytes / 1048576).toFixed(2) + ' MB';
}

function handleFile(file) {
if (!file) { resetAttachment(); return; }
// Validasi tipe
const allowed = ['application/pdf', 'image/jpeg', 'image/jpg'];
if (!/\.(pdf|jpe?g)$/i.test(file.name) || (file.type && !allowed.includes(file.type))) {
resetAttachment('Format file harus PDF atau JPG.');
return;
}
// Validasi ukuran
if (file.size > maxSize) {
resetAttachment('Ukuran file maksimal 2MB.');
return;
}
// Tampilkan preview
attachmentInput.setCustomValidity('');
attachmentInput.removeAttribute('aria-invalid');
errorEl.textContent = '';
filenameEl.textContent = file.name;
filesizeEl.textContent = formatSize(file.size);
placeholder.style.display = 'none';
preview.classList.add('show');
dropzone.classList.add('has-file');
}

function resetAttachment(message = '') {
attachmentInput.value = '';
attachmentInput.setCustomValidity(message);
errorEl.textContent = message;
if (message) attachmentInput.setAttribute('aria-invalid', 'true');
else attachmentInput.removeAttribute('aria-invalid');
filenameEl.textContent = '-';
filesizeEl.textContent = '-';
placeholder.style.display = 'block';
preview.classList.remove('show');
dropzone.classList.remove('has-file');
}

attachmentInput.addEventListener('invalid', () => {
errorEl.textContent = attachmentInput.validity.customError
    ? attachmentInput.validationMessage
    : 'Lampiran (Laporan HRIS / Izin Perjalanan Dinas) wajib diunggah.';
attachmentInput.setAttribute('aria-invalid', 'true');
});

attachmentInput.addEventListener('change', (e) => {
const file = e.target.files[0];
handleFile(file);
});

removeBtn.addEventListener('click', (e) => {
e.stopPropagation();
resetAttachment();
attachmentInput.focus();
});

// Drag & drop
['dragenter', 'dragover'].forEach(evt => {
dropzone.addEventListener(evt, (e) => {
e.preventDefault();
e.stopPropagation();
dropzone.classList.add('dragover');
});
});
['dragleave', 'drop'].forEach(evt => {
dropzone.addEventListener(evt, (e) => {
e.preventDefault();
e.stopPropagation();
dropzone.classList.remove('dragover');
});
});
dropzone.addEventListener('drop', (e) => {
if (attachmentInput.disabled) return;
const file = e.dataTransfer.files[0];
if (file) {
// Manual set file ke input
const dt = new DataTransfer();
dt.items.add(file);
attachmentInput.files = dt.files;
handleFile(file);
}
});

// Initialize only after every upload control and event handler is ready.
tipeRadios.forEach(r => r.addEventListener('change', syncTipe));
syncTipe();
refreshMemberList();

try {
    const prefillTipe = sessionStorage.getItem('prefillTipeBs');
    const hasOldInput = @json(session()->hasOldInput());
    if (prefillTipe && !hasOldInput) {
        const radio = [...tipeRadios].find(input => input.value === prefillTipe);
        if (radio) { radio.checked = true; syncTipe(); }
    }
    sessionStorage.removeItem('prefillTipeBs');
} catch (_) {
    // The form remains usable when browser storage is unavailable.
}
});
</script>
</body>
</html>
