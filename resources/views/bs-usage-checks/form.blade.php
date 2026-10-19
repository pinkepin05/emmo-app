    @php
        $reloadInputs = $errors->has('source_token') || $errors->has('review_token') || $errors->has('details');
        $fieldValue = fn ($name) => $reloadInputs ? $defaults[$name] : old($name, $defaults[$name]);
        $canSave = $items->isNotEmpty() && !\App\Services\BsUsageCheckService::awaitingResubmission($transaction);
    @endphp
        <ol class="check-steps" aria-label="Langkah pemeriksaan"><li><span class="check-step">1</span>Tinjau pengajuan</li><li><span class="check-step">2</span>Periksa & sesuaikan nominal per item</li><li><span class="check-step">3</span>Konfirmasi hasil pemeriksaan</li></ol>

        @if($reloadInputs)<div class="alert alert-warning" role="alert">Rincian terbaru sudah ditampilkan. Periksa kembali nominal dan catatan sebelum konfirmasi.</div>@endif
        @if($draft)<div class="alert alert-secondary"><strong>Draft pemeriksaan #{{ $draft->id }}</strong> · terakhir disimpan oleh {{ $draft->checker_name }} pada {{ \App\Support\AppTime::format($draft->updated_at) }}. Draft ini belum menjadi hasil Finance.</div>@endif
        @if($revisionOf)<div class="alert alert-info">Pemeriksaan sebelumnya: <a href="{{ ($unifiedWorkspace ?? false) ? route('transaction-recap.show', $workspaceParams + ['hasil' => $revisionOf->id]).'#hasil-finance' : route('bs-usage-checks.show', $revisionOf) }}">{{ $revisionOf->document_no }}</a>. Dokumen tersebut tetap tersimpan setelah konfirmasi baru.</div>@endif
        @if($sourceChanged)<div class="alert alert-warning">Pengajuan berubah sejak pemeriksaan terakhir. Nominal item yang berubah atau baru diisi dari pengajuan terbaru; periksa kembali sebelum menyimpan.</div>@endif
        @if($needsItemReview)<div class="alert alert-warning" role="alert">Pemeriksaan sebelumnya menggabungkan beberapa rincian dalam satu kategori. Isi nominal persetujuan untuk setiap rincian tersebut.</div>@endif
        @if(\App\Services\BsUsageCheckService::awaitingResubmission($transaction))<div class="alert alert-warning">Pengajuan perlu dikonfirmasi kembali sebelum pemeriksaan Finance dapat dilanjutkan.</div>@endif
        @if($transaction->status !== 'VALIDATED')<div class="alert alert-info">Transaksi sumber belum final ({{ $transaction->status }}). Pemeriksaan ini mencatat rincian saat ini.</div>@endif

        <form method="POST" action="{{ route('bs-usage-checks.store', ['jenis' => $jenis, 'source' => $transaction->id]) }}" id="bs-check-form" class="check-form">
            @csrf
            @if($unifiedWorkspace ?? false)
                <input type="hidden" name="_workspace" value="1">
                @foreach($workspaceContext as $contextKey => $contextValue)
                    <input type="hidden" name="workspace_context[{{ $contextKey }}]" value="{{ $contextValue }}">
                @endforeach
            @endif
            <input type="hidden" name="source_token" value="{{ $sourceToken }}">
            <input type="hidden" name="review_token" value="{{ $reviewToken }}">

            <!-- 🎯 HIDDEN INPUT: Agar backend tetap valid, kita set default ke APPROVED karena penolakan sudah ditangani per-item (nilai 0) -->
            <input type="hidden" name="keputusan" value="APPROVED">

            <section class="check-card"><h2 class="h6 fw-bold mb-3"><span class="check-step">1</span>Pengajuan yang diperiksa</h2>
                <div class="check-context"><div><small>{{ \App\Services\BsUsageCheckService::LABELS[$jenis] }}</small><strong>{{ $transaction->transaksi_id }}</strong><small>{{ $transaction->user->nama }}</small></div><a class="btn btn-sm btn-outline-primary" href="{{ ($unifiedWorkspace ?? false) ? $detailUrl.'#rincian-pengajuan' : route('transaction-recap.show', [$jenis, $transaction->id]) }}">Rincian &amp; Bukti Pengajuan</a></div>
                <div class="row g-3"><div class="col-sm-6"><label class="form-label" for="tanggal_pengecekan">Tanggal pengecekan</label><input type="date" class="form-control" id="tanggal_pengecekan" name="tanggal_pengecekan" value="{{ $fieldValue('tanggal_pengecekan') }}" required></div><div class="col-sm-6"><label class="form-label" for="periode">Periode pemeriksaan</label><input id="periode" class="form-control check-readonly" value="{{ substr($fieldValue('tanggal_pengecekan'), 0, 7) }}" readonly><div class="form-text">Mengikuti tanggal pengecekan.</div></div></div>
            </section>

            <section class="check-card"><h2 class="h6 fw-bold"><span class="check-step">2</span>Periksa nominal per item</h2>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Panduan Persetujuan:</strong> Penyesuaian dilakukan <u>per item</u>.
                    Jika <em>Nominal Disetujui</em> diisi <strong>0</strong>, item tersebut dianggap <strong>ditolak</strong>.
                    Jika ada selisih dengan nominal asal, akan tercatat otomatis sebagai <em>Selisih Persetujuan Finance</em>.
                </div>
                <div class="table-responsive"><table class="table check-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col" width="15%">Item</th><th scope="col" width="20%">Keterangan</th><th scope="col" width="10%" class="text-center">Foto Bukti</th><th scope="col" class="check-money" width="15%">Nominal Asal (Rp)</th><th scope="col" class="check-money" width="15%">Nominal Disetujui (Rp)</th><th scope="col" class="check-money" width="10%">Selisih (Rp)</th><th scope="col" width="25%">Catatan internal Finance</th></tr></thead><tbody role="rowgroup">
                    @forelse($items as $item)
                        @php
                            $key = (int) $item['source_detail_id'];
                            $itemLabel = $item['cost_category_name'].' · '.($item['source_remark'] ?: 'Rincian '.$loop->iteration);
                            $restoreItem = !$reloadInputs && (string) old('details.'.$key.'.source_detail_id') === (string) $item['source_detail_id'];
                            $photoUrl = !empty($item['source_photo_path']) ? route('transaction-detail-photo.show', ['type' => $jenis, 'detail' => $item['source_detail_id']]) : null;
                            $nominalDisetujui = $restoreItem ? old('details.'.$key.'.nominal_disetujui', $item['nominal_disetujui']) : $item['nominal_disetujui'];
                        @endphp
                        <tr role="row" data-check-item data-original-minor="{{ \App\Support\Money::minor($item['nominal_asal']) }}">
                            <td role="cell" class="fw-semibold">{{ $item['cost_category_name'] }}<input type="hidden" name="details[{{ $key }}][source_detail_id]" value="{{ $item['source_detail_id'] }}"><input type="hidden" name="details[{{ $key }}][cost_category_id]" value="{{ $item['cost_category_id'] }}"></td>
                            <td role="cell" data-label="Keterangan" class="text-break">{{ $item['source_remark'] ?: '—' }}</td>

                            {{-- 🎯 KOLOM FOTO TERSENDIRI (DENGAN TRIGGER MODAL) --}}
                            <td role="cell" data-label="Foto Bukti" class="photo-cell text-center">
                                @if($photoUrl)
                                    <button type="button" class="btn btn-link p-0 photo-link-wrapper" data-bs-toggle="modal" data-bs-target="#photoPreviewModal" data-img-url="{{ $photoUrl }}" title="Klik untuk memperbesar">
                                        <img src="{{ $photoUrl }}" alt="Foto bukti" class="photo-thumbnail-check" loading="lazy">
                                    </button>
                                @else
                                    <div class="no-photo-badge-check" title="Tidak ada foto">
                                        <i class="bi bi-image-slash"></i>
                                    </div>
                                @endif
                            </td>

                            <td role="cell" data-label="Nominal asal (Rp)" class="check-money">{{ number_format($item['nominal_asal'], 2, ',', '.') }}</td>

                            {{--  KOLOM NOMINAL DISETUJUI DENGAN FORMAT RUPIAH --}}
                            <td role="cell" data-label="Nominal disetujui (Rp)">
                                <input aria-label="Nominal disetujui {{ $itemLabel }}"
                                       type="text"
                                       inputmode="decimal"
                                       class="form-control text-end nominal-formatted"
                                       data-approved
                                       value="{{ $nominalDisetujui }}"
                                       placeholder="0">
                                <input type="hidden" name="details[{{ $key }}][nominal_disetujui]"
                                       class="nominal-raw-value" data-approved-value
                                       value="{{ $nominalDisetujui }}">
                            </td>

                            <td role="cell" data-label="Selisih (Rp)" class="check-money" data-difference>0,00</td>
                            <td role="cell" data-label="Catatan internal Finance"><textarea aria-label="Catatan {{ $itemLabel }}" class="form-control" name="details[{{ $key }}][catatan]" maxlength="2000" rows="2" placeholder="Alasan pengurangan/penolakan item ini…">{{ $restoreItem ? old('details.'.$key.'.catatan', $item['catatan']) : $item['catatan'] }}</textarea></td>
                        </tr>
                    @empty<tr><td colspan="7" class="text-center text-muted py-4">Transaksi ini belum mempunyai rincian biaya aktif. Tambahkan rincian pada transaksi sumber sebelum melakukan pengecekan.</td></tr>@endforelse
                </tbody><tfoot><tr class="fw-bold"><td colspan="3">Total</td><td data-label="Nominal asal (Rp)" class="check-money" id="check-total-original">{{ number_format(\App\Support\Money::decimal($items->sum(fn ($item) => \App\Support\Money::minor($item['nominal_asal']))), 2, ',', '.') }}</td><td data-label="Total Disetujui (Rp)" class="check-money" id="check-total-approved">—</td><td data-label="Total Selisih (Rp)" class="check-money" id="check-total-difference">—</td><td></td></tr></tfoot></table></div>
            </section>

            <section class="check-card"><h2 class="h6 fw-bold mb-3"><span class="check-step">3</span>Catatan Umum Pemeriksaan</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="catatan_keputusan">Catatan untuk User <span class="text-muted fw-normal">(Opsional)</span></label>
                        <textarea class="form-control" name="catatan_keputusan" id="catatan_keputusan" rows="3" maxlength="2000" aria-describedby="decision-note-hint" placeholder="Jelaskan alasan umum pengurangan nominal atau hal yang perlu diperbaiki user di pengajuan berikutnya…">{{ $fieldValue('catatan_keputusan') }}</textarea>
                        <div class="form-text" id="decision-note-hint">Catatan ini akan terlihat oleh user setelah Anda mengonfirmasi hasil pemeriksaan.</div>
                    </div>
                </div>
            </section>

            <p class="small text-muted">Simpan Draft belum mengubah saldo. Simpan Konfirmasi akan memproses nominal yang disetujui per item (item dengan nilai 0 dianggap ditolak). Sisa Saldo BS dan Total Keluar Stok Kas akan diperbarui sesuai total nominal disetujui Finance.</p>
            <div class="check-savebar"><div class="check-save-totals"><div><span id="check-approved-label">Total disetujui Finance</span><strong id="check-live-approved">—</strong></div><div><span>Selisih pemeriksaan</span><strong id="check-live-difference">—</strong></div></div><div class="recap-head-actions"><button class="btn btn-outline-secondary" type="submit" name="save_action" value="draft" formnovalidate @disabled(!$canSave)><i class="bi bi-save me-1" aria-hidden="true"></i>Simpan Draft</button><button class="btn btn-primary" type="submit" name="save_action" value="confirm" @disabled(!$canSave)><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Simpan Konfirmasi</button></div></div>
        </form>

    {{-- 🎯 MODAL POP-UP PREVIEW FOTO --}}
    <div class="modal fade" id="photoPreviewModal" tabindex="-1" aria-labelledby="photoPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white fw-bold" id="photoPreviewModalLabel"><i class="bi bi-zoom-in me-2"></i>Pratinjau Foto Bukti</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="modalImagePreview" src="" alt="Pratinjau Foto" class="img-fluid rounded shadow-lg" style="max-height: 80vh; object-fit: contain; background: rgba(0,0,0,0.5);">
                </div>
            </div>
        </div>
    </div>


@push('styles')
<style>
/* 🎯 FOTO KOLOM TERSENDIRI - BS Usage Check */
.photo-cell { vertical-align: middle; padding: 0.75rem 0.5rem !important; }
.photo-link-wrapper { display: inline-block; transition: transform 0.25s ease; text-decoration: none; }
.photo-link-wrapper:hover { transform: scale(1.08); }
.photo-thumbnail-check { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 2px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: all 0.25s ease; cursor: zoom-in; }
.photo-link-wrapper:hover .photo-thumbnail-check { border-color: #6f42c1; box-shadow: 0 4px 12px rgba(111, 66, 193, 0.3); }
.no-photo-badge-check { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; font-size: 1rem; }

/* 🎯 FORMAT NOMINAL INPUT */
.nominal-formatted {
    font-variant-numeric: tabular-nums;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.nominal-formatted:focus {
    background-color: #f8f9fa;
}

@media (max-width: 992px) { .photo-thumbnail-check { width: 45px; height: 45px; } }
@media (max-width: 768px) {
    .photo-cell { text-align: center; }
    .table.check-table thead { display: none; }
    .table.check-table, .table.check-table tbody, .table.check-table tr, .table.check-table td { display: block; width: 100%; }
    .table.check-table tr { margin-bottom: 1rem; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; background: white; }
    .table.check-table td { border: none; padding: 0.5rem 0; display: flex; justify-content: space-between; align-items: center; text-align: right; }
    .table.check-table td::before { content: attr(data-label); font-weight: 700; color: #718096; font-size: 0.8rem; text-transform: uppercase; text-align: left; }
    .photo-cell { justify-content: flex-end; }
    .photo-thumbnail-check { width: 60px; height: 60px; }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/bs-usage-checks.js') }}?v={{ filemtime(public_path('js/bs-usage-checks.js')) }}" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 🎯 MODAL PREVIEW FOTO
        const photoModal = document.getElementById('photoPreviewModal');
        if (photoModal) {
            photoModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const imageUrl = button.getAttribute('data-img-url');
                const modalImage = photoModal.querySelector('#modalImagePreview');
                modalImage.src = imageUrl;
            });
            photoModal.addEventListener('hidden.bs.modal', function () {
                const modalImage = photoModal.querySelector('#modalImagePreview');
                modalImage.src = '';
            });
        }

    });
</script>
@endpush
