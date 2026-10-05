@extends('layouts.app-with-sidebar')
@section('title', 'Form Pengecekan Pemakaian BS')
@section('content')
    @include('transaction-recap.assets')
    @php
        $reloadInputs = $errors->has('source_token') || $errors->has('review_token');
        $fieldValue = fn ($name) => $reloadInputs ? $defaults[$name] : old($name, $defaults[$name]);
        $canSave = $items->isNotEmpty() && !\App\Services\BsUsageCheckService::awaitingResubmission($transaction);
    @endphp
    <div class="recap-workspace">
        <a class="recap-back" href="{{ route('bs-usage-checks.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke daftar pengecekan</a>
        <header class="recap-head"><div><div class="recap-eyebrow">Pemeriksaan Finance</div><h1>{{ $draft ? 'Lanjutkan draft pemeriksaan' : ($historyCount ? 'Periksa kembali pengajuan' : 'Periksa pengajuan') }}</h1><p>Simpan pekerjaan sebagai draft atau konfirmasikan hasil pemeriksaan. Setiap konfirmasi menyimpan keputusan baru; keputusan sebelumnya tetap tercatat.</p></div></header>
        @include('bs-usage-checks.shared')
        <ol class="check-steps" aria-label="Langkah pemeriksaan"><li><span class="check-step">1</span>Tinjau pengajuan</li><li><span class="check-step">2</span>Periksa nominal</li><li><span class="check-step">3</span>Konfirmasi keputusan</li></ol>
        @if($reloadInputs)<div class="alert alert-warning" role="alert">Rincian terbaru sudah ditampilkan. Periksa kembali nominal dan catatan sebelum konfirmasi.</div>@endif
        @if($draft)<div class="alert alert-secondary"><strong>Draft pemeriksaan #{{ $draft->id }}</strong> · terakhir disimpan oleh {{ $draft->checker_name }} pada {{ \App\Support\AppTime::format($draft->updated_at) }}. Draft ini belum menjadi hasil Finance.</div>@endif
        @if($revisionOf)<div class="alert alert-info">Pemeriksaan sebelumnya: <a href="{{ route('bs-usage-checks.show', $revisionOf) }}">{{ $revisionOf->document_no }}</a>. Dokumen tersebut tetap tersimpan setelah konfirmasi baru.</div>@endif
        @if($sourceChanged)<div class="alert alert-warning">Pengajuan berubah sejak pemeriksaan terakhir. Nominal item yang berubah atau baru diisi dari pengajuan terbaru; periksa kembali sebelum menyimpan.</div>@endif
        @if(\App\Services\BsUsageCheckService::awaitingResubmission($transaction))<div class="alert alert-warning">Pengajuan perlu dikonfirmasi kembali sebelum pemeriksaan Finance dapat dilanjutkan.</div>@endif
        @if($transaction->status !== 'VALIDATED')<div class="alert alert-info">Transaksi sumber belum final ({{ $transaction->status }}). Pemeriksaan ini mencatat rincian saat ini; sumber belum masuk rekap pengajuan terkonfirmasi.</div>@endif
        @if($draft?->keputusan === 'REVISION')<div class="alert alert-warning">Draft lama menggunakan keputusan Perlu Revisi. Pilih Disetujui Finance atau Ditolak sebelum menyimpan kembali; rincian draft tetap tersedia.</div>@endif
        <form method="POST" action="{{ route('bs-usage-checks.store', ['jenis' => $jenis, 'source' => $transaction->id]) }}" id="bs-check-form" class="check-form">
            @csrf
            <input type="hidden" name="source_token" value="{{ $sourceToken }}">
            <input type="hidden" name="review_token" value="{{ $reviewToken }}">
            <section class="check-card"><h2 class="h6 fw-bold mb-3"><span class="check-step">1</span>Pengajuan yang diperiksa</h2>
                <div class="check-context"><div><small>{{ \App\Services\BsUsageCheckService::LABELS[$jenis] }}</small><strong>{{ $transaction->transaksi_id }}</strong><small>{{ $transaction->user->nama }}</small></div><a class="btn btn-sm btn-outline-primary" href="{{ route('transaction-recap.show', [$jenis, $transaction->id]) }}">Rincian &amp; Bukti Pengajuan</a></div>
                <div class="row g-3"><div class="col-sm-6"><label class="form-label" for="tanggal_pengecekan">Tanggal pengecekan</label><input type="date" class="form-control" id="tanggal_pengecekan" name="tanggal_pengecekan" value="{{ $fieldValue('tanggal_pengecekan') }}" required></div><div class="col-sm-6"><label class="form-label" for="periode">Periode pemeriksaan</label><input id="periode" class="form-control check-readonly" value="{{ substr($fieldValue('tanggal_pengecekan'), 0, 7) }}" readonly><div class="form-text">Mengikuti tanggal pengecekan.</div></div></div>
                <details class="recap-guide mb-0"><summary>Informasi dokumen otomatis</summary><dl class="row small mt-3 mb-0"><dt class="col-sm-4">Nomor dokumen</dt><dd class="col-sm-8">Otomatis saat Simpan Konfirmasi</dd><dt class="col-sm-4">Checker</dt><dd class="col-sm-8">{{ auth()->user()->nama }}</dd><dt class="col-sm-4">Referensi</dt><dd class="col-sm-8">{{ $jenis }} · {{ $transaction->transaksi_id }}</dd></dl></details>
            </section>
            <section class="check-card"><h2 class="h6 fw-bold"><span class="check-step">2</span>Periksa nominal per item</h2><p class="small text-muted">Nominal awal mengikuti pengajuan. Ubah hanya item yang perlu disesuaikan. Item dengan kategori sama dijumlahkan.</p>
                <div class="table-responsive"><table class="table check-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Item</th><th scope="col" class="check-money">Nominal Asal (Rp)</th><th scope="col" class="check-money">Nominal Disetujui (Rp)</th><th scope="col" class="check-money">Selisih (Rp)</th><th scope="col">Catatan internal Finance</th></tr></thead><tbody role="rowgroup">
                    @forelse($items as $item)
                        @php($key = (int) $item['cost_category_id'])
                        <tr role="row" data-check-item data-original-minor="{{ \App\Support\Money::minor($item['nominal_asal']) }}">
                            <td role="cell" class="fw-semibold">{{ $item['cost_category_name'] }}<input type="hidden" name="details[{{ $key }}][cost_category_id]" value="{{ $key }}"></td>
                            <td role="cell" data-label="Nominal asal (Rp)" class="check-money">{{ number_format($item['nominal_asal'], 2, ',', '.') }}</td>
                            <td role="cell" data-label="Nominal disetujui (Rp)"><input aria-label="Nominal disetujui {{ $item['cost_category_name'] }}" type="number" inputmode="decimal" min="0" max="9999999999999.99" step="0.01" class="form-control text-end" data-approved name="details[{{ $key }}][nominal_disetujui]" value="{{ $reloadInputs ? $item['nominal_disetujui'] : old('details.'.$key.'.nominal_disetujui', $item['nominal_disetujui']) }}" required></td>
                            <td role="cell" data-label="Selisih (Rp)" class="check-money" data-difference>0,00</td>
                            <td role="cell" data-label="Catatan internal Finance"><textarea aria-label="Catatan {{ $item['cost_category_name'] }}" class="form-control" name="details[{{ $key }}][catatan]" maxlength="2000" rows="2" placeholder="Catatan untuk pemeriksaan…">{{ $reloadInputs ? $item['catatan'] : old('details.'.$key.'.catatan', $item['catatan']) }}</textarea></td>
                        </tr>
                    @empty<tr><td colspan="5" class="text-center text-muted py-4">Transaksi ini belum mempunyai rincian biaya aktif. Tambahkan rincian pada transaksi sumber sebelum melakukan pengecekan.</td></tr>@endforelse
                </tbody><tfoot><tr class="fw-bold"><td>Total</td><td data-label="Nominal asal (Rp)" class="check-money" id="check-total-original">{{ number_format(\App\Support\Money::decimal($items->sum(fn ($item) => \App\Support\Money::minor($item['nominal_asal']))), 2, ',', '.') }}</td><td data-label="Nilai pemeriksaan (Rp)" class="check-money" id="check-total-approved">—</td><td data-label="Selisih (Rp)" class="check-money" id="check-total-difference">—</td><td></td></tr></tfoot></table></div>
                <p class="small text-muted mb-0">Selisih = nominal asal − nominal disetujui. Catatan per item hanya terlihat pada halaman Finance.</p>
            </section>
            <section class="check-card"><h2 class="h6 fw-bold mb-3"><span class="check-step">3</span>Tentukan keputusan Finance</h2><div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="keputusan">Keputusan Finance</label><select class="form-select" name="keputusan" id="keputusan" aria-describedby="check-decision-help" required><option value="" @selected($fieldValue('keputusan') === '') disabled>Pilih keputusan Finance</option>@foreach(\App\Services\BsUsageCheckService::DECISIONS as $value => $label)<option value="{{ $value }}" @selected($fieldValue('keputusan') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-8"><label class="form-label" for="catatan_keputusan">Catatan Keputusan untuk User <span id="check-note-required" class="text-danger"></span></label><textarea class="form-control" name="catatan_keputusan" id="catatan_keputusan" rows="3" maxlength="2000" aria-describedby="decision-note-hint" placeholder="Jelaskan alasan atau perbaikan yang diperlukan…">{{ $fieldValue('catatan_keputusan') }}</textarea><div class="form-text" id="decision-note-hint">Terlihat oleh user setelah konfirmasi. Alasan penolakan wajib diisi saat mengonfirmasi keputusan Ditolak.</div></div>
            </div><p class="check-decision-help mb-0" id="check-decision-help" role="status">Persetujuan memakai nominal hasil pemeriksaan. Penolakan membuat persetujuan nol dan wajib disertai alasan. User dapat memperbaiki dan mengajukan kembali.</p></section>
            <p class="small text-muted">Simpan Draft untuk melanjutkan nanti; nominal boleh belum lengkap. Simpan Konfirmasi menerbitkan dokumen baru dan memperbarui hasil Finance. Riwayat tetap tersimpan dan saldo BS tidak berubah.</p>
            <div class="check-savebar"><div class="check-save-totals"><div><span id="check-approved-label">Total disetujui</span><strong id="check-live-approved">—</strong></div><div><span>Selisih pemeriksaan</span><strong id="check-live-difference">—</strong></div></div><div class="recap-head-actions"><button class="btn btn-outline-secondary" type="submit" name="save_action" value="draft" formnovalidate @disabled(!$canSave)><i class="bi bi-save me-1" aria-hidden="true"></i>Simpan Draft</button><button class="btn btn-primary" type="submit" name="save_action" value="confirm" @disabled(!$canSave)><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Simpan Konfirmasi</button></div></div>
        </form>
    </div>
@endsection
@push('scripts')<script src="{{ asset('js/bs-usage-checks.js') }}?v={{ filemtime(public_path('js/bs-usage-checks.js')) }}" defer></script>@endpush
