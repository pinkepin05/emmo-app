# Kode existing: kode-pendukung-lain, bagian 4

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: resources/views/pengajuan-bs/create.blade.php

SHA-256: `590359888e70c65fdd3ffda9d577798d628d79fec5fef004986f83d2346ef3ee`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

~~~~

## FILE: resources/views/pengajuan-bs/index.blade.php

SHA-256: `39a183d56c4a760026705b5fd80ad0b04d2816c900cc8090457ea79cdfa6b975`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengajuan BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body: #f8fafc; --bg-card: #ffffff; --text-primary: #0f172a; --text-secondary: #64748b; --border-color: #e2e8f0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-primary); min-height: 100vh; }
        .page-header { padding: 2rem 0 1rem; }
        .card-custom { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .btn-nav { background-color: white; border: 1px solid var(--border-color); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; }
        .btn-primary-custom { background-color: var(--text-primary); color: white; border-radius: 8px; padding: 0.5rem 1rem; font-weight: 600; border: none; text-decoration: none; font-size: 0.875rem;}
        .table-custom { border-collapse: separate; border-spacing: 0; width: 100%; }
        .table-custom th { background-color: #f8fafc; color: var(--text-secondary); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-color); padding: 1rem; }
        .table-custom td { border-bottom: 1px solid var(--border-color); padding: 1rem; font-size: 0.95rem; color: var(--text-primary); }
        .badge-status { padding: 0.4em 0.8em; border-radius: 6px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;}
        .badge-tipe { padding: 0.4em 0.8em; border-radius: 6px; font-weight: 600; font-size: 0.72rem; }
    </style>
</head>
<body class="py-4 py-md-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1100px;">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Daftar Pengajuan BS Saya</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('dashboard.transaksi') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
                <a href="{{ route('pengajuan-bs.create') }}" class="btn-primary-custom"><i class="bi bi-plus-lg me-1"></i> Buat Pengajuan</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">✅ {{ session('success') }}</div>
        @endif

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Doc No</th>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Nominal</th>
                            <th>Nominal Cair (Finance)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $bs)
                        <tr>
                            <td class="fw-semibold">{{ $bs->document_no }}</td>
                            <td>{{ \App\Support\AppTime::format($bs->created_at) }}</td>

                            {{-- 🎯 BARU: Kolom Tipe Pengajuan --}}
                            <td>
                                @if(($bs->tipe_pengajuan ?? 'operasional_sales') === 'perjalanan_dinas')
                                    <span class="badge-tipe" style="background: #ecfdf5; color: #047857;">
                                        <i class="bi bi-airplane-fill me-1"></i>Perjalanan Dinas
                                    </span>
                                @else
                                    <span class="badge-tipe" style="background: #eff6ff; color: #1d4ed8;">
                                        <i class="bi bi-briefcase-fill me-1"></i>Operasional Sales
                                    </span>
                                @endif
                            </td>

                            <td>Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</td>

                            <td>
                                @if(($bs->nominal_approved ?? 0) > 0)
                                    <span class="fw-semibold text-success">Rp {{ number_format($bs->nominal_approved, 0, ',', '.') }}</span>
                                    @if($bs->nominal_approved < $bs->nominal_pengajuan)
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                            <i class="bi bi-scissors me-1"></i>Dipotong Rp {{ number_format($bs->nominal_pengajuan - $bs->nominal_approved, 0, ',', '.') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $colors = [
                                        'PENDING_ATASAN'  => 'bg-warning text-dark',
                                        'APPROVED_ATASAN' => 'bg-info text-white',
                                        'REJECTED_ATASAN' => 'bg-danger text-white',
                                        'DISBURSED'       => 'bg-success text-white',
                                        'SETTLED'         => 'bg-secondary text-white'
                                    ];
                                @endphp
                                <span class="badge-status {{ $colors[$bs->status] ?? 'bg-secondary text-white' }}">
                                    {{ str_replace('_', ' ', $bs->status) }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('pengajuan-bs.show', $bs->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                                    <i class="bi bi-clock-history"></i> Riwayat &amp; Transaksi
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Belum ada pengajuan BS.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/pengajuan-bs/show.blade.php

SHA-256: `35ffa5d7a8633817c6306a53c19fe23897ab7cc2c703e863f09be4f09e80f087`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail & Riwayat BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body: #f8fafc; --border-color: #e2e8f0; --text-primary: #0f172a; --text-secondary: #64748b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-primary); }
        .card-custom { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .btn-nav { background-color: white; border: 1px solid var(--border-color); color: var(--text-secondary); font-weight: 500; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; }

        /* Timeline Styles */
        .timeline { position: relative; padding: 1rem 0; }
        .timeline::before { content: ''; position: absolute; left: 20px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
        .timeline-item { position: relative; padding-left: 60px; margin-bottom: 2rem; }
        .timeline-icon { position: absolute; left: 0; top: 0; width: 42px; height: 42px; border-radius: 50%; background: #fff; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; z-index: 1; }

        .icon-created { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
        .icon-approved { border-color: #10b981; color: #10b981; background: #ecfdf5; }
        .icon-rejected { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
        .icon-disbursed { border-color: #f59e0b; color: #f59e0b; background: #fffbeb; }
        .icon-settled { border-color: #6366f1; color: #6366f1; background: #eef2ff; }
        .icon-default { border-color: #64748b; color: #64748b; background: #f8fafc; }

        .timeline-content { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem 1.25rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .timeline-title { font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem; }
        .timeline-meta { font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.5rem; }
        .timeline-desc { font-size: 0.9rem; color: var(--text-primary); }
        .timeline-nominal { font-weight: 700; color: #059669; font-size: 0.95rem; margin-top: 0.25rem; }

        /* Member Badge */
        .member-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: #eef2ff;
            color: #4f46e5;
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #c7d2fe;
        }
        .member-badge i { font-size: 0.85rem; }

        /* Tipe Badge */
        .badge-tipe { padding: 0.45em 0.85em; border-radius: 6px; font-weight: 600; font-size: 0.75rem; }
        .report-amount { font-size: 1.1rem; font-weight: 700; }
        .report-table th, .report-table td { vertical-align: top; white-space: nowrap; }
        .report-table .detail-cell { white-space: normal; min-width: 210px; }
        .report-table details { font-size: 0.85rem; }
        .report-table summary { color: #2563eb; cursor: pointer; }
        .report-table .detail-row { display: grid; grid-template-columns: 1fr auto; gap: 0.75rem; border-top: 1px solid var(--border-color); padding: 0.35rem 0; }
    </style>
</head>
<body class="py-5">
    @include('partials.application-time')
    <div class="container" style="max-width: 1100px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Riwayat Pengajuan BS</h3>
            <a href="{{ route('pengajuan-bs.index') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        {{-- Info Header BS --}}
        <div class="card-custom mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">{{ $bs->document_no }}</h5>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <span class="badge bg-secondary">{{ $bs->periode }}</span>

                        {{-- 🎯 BARU: Badge Tipe Pengajuan --}}
                        @if(($bs->tipe_pengajuan ?? 'operasional_sales') === 'perjalanan_dinas')
                            <span class="badge-tipe" style="background: #ecfdf5; color: #047857;">
                                <i class="bi bi-airplane-fill me-1"></i>Perjalanan Dinas
                            </span>
                        @else
                            <span class="badge-tipe" style="background: #eff6ff; color: #1d4ed8;">
                                <i class="bi bi-briefcase-fill me-1"></i>Operasional Sales
                            </span>
                        @endif

                        <span class="badge
                            @if($bs->status == 'DISBURSED') bg-success
                            @elseif($bs->status == 'REJECTED_ATASAN') bg-danger
                            @elseif($bs->status == 'PENDING_ATASAN') bg-warning text-dark
                            @elseif($bs->status == 'SETTLED') bg-dark
                            @else bg-info @endif">
                            {{ str_replace('_', ' ', $bs->status) }}
                        </span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted small">Diajukan oleh</div>
                    <strong>{{ $bs->user->nama ?? '-' }}</strong>
                    <div class="text-muted small">Diajukan {{ \App\Support\AppTime::format($bs->created_at) }}</div>
                </div>
            </div>
            <hr>
            <div class="row text-center">
                <div class="col-4">
                    <div class="text-muted small">Nominal Pengajuan</div>
                    <strong>Rp {{ number_format($bs->nominal_pengajuan, 0, ',', '.') }}</strong>
                </div>
                <div class="col-4">
                    <div class="text-muted small">Saldo BS Gabungan Saat Pengajuan</div>
                    <strong>Rp {{ number_format($bs->nominal_sisa_bs_snapshot, 0, ',', '.') }}</strong>
                </div>
                <div class="col-4">
                    <div class="text-muted small">Nominal Cair</div>
                    <strong class="text-success">Rp {{ number_format($bs->nominal_approved, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($bs->description)
                <div class="mt-3 p-3 bg-light rounded">
                    <strong class="small text-muted d-block mb-1">📝 Deskripsi / Keperluan:</strong>
                    {{ $bs->description }}
                </div>
            @endif

            {{-- 🎯 ANGGOTA GRUP DINAS (hanya relevan untuk Perjalanan Dinas) --}}
            @if($bs->members && $bs->members->count() > 0)
                <div class="mt-3 p-3 rounded" style="background: linear-gradient(135deg, #f0f4ff 0%, #e8efff 100%); border: 1px dashed #a5b4fc;">
                    <strong class="small d-block mb-2" style="color: #4338ca;">
                        <i class="bi bi-people-fill me-1"></i> Anggota Grup Dinas:
                    </strong>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($bs->members as $m)
                            <span class="member-badge">
                                <i class="bi bi-person-badge"></i>
                                {{ $m->nama ?? $m->name ?? 'User #' . $m->id }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 🎯 INFO SETTLEMENT --}}
            @if($bs->status === 'SETTLED')
                <div class="mt-3 p-3 rounded" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #86efac;">
                    <strong class="small d-block mb-1" style="color: #166534;">
                        <i class="bi bi-check-circle-fill me-1"></i> Status Penyelesaian:
                    </strong>
                    <div class="small" style="color: #15803d;">
                        @if($bs->nominal_pengembalian > 0)
                            ✅ User menyetor kembali <strong>Rp {{ number_format($bs->nominal_pengembalian, 0, ',', '.') }}</strong> ke kas perusahaan
                        @elseif($bs->nominal_reimburse > 0)
                            ✅ Kas me-reimburse <strong>Rp {{ number_format($bs->nominal_reimburse, 0, ',', '.') }}</strong> ke user
                        @else
                            ✅ Lunas tanpa selisih
                        @endif
                        <span class="text-muted ms-1">• Ditutup pada {{ \App\Support\AppTime::format($bs->settled_at) }} (Periode {{ $bs->settled_periode }})</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Timeline Riwayat --}}
        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Jejak Audit (Audit Trail)</h5>
        <div class="card-custom">
            <div class="timeline">
                @forelse($logs as $log)
                    @php
                        $iconClass = 'icon-default';
                        $icon = 'bi-circle';
                        if ($log->activity_code == 'CREATED') { $iconClass = 'icon-created'; $icon = 'bi-pencil-square'; }
                        elseif ($log->activity_code == 'APPROVED_ATASAN') { $iconClass = 'icon-approved'; $icon = 'bi-check-circle'; }
                        elseif ($log->activity_code == 'REJECTED_ATASAN') { $iconClass = 'icon-rejected'; $icon = 'bi-x-circle'; }
                        elseif ($log->activity_code == 'DISBURSED') { $iconClass = 'icon-disbursed'; $icon = 'bi-cash-coin'; }
                        elseif ($log->activity_code == 'SETTLED') { $iconClass = 'icon-settled'; $icon = 'bi-archive-fill'; }
                    @endphp

                    <div class="timeline-item">
                        <div class="timeline-icon {{ $iconClass }}">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">{{ $log->activity_name }}</div>
                            <div class="timeline-meta">
                                Oleh: <strong>{{ $log->actor->nama ?? 'System' }}</strong>
                                &bull; {{ \App\Support\AppTime::format($log->created_at) }}
                            </div>
                            @if($log->description)
                                <div class="timeline-desc">{{ $log->description }}</div>
                            @endif
                            @if($log->nominal)
                                <div class="timeline-nominal">
                                    <i class="bi bi-cash-stack me-1"></i> Rp {{ number_format($log->nominal, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0">Belum ada riwayat.</p>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/periode/index.blade.php

SHA-256: `344963bae633c3006df30f5999e63133abf32f22cbfdb913f5de700b697d520c`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Periode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-body:#f8fafc; --border-color:#e2e8f0; --text-primary:#0f172a; --text-secondary:#64748b; }
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg-body);color:var(--text-primary);}
        .card-custom{background:#fff;border:1px solid var(--border-color);border-radius:16px;overflow:hidden;}
        .table-custom th{background:#f8fafc;color:var(--text-secondary);font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;padding:1rem;border-bottom:1px solid var(--border-color);}
        .table-custom td{padding:1rem;border-bottom:1px solid var(--border-color);vertical-align:middle;}
        .btn-nav{background:#fff;border:1px solid var(--border-color);color:var(--text-secondary);padding:.5rem 1rem;border-radius:8px;text-decoration:none;font-size:.875rem;}
        .btn-nav:hover{color:var(--text-primary);background:#f1f5f9;}

        /* ✅ FIX 1: semua tombol aksi pakai inline-flex (bukan inline),
           agar padding vertikal dihitung → tidak terpotong/overlap lagi,
           dan ikon rata tengah dengan teks. */
        .btn-history,
        .btn-close-periode,
        .btn-reopen-periode{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.4rem;
            border-radius:8px;
            padding:.45rem .9rem;
            font-weight:600;
            font-size:.8rem;
            line-height:1.2;
            white-space:nowrap;
        }
        .btn-history{background:#fff;border:1px solid var(--border-color);color:var(--text-secondary);text-decoration:none;}
        .btn-history:hover{color:var(--text-primary);background:#f1f5f9;}
        .btn-close-periode{background:#ef4444;color:#fff;border:none;}
        .btn-close-periode:hover{background:#dc2626;}
        .btn-reopen-periode{background:#f59e0b;color:#fff;border:none;}
        .btn-reopen-periode:hover{background:#d97706;}

        /* ✅ FIX 2: wrapper aksi — satu baris, rata kanan, spacing konsisten,
           vertikal center. Form di dalamnya tidak menambah margin. */
        .action-wrap{display:flex;align-items:center;justify-content:flex-end;gap:.5rem;flex-wrap:nowrap;}
        .action-wrap form{margin:0;}
        .lock-note{display:inline-flex;align-items:center;gap:.35rem;white-space:nowrap;}

        .badge-status{padding:.4em .8em;border-radius:6px;font-weight:600;font-size:.75rem;text-transform:uppercase;}
        .badge-current{background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;}
        .audit-note{font-size:.72rem;color:var(--text-secondary);}
    </style>
</head>
<body class="py-5">
    @include('partials.application-time')
<div class="container" style="max-width:950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Manajemen Periode & Tutup Buku</h3>
        <a href="{{ route('dashboard.master_data') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div class="small">
            <strong>Mode Auto-Roll Aktif:</strong> Sistem otomatis membuka periode baru saat bulan di dunia nyata berganti.
            Finance dapat menutup periode lama setelah seluruh draft diselesaikan dan saldo diperiksa. Sisa saldo BS diteruskan ke periode berikutnya.
            Periode CLOSED dapat <strong>dibuka kembali</strong> selama periode sesudahnya belum memiliki transaksi.
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success border-0 shadow-sm" style="border-radius:12px;">✅ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger border-0 shadow-sm" style="border-radius:12px;"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="card-custom">
        {{-- ✅ FIX 3: aman di layar sempit, tombol tidak wrap acak --}}
        <div class="table-responsive">
            <table class="table-custom mb-0 w-100">
                <thead>
                <tr>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Ditutup Pada</th>
                    <th class="text-end pe-4" style="white-space:nowrap;">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($periods as $p)
                    <tr>
                        <td class="fw-semibold">
                            {{ $p->periode }}
                            @if($p->periode === $currentMonth)
                                <span class="badge-current badge ms-1" style="font-size:.65rem;">BULAN SAAT INI</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-status {{ $p->status === 'OPEN' ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $p->status }}</span>
                            @if($p->reopened_at)
                                <div class="audit-note mt-1"><i class="bi bi-unlock-fill"></i> Dibuka kembali: {{ \App\Support\AppTime::format($p->reopened_at) }} — {{ $p->reopen_reason }}</div>
                            @endif
                        </td>
                        <td class="text-muted small">
                            @if($p->closed_at)
                                {{ \App\Support\AppTime::format($p->closed_at) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="pe-4">
                            {{-- ✅ FIX 4: Riwayat + Buka Kembali sejajar satu baris --}}
                            <div class="action-wrap">
                                <a href="{{ route('periode.show', $p->periode) }}" class="btn-history">
                                    <i class="bi bi-clock-history"></i> Riwayat
                                </a>

                                @if($p->status === 'OPEN')
                                    {{-- 🎯 GUARD UI: Kunci tombol jika bulan riil belum melewati periode ini --}}
                                    @if($currentMonth <= $p->periode)
                                        <span class="lock-note text-muted small" title="Bulan di dunia nyata belum melewati periode ini">
                                            <i class="bi bi-lock-fill"></i> Terkunci
                                        </span>
                                    @else
                                        <form action="{{ route('periode.close', $p->id) }}" method="POST"
                                              onsubmit="return confirm('Tutup buku periode {{ $p->periode }}? Transaksi periode ini akan dibekukan.');">
                                            @csrf
                                            <button class="btn-close-periode"><i class="bi bi-lock-fill"></i> Tutup Buku</button>
                                        </form>
                                    @endif
                                @else
                                    <button type="button" class="btn-reopen-periode btn-reopen"
                                            data-bs-toggle="modal" data-bs-target="#reopenModal"
                                            data-action="{{ route('periode.reopen', $p->id) }}"
                                            data-periode="{{ $p->periode }}">
                                        <i class="bi bi-unlock-fill"></i> Buka Kembali
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada periode.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🎯 MODAL REOPEN (wajib alasan → audit) --}}
<div class="modal fade" id="reopenModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="reopenForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Buka Kembali Periode <span id="reopenLabel" class="fw-bold"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small mb-3">
                    Periode akan kembali menerima transaksi. Sistem menolak reopen bila periode sesudahnya
                    sudah berisi transaksi (menjaga integritas rollover saldo BS).
                </div>
                <label class="form-label small fw-semibold">Alasan (wajib — tercatat sebagai audit)</label>
                <input type="text" name="reason" class="form-control" required minlength="5"
                       placeholder="Contoh: salah klik tutup buku / koreksi transaksi">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-warning text-white fw-semibold">Buka Kembali</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-reopen').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('reopenLabel').textContent = btn.dataset.periode;
        document.getElementById('reopenForm').action = btn.dataset.action;
    });
});
</script>
</body>
</html>

~~~~

## FILE: resources/views/periode/show.blade.php

SHA-256: `9c465bdd04bc0a89dda1b675f37e93ce1f24b98950df0d2b66d95546e5db134c`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Periode {{ $periode }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{background:#f8fafc;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#0f172a;}
        .card-x{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;}
        .table-x td,.table-x th{padding:.6rem .9rem;font-size:.85rem;border-bottom:1px solid #eef2f7;vertical-align:middle;}
        .table-x tbody tr:last-child td{border-bottom:none;}
        .badge-status{padding:.4em .8em;border-radius:6px;font-weight:600;font-size:.75rem;}
        .sum-card{font-size:.78rem;color:#64748b;}
        .sum-card b{display:block;font-size:1.05rem;color:#0f172a;margin-top:.15rem;}
        .btn-nav{background:#fff;border:1px solid #e2e8f0;color:#64748b;padding:.5rem 1rem;border-radius:8px;text-decoration:none;font-size:.875rem;}
    </style>
</head>
<body class="py-4">
    @include('partials.application-time')
<div class="container" style="max-width:1050px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clock-history"></i> Riwayat Periode {{ $periode }}
            <span class="badge-status text-white ms-2 {{ $status === 'OPEN' ? 'bg-success' : 'bg-secondary' }}">{{ $status }}</span>
        </h4>
        <a href="{{ route('periode.index') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    @if($row && $row->closed_at)
        <div class="alert alert-secondary small" style="border-radius:12px;">
            <i class="bi bi-lock-fill me-1"></i> Ditutup pada {{ \App\Support\AppTime::format($row->closed_at) }}
            @if($closedByName) oleh <strong>{{ $closedByName }}</strong> @endif
            @if($row->reopened_at)
                · <i class="bi bi-unlock-fill me-1"></i> Dibuka kembali {{ \App\Support\AppTime::format($row->reopened_at) }} — {{ $row->reopen_reason }}
            @endif
        </div>
    @endif

    {{-- RINGKASAN NOMINAL PER PERIODE --}}
    <div class="row g-3 mb-4">
        <div class="col"><div class="card-x p-3 sum-card">BS Disetujui ({{ $bs->count() }})<b>Rp {{ number_format($totals['bsApproved'], 0, ',', '.') }}</b></div></div>
        <div class="col"><div class="card-x p-3 sum-card">Operasional ({{ $operasional->count() }})<b>Rp {{ number_format($totals['operasional'], 0, ',', '.') }}</b></div></div>
        <div class="col"><div class="card-x p-3 sum-card">Perjalanan Dinas ({{ $perdin->count() }})<b>Rp {{ number_format($totals['perdin'], 0, ',', '.') }}</b></div></div>
        <div class="col"><div class="card-x p-3 sum-card">Event Dalam ({{ $eventDalam->count() }})<b>Rp {{ number_format($totals['eventDalam'], 0, ',', '.') }}</b></div></div>
        <div class="col"><div class="card-x p-3 sum-card">Event Luar ({{ $eventLuar->count() }})<b>Rp {{ number_format($totals['eventLuar'], 0, ',', '.') }}</b></div></div>
    </div>

    @php
        $sections = [
            ['Pengajuan Bon Sementara (BS)', $bs, 'pengajuan-bs.show', 'nominal_approved'],
            ['Operasional Area Sales', $operasional, 'transaksi_operasional.show', null],
            ['Perjalanan Dinas', $perdin, 'transaksi_perjalanan_dinas.show', null],
            ['Event Marketing Dalam Kota', $eventDalam, 'perongkosan_event_dalam_kota.show', 'nominal'],
            ['Event Marketing Luar Kota', $eventLuar, 'perongkosan_event_luar_kota.show', 'nominal'],
        ];
    @endphp

    @foreach($sections as [$title, $rows, $routeShow, $nominalCol])
        <div class="card-x mb-4">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <strong>{{ $title }}</strong>
                <span class="text-muted small">{{ $rows->count() }} transaksi</span>
            </div>
            @if($rows->isEmpty())
                <div class="p-3 text-muted small">Tidak ada transaksi pada periode ini.</div>
            @else
                <div class="table-responsive">
                    <table class="table-x w-100 mb-0">
                        <thead>
                            <tr>
                                <th>ID</th><th>Tanggal</th><th>User</th><th>Deskripsi</th>
                                @if($nominalCol)<th class="text-end">Nominal</th>@endif
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $r)
                            <tr>
                                <td><code>{{ $r->document_no ?? $r->transaksi_id }}</code></td>
                                <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                                <td>{{ $r->user_nama ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($r->description ?? '-', 60) }}</td>
                                @if($nominalCol)
                                    <td class="text-end">{{ number_format($r->{$nominalCol} ?? 0, 0, ',', '.') }}</td>
                                @endif
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route($routeShow, $r->id) }}">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach
</div>
</body>
</html>

~~~~

## FILE: resources/views/perongkosan_event_dalam_kota/create.blade.php

SHA-256: `0ae0a40b333797c56b38bbddedb0edd2dbe858d116bae678f3a9d1926cc0a1bf`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

~~~~

## FILE: resources/views/perongkosan_event_dalam_kota/edit.blade.php

SHA-256: `4ebba776470fc47f704f0bb681cacb6dfe885ae9ed0d093045c732ede973a512`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

~~~~

## FILE: resources/views/perongkosan_event_dalam_kota/index.blade.php

SHA-256: `9d51737a7e8e41127e0eefcb7a580d0beb55c9da6c631c5c7219993a7b5951c4`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Event Dalam Kota</title>
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

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
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

        .btn-light-header {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .btn-light-header:hover { 
            background: white; 
            color: var(--primary-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
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
            flex-wrap: wrap;
            gap: 1rem;
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

        .count-badge {
            background: #dbeafe;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
        }

        .filter-select {
            width: auto;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--text-primary);
            padding: 0.5rem 2rem 0.5rem 0.75rem;
        }
        
        .filter-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-success-modern {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .btn-success-modern:hover { 
            background: #047857; 
            color: white; 
        }

        .table-modern { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0; 
        }
        
        .table-modern thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--border-color);
            padding: 0.875rem 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        
        .table-modern tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            color: var(--text-primary);
            font-size: 0.875rem;
        }
        
        .table-modern tbody tr:hover { 
            background: #f8fafc; 
        }
        
        .table-modern tbody tr:last-child td { 
            border-bottom: none; 
        }

        .code-badge {
            background: #dbeafe;
            color: var(--primary-color);
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .avatar {
            width: 32px; 
            height: 32px; 
            border-radius: 50%;
            background: var(--primary-color);
            color: white; 
            font-weight: 600; 
            font-size: 0.8rem;
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
        }

        .desc-cell {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nominal-cell { 
            font-weight: 600; 
            color: var(--text-primary); 
            white-space: nowrap; 
        }

        .foto-chip {
            width: 36px; 
            height: 36px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            border: 1px solid;
            text-decoration: none;
            margin: 0 2px;
            color: var(--text-secondary);
            border-color: var(--border-color);
            background: white;
        }
        
        .foto-chip:hover { 
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-action {
            border: none;
            border-radius: 6px;
            padding: 0.4rem 0.75rem;
            font-weight: 500;
            font-size: 0.8rem;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
        }
        
        .btn-action.edit {
            background: var(--warning-color);
        }
        
        .btn-action.edit:hover { 
            background: #b45309; 
            color: white; 
        }
        
        .btn-action.hapus {
            background: var(--danger-color);
        }
        
        .btn-action.hapus:hover { 
            background: #b91c1c; 
            color: white; 
        }

        .empty-state { 
            text-align: center; 
            padding: 3rem 1rem; 
        }
        
        .empty-state .icon-circle {
            width: 80px; 
            height: 80px; 
            border-radius: 50%;
            background: #f1f5f9;
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .empty-state .icon-circle i {
            font-size: 2rem;
            color: var(--text-secondary);
        }
        
        .empty-state h6 { 
            font-weight: 600; 
            color: var(--text-primary); 
        }

        .pagination { gap: 4px; }
        
        .page-link {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-weight: 500;
            margin: 0;
            padding: 0.5rem 0.75rem;
        }
        
        .page-link:hover { 
            background: var(--bg-color); 
            color: var(--text-primary); 
        }
        
        .page-item.active .page-link {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .page-item.disabled .page-link { 
            color: var(--text-secondary); 
            background: transparent; 
        }

        .alert-custom {
            border-radius: 6px; 
            border: none;
            padding: 1rem 1.25rem;
        }

        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.25rem; }
            .desc-cell { max-width: 150px; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')
    <div class="page-header">
        <div class="container" style="max-width: 1200px;">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1><i class="bi bi-briefcase me-2"></i>Event Dalam Kota</h1>
                    <p class="mb-0">Daftar seluruh transaksi perongkosan event dalam kota</p>
                </div>
                <div class="col-lg-6 d-flex justify-content-lg-end gap-2 flex-wrap mt-3 mt-lg-0">
                    <a href="{{ route('perongkosan_event_dalam_kota.create') }}" class="btn btn-light-header btn-modern">
                        <i class="bi bi-plus-lg"></i> Tambah
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
            <div class="alert alert-success alert-custom mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle me-2"></i>
                    <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                </div>
            </div>
        @endif

        <div class="card-modern">
            <div class="card-header-modern">
                <h5>
                    <i class="bi bi-list-check"></i> Daftar Transaksi Event Dalam Kota
                    <span class="count-badge">{{ $events->total() }} Transaksi</span>
                </h5>

                <form method="GET" action="{{ route('perongkosan_event_dalam_kota.export') }}" class="d-flex gap-2 align-items-center flex-wrap">
                    <select name="periode" class="form-select filter-select">
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
                                <th>ID Transaksi</th>
                                <th>Tanggal</th>
                                <th>User (Karyawan)</th>
                                <th>Deskripsi</th>
                                <th>Nominal (Rp)</th>
                                <th class="text-center">Foto Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                            <tr>
                                <td><span class="code-badge">{{ $event->transaksi_id }}</span></td>
                                <td><i class="bi bi-calendar3 me-2 text-muted"></i>{{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ strtoupper(substr($event->user->name ?? $event->user->nama ?? 'U', 0, 1)) }}</span>
                                        <span class="fw-medium">{{ $event->user->name ?? $event->user->nama ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td><div class="desc-cell" title="{{ $event->description }}">{{ $event->description }}</div></td>
                                <td class="nominal-cell">Rp {{ number_format($event->nominal, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        @if($event->foto_event_1)
                                            <a href="{{ asset('storage/' . $event->foto_event_1) }}" target="_blank" class="foto-chip" title="Lihat Foto 1">
                                                <i class="bi bi-camera"></i> 1
                                            </a>
                                        @endif
                                        @if($event->foto_event_2)
                                            <a href="{{ asset('storage/' . $event->foto_event_2) }}" target="_blank" class="foto-chip" title="Lihat Foto 2">
                                                <i class="bi bi-camera"></i> 2
                                            </a>
                                        @endif
                                        @if($event->foto_event_3)
                                            <a href="{{ asset('storage/' . $event->foto_event_3) }}" target="_blank" class="foto-chip" title="Lihat Foto 3">
                                                <i class="bi bi-camera"></i> 3
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('perongkosan_event_dalam_kota.show', $event->id) }}" class="btn-action edit" style="background: var(--primary-color);">
    <i class="bi bi-eye"></i> Lihat Detail
</a>
                                        <form action="{{ route('perongkosan_event_dalam_kota.destroy', $event->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn-action hapus" onclick="return confirm('Yakin hapus data ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="icon-circle"><i class="bi bi-inbox"></i></div>
                                        <h6>Belum ada data transaksi</h6>
                                        <p class="text-muted mb-3">Mulai buat transaksi event dalam kota pertama Anda.</p>
                                        <a href="{{ route('perongkosan_event_dalam_kota.create') }}" class="btn btn-success-modern btn-modern">
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

            @if($events->hasPages())
            <div class="card-footer bg-white border-0 d-flex justify-content-end px-4 pb-4 pt-3">
                {{ $events->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>

~~~~

## FILE: resources/views/perongkosan_event_dalam_kota/show.blade.php

SHA-256: `0140edd2937ff65510747202bc4bd6a32c7d3d04b4f9110f3e39019c9e1401eb`

~~~~php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

~~~~
