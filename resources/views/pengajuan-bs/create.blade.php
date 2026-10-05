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

        /* ===== Card Pilihan Tipe Pengajuan ===== */
        .tipe-card {
            display: flex;
            align-items: center;
            height: 100%;
            padding: 1rem;
            background: #fff;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 0;
        }
        .tipe-card:hover { border-color: #94a3b8; background-color: #f8fafc; }
        .tipe-card input[type="radio"] { width: 1.2em; height: 1.2em; margin-right: 0.85rem; cursor: pointer; flex-shrink: 0; }
        .tipe-card .tipe-icon { font-size: 1.6rem; margin-right: 0.85rem; flex-shrink: 0; }
        .tipe-card.active { border-color: #3b82f6; background-color: #eff6ff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12); }

        /* ===== Custom Checkbox Member ===== */
        .member-card {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .member-card:hover { border-color: #94a3b8; background-color: #f8fafc; }
        .member-card:has(input:checked) { border-color: #3b82f6; background-color: #eff6ff; }
        .member-card input[type="checkbox"] { width: 1.2em; height: 1.2em; margin-right: 1rem; cursor: pointer; flex-shrink: 0; }

        /* ===== Show/Hide Section Anggota Dinas ===== */
        #section-anggota-dinas { display: none; }
        #section-anggota-dinas.show { display: block; animation: fadeIn 0.25s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: none; }
        }
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

            <form action="{{ route('pengajuan-bs.store') }}" method="POST" id="form-pengajuan">
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

                    {{-- Kolom pencarian --}}
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
        const bersih = (v) => String(v).replace(/\D/g, '')
                                       .replace(/^0+/, '')
                                       .slice(0, 12);

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

        /* ============ 3. TOGGLE TIPE PENGAJUAN → SECTION ANGGOTA ============ */
        const tipeRadios     = document.querySelectorAll('input[name="tipe_pengajuan"]');
        const sectionAnggota = document.getElementById('section-anggota-dinas');

        function syncTipe() {
            const terpilih = document.querySelector('input[name="tipe_pengajuan"]:checked');

            // Highlight card yang aktif
            document.querySelectorAll('.tipe-card').forEach(c => c.classList.remove('active'));
            if (terpilih) terpilih.closest('.tipe-card').classList.add('active');

            if (terpilih && terpilih.value === 'perjalanan_dinas') {
                // ✅ Buka section anggota grup dinas
                sectionAnggota.classList.add('show');
            } else {
                // ✅ Tutup section + bersihkan centangan agar tidak ikut terkirim
                sectionAnggota.classList.remove('show');
                sectionAnggota.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                refreshMemberList();
            }
        }

        tipeRadios.forEach(r => r.addEventListener('change', syncTipe));

        // Inisialisasi saat load (mendukung old() setelah validasi gagal)
        syncTipe();
        refreshMemberList();

        // 🎯 PRE-FILL: Auto-pilih tipe pengajuan jika user datang dari form transaksi perjalanan dinas
const prefillTipe = sessionStorage.getItem('prefillTipeBs');
if (prefillTipe) {
    const radio = document.querySelector(`input[name="tipe_pengajuan"][value="${prefillTipe}"]`);
    if (radio) {
        radio.checked = true;
        radio.dispatchEvent(new Event('change'));
    }
    sessionStorage.removeItem('prefillTipeBs');
}
    });
    </script>
</body>
</html>
