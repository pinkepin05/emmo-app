<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Transaksi Operasional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- 🎯 TAMBAHAN: Flatpickr CSS untuk Datepicker Modern -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            --primary-color: #667eea;
            --success-color: #11998e;
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

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }

        .page-header h1 { font-weight: 800; margin: 0; font-size: 2rem; }
        .page-header p { margin: 0.5rem 0 0 0; opacity: 0.9; }

        .card-modern {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: visible;
            margin-bottom: 2rem;
        }

        .card-header-modern {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 20px 20px 0 0;
        }

        .card-header-modern.success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            border-bottom-color: rgba(17, 153, 142, 0.2);
        }

        .card-header-modern.sticky-detail {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: linear-gradient(135deg, #e6f7f4 0%, #eafcf0 100%);
            transition: box-shadow 0.25s ease, border-radius 0.25s ease;
        }
        .card-header-modern.sticky-detail.is-stuck {
            box-shadow: 0 8px 24px rgba(17, 153, 142, 0.22);
            border-radius: 0;
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

        .card-header-modern.success h5 i {
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-label { font-weight: 600; color: #4a5568; margin-bottom: 0.5rem; font-size: 0.9rem; }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-control:disabled { background: #f7fafc; border-color: #e2e8f0; }

        .input-group-text {
            background: white;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .input-with-icon .form-control { border-radius: 0 12px 12px 0; }

        .btn-modern {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            border-radius: 15px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .btn-primary-modern:hover,
        .btn-primary-modern:focus,
        .btn-primary-modern:active {
            background: var(--primary-gradient);
            color: white;
            transform: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
            transition: all 0.3s ease;
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.5);
            color: white;
        }

        .btn-danger-modern {
            background: var(--danger-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(238, 9, 121, 0.4);
            transition: all 0.3s ease;
        }

        .btn-danger-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(238, 9, 121, 0.5);
            color: white;
        }

        .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; }

        .table-modern thead th {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-bottom: 2px solid #e2e8f0;
            padding: 1rem;
            font-weight: 700;
            color: #2d3748;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f0f4f8;
            vertical-align: middle;
        }

        .table-modern tbody tr:hover { background: rgba(102, 126, 234, 0.02); }

        .total-badge {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            border: 2px solid var(--success-color);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            text-align: right;
        }

        .total-badge .amount {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .info-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-left: 4px solid var(--primary-color);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-top: 1rem;
        }

        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.2), transparent);
            margin: 1.5rem 0;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-slide-in { animation: slideIn 0.5s ease; }

        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.5rem; }
            .card-header-modern { flex-direction: column; gap: 1rem; text-align: center; }
            .total-badge { text-align: center; margin-bottom: 1rem; width: 100%; }
            .total-badge .amount { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    @include('partials.application-time')
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="bi bi-receipt me-3"></i>Transaksi Operasional</h1>
                    <p class="mb-0">Input dan kelola transaksi area sales dengan mudah</p>
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

        <form action="{{ route('transaksi_operasional.store') }}" method="POST" id="form-operasional">
            @csrf

            <!-- Card 1: Data Header -->
            <div class="card-modern animate-slide-in">
                <div class="card-header-modern">
                    <h5><i class="bi bi-clipboard-data"></i> Data Header Transaksi</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary">Step 1 dari 2</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-calendar-event me-2 text-primary"></i>Tanggal Transaksi
                            </label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <!-- 🎯 UBAH: type="text" agar Flatpickr bisa mengambil alih -->
                                <input type="text" id="tanggal_picker" name="tanggal" class="form-control" value="{{ old('tanggal', \App\Support\AppTime::now()->toDateString()) }}" placeholder="Pilih tanggal..." required autocomplete="off">
                            </div>
                            <div id="periodWarning" class="alert alert-danger d-none mt-2" style="border-radius:12px;">
                                <i class="bi bi-lock-fill me-2"></i><span id="periodWarningText"></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-person-badge me-2 text-primary"></i>User (Karyawan)
                            </label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="text" class="form-control bg-light" value="{{ Auth::user()->nama ?? Auth::user()->name }} (NIK: {{ Auth::user()->NIK ?? '-' }})" readonly disabled>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Transaksi otomatis dicatat atas nama akun Anda</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-file-text me-2 text-primary"></i>Deskripsi (Tujuan) <span class="text-muted fw-normal small">(Opsional)</span>
                            </label>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" name="description" class="form-control"
                                       placeholder="Contoh: Kunjungan Toko ABC"
                                       value="{{ old('description') }}">
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Kosongkan jika tidak perlu.</small>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                   <div class="mt-4">
    @include('partials.transaction-funding', ['fundingType' => 'operasional_sales'])
    <div id="bs-warning-info" class="alert alert-warning mt-3 d-none">Total rincian biaya melebihi saldo BS gabungan yang tersedia.</div>
</div>
</div>
</div>

            <!-- Card 2: Rincian Biaya (dengan sticky header) -->
            <div class="card-modern animate-slide-in" style="animation-delay: 0.1s">
                <div id="sticky-sentinel" style="height: 0;"></div>

                <div class="card-header-modern success sticky-detail" id="detail-header">
                    <h5><i class="bi bi-receipt-cutoff"></i> Rincian Biaya (Detail)</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="total-badge mb-0">
                            <small class="text-muted d-block">Total Rincian</small>
                            <span id="total-rincian" class="amount">Rp 0</span>
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
                                    <th width="35%"><i class="bi bi-chat-left-text me-2"></i>Remark (Keterangan)</th>
                                    <th width="25%"><i class="bi bi-currency-dollar me-2"></i>Nominal (Rp) <span class="text-danger">*</span></th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $oldDetails = array_values(old('details', [[]])); @endphp
                                @foreach($oldDetails as $idx => $oldDetail)
                                <tr id="baris-{{ $idx }}" class="animate-slide-in">
                                    <td>
                                        <select name="details[{{ $idx }}][cost_category_id]" class="form-select" required>
                                            <option value="">-- Pilih Cost Category --</option>
                                            @foreach($cost_categories as $cc)
                                                <option value="{{ $cc->id }}" @selected(($oldDetail['cost_category_id'] ?? '') == $cc->id)>{{ $cc->name ?? $cc->nama ?? $cc->code }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $idx }}][remark]" class="form-control" placeholder="Misal: Bensin Motor" value="{{ $oldDetail['remark'] ?? '' }}" required>
                                    </td>
                                    <td>
                                        @php $oldNominal = $oldDetail['nominal'] ?? ''; @endphp
                                        <div class="input-group input-with-icon">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" inputmode="numeric" autocomplete="off"
                                                   class="form-control nominal-display" placeholder="0" value="{{ $oldNominal !== '' ? number_format((float) $oldNominal, 0, ',', '.') : '' }}" required>
                                            <input type="hidden" name="details[{{ $idx }}][nominal]" class="nominal-hidden" value="{{ $oldNominal }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus" data-id="{{ $idx }}" title="Hapus baris">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <p class="text-muted small">Draft dapat diedit dan belum mengurangi saldo BS. Konfirmasi membukukan pemakaian BS dan mengunci transaksi.</p>
            <div class="d-grid d-sm-flex gap-2 animate-slide-in" style="animation-delay: 0.2s">
                <button type="submit" name="save_action" value="draft" class="btn btn-outline-secondary btn-lg py-3 flex-fill">
                    <i class="bi bi-save me-2 fs-5"></i> Simpan Draft
                </button>
                <button type="submit" name="save_action" value="confirm" class="btn btn-primary-modern btn-lg py-3 flex-fill">
                    <i class="bi bi-check-circle me-2 fs-5"></i> Simpan &amp; Konfirmasi
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- 🎯 TAMBAHAN: Flatpickr JS & Locale Indonesia -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

    <script>
        $(document).ready(function() {

            // ============================================================
            // 🎯 1. STICKY HEADER DETECTION
            // ============================================================
            const sentinel     = document.getElementById('sticky-sentinel');
            const detailHeader = document.getElementById('detail-header');
            if (sentinel && detailHeader && 'IntersectionObserver' in window) {
                new IntersectionObserver((entries) => {
                    detailHeader.classList.toggle('is-stuck', !entries[0].isIntersecting);
                }, { threshold: 1 }).observe(sentinel);
            }

            // ============================================================
            // 🎯 2. FORMAT & LIMIT NOMINAL
            // ============================================================
            const MAX_DIGITS   = 9;
            const formatRibuan = d => d.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const bersih       = v => String(v).replace(/\D/g, '').replace(/^0+/, '').slice(0, MAX_DIGITS);

            function syncNominal(el) {
                const caret       = el.selectionStart || 0;
                const digitBefore = el.value.slice(0, caret).replace(/\D/g, '').length;
                const digits      = bersih(el.value);

                el.value = digits ? formatRibuan(digits) : '';

                let count = 0, pos = 0;
                for (let i = 0; i < el.value.length; i++) {
                    if (count >= digitBefore) break;
                    if (/\d/.test(el.value[i])) count++;
                    pos = i + 1;
                }
                el.setSelectionRange(pos, pos);

                const hidden = el.closest('td').querySelector('.nominal-hidden');
                if (hidden) hidden.value = digits;
            }

            $(document).on('input', '.nominal-display', function() {
                syncNominal(this);
                hitungTotal();
            });

            // ============================================================
            // 🎯 3. LOGIC TOTAL & WARNING BS
            // ============================================================
            function getSisaSaldoBs() {
    const value = document.getElementById('bs-saldo-info')?.dataset.balance;
    return value === undefined || value === '' ? null : Number(value);
}

            function hitungTotal() {
                let total = 0;
                $('.nominal-hidden').each(function() {
                    total += parseFloat(this.value) || 0;
                });
                $('#total-rincian').html('Rp ' + new Intl.NumberFormat('id-ID').format(total));

                const sisa = getSisaSaldoBs();
                if (sisa !== null && total > sisa) {
                    $('#bs-warning-info').removeClass('d-none');
                    $('#total-rincian').css('background', 'linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)')
                                       .css('-webkit-background-clip', 'text')
                                       .css('-webkit-text-fill-color', 'transparent');
                } else {
                    $('#bs-warning-info').addClass('d-none');
                    $('#total-rincian').css('background', 'var(--success-gradient)')
                                       .css('-webkit-background-clip', 'text')
                                       .css('-webkit-text-fill-color', 'transparent');
                }
                return total;
            }

            document.addEventListener('bs-balance-updated', hitungTotal);

            // ============================================================
            // 🎯 4. DYNAMIC ROWS
            // ============================================================
            let rowIndex = {{ count($oldDetails) }};

            const costCategoryOptions = `
                <option value="">-- Pilih Cost Category --</option>
                @foreach($cost_categories as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->name ?? $cc->nama ?? $cc->code }}</option>
                @endforeach
            `;

            function reindexRows() {
                $('#tabel-detail tbody tr').each(function(i) {
                    $(this).attr('id', 'baris-' + i);
                    $(this).find('select').attr('name', 'details[' + i + '][cost_category_id]');
                    $(this).find('input[type="text"][name^="details"]').attr('name', 'details[' + i + '][remark]');
                    $(this).find('.nominal-hidden').attr('name', 'details[' + i + '][nominal]');
                    $(this).find('.btn-hapus').attr('data-id', i);
                });
                rowIndex = $('#tabel-detail tbody tr').length;
            }

            $('#btn-tambah-baris').click(function() {
                const current = rowIndex;
                let barisBaru = `
                    <tr id="baris-${current}" class="animate-slide-in">
                        <td>
                            <select name="details[${current}][cost_category_id]" class="form-select" required>
                                ${costCategoryOptions}
                            </select>
                        </td>
                        <td>
                            <input type="text" name="details[${current}][remark]" class="form-control" placeholder="Tambahan keterangan..." required>
                        </td>
                        <td>
                            <div class="input-group input-with-icon">
                                <span class="input-group-text">Rp</span>
                                <input type="text" inputmode="numeric" autocomplete="off"
                                       class="form-control nominal-display" placeholder="0" required>
                                <input type="hidden" name="details[${current}][nominal]" class="nominal-hidden" value="">
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger-modern btn-icon btn-sm btn-hapus" data-id="${current}" title="Hapus baris">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#tabel-detail tbody').append(barisBaru);
                rowIndex++;
                hitungTotal();

                const el = document.getElementById('baris-' + current);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => el.querySelector('select')?.focus({ preventScroll: true }), 400);
                }
            });

            $(document).on('click', '.btn-hapus', function() {
                const $row = $(this).closest('tr');
                if ($row.hasClass('removing')) return;

                if ($('#tabel-detail tbody tr').not('.removing').length <= 1) {
                    alert('⚠️ Minimal harus ada 1 baris rincian biaya.');
                    return;
                }

                $row.addClass('removing').fadeOut(300, function() {
                    $(this).remove();
                    reindexRows();
                    hitungTotal();
                });
            });

            // ============================================================
            // 🎯 5. VALIDASI SUBMIT
            // ============================================================
            $('#form-operasional').on('submit', function(e) {
                let nominalValid = true;
                $('.nominal-display').each(function() {
                    const digits = bersih(this.value);
                    $(this).closest('td').find('.nominal-hidden').val(digits);
                    if (!digits) nominalValid = false;
                });
                if (!nominalValid) {
                    e.preventDefault();
                    alert('⚠️ Nominal tidak boleh kosong atau nol.');
                    return false;
                }

                const sisa  = getSisaSaldoBs();
                const total = hitungTotal();

                // Saldo diperiksa di server saat Konfirmasi.

            });

            hitungTotal();

            // ============================================================
            // 🎯 6. PERIOD LOCK FRONTEND (DENGAN FLATPICKR)
            // ============================================================
            const tglInput  = document.getElementById('tanggal_picker');
            const warning   = document.getElementById('periodWarning');
            const submitButtons = document.querySelectorAll('#form-operasional button[name="save_action"]');
            
            // 🎯 Daftar periode OPEN dari backend (diinject dari Controller)
            const openPeriods = @json($openPeriods);

            if (tglInput && typeof flatpickr !== 'undefined') {
                flatpickr(tglInput, {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d F Y", // Tampilan user: 03 September 2026
                    locale: "id",       // Bahasa Indonesia
                    maxDate: "today",   // 🎯 Tidak bisa pilih masa depan
                    defaultDate: tglInput.value || new Date(),
                    disable: [
                        function(date) {
                            const y = date.getFullYear();
                            const m = String(date.getMonth() + 1).padStart(2, '0');
                            const periodeStr = `${y}-${m}`;
                            // 🎯 NON-AKTIFKAN (abu-abu) jika bulan TIDAK ADA di daftar OPEN
                            return !openPeriods.includes(periodeStr);
                        }
                    ],
                    onChange: function(selectedDates, dateStr, instance) {
                        validatePeriod(dateStr);
                    }
                });
            }

            async function validatePeriod(dateStr) {
                if (!dateStr) return;
                const periode = dateStr.slice(0, 7);
                
                // 🎯 Validasi cepat dari data frontend
                if (!openPeriods.includes(periode)) {
                    document.getElementById('periodWarningText').textContent =
                        `Periode ${periode} tidak aktif atau sudah DITUTUP. Pilih tanggal di bulan yang berjalan/OPEN.`;
                    warning.classList.remove('d-none');
                    submitButtons.forEach(button => button.disabled = true);
                    return;
                }

                try {
                    // Validasi ganda ke API untuk memastikan sinkronisasi real-time
                    const data = await (await fetch(`/api/periode/status?periode=${periode}`)).json();
                    if (!data.open) {
                        document.getElementById('periodWarningText').textContent =
                            `Periode ${periode} sudah DITUTUP. Transaksi akan ditolak sistem — pilih tanggal di periode OPEN.`;
                        warning.classList.remove('d-none');
                        submitButtons.forEach(button => button.disabled = true);
                    } else {
                        warning.classList.add('d-none');
                        submitButtons.forEach(button => button.disabled = false);
                    }
                } catch (err) {
                    console.warn('Gagal cek status periode:', err);
                    // Fallback: jika API gagal tapi frontend bilang open, biarkan saja (backend akan catch juga)
                    warning.classList.add('d-none');
                    submitButtons.forEach(button => button.disabled = false);
                }
            }

            // Trigger validasi awal jika sudah ada nilai default
            if (tglInput && tglInput._flatpickr && tglInput._flatpickr.selectedDates.length > 0) {
                validatePeriod(tglInput._flatpickr.selectedDates[0].toISOString().slice(0, 10));
            } else if (tglInput && tglInput.value) {
                validatePeriod(tglInput.value);
            }
        });
    </script>
</body>
</html>
