@if($bs->isPerjalananDinas() && $bs->attachment_path)
    @php
        $attachmentUrl = route('pengajuan-bs.attachment', $bs);
        $attachmentName = $bs->attachment_name ?: 'Dokumen Pendukung';
        $isPdf = strtolower(pathinfo($bs->attachment_path, PATHINFO_EXTENSION)) === 'pdf';
    @endphp
    <div class="card-custom mb-4" data-bs-attachment-panel>
        <div class="section-title">Lampiran (Laporan HRIS / Izin Perjalanan Dinas)</div>
        <div class="d-flex flex-wrap align-items-center gap-3 p-3 rounded bg-light border">
            <i class="bi {{ $isPdf ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-image text-primary' }} fs-2" aria-hidden="true"></i>
            <div class="flex-grow-1" style="min-width: 0; overflow-wrap: anywhere;">
                <div class="fw-semibold">{{ $attachmentName }}</div>
                <small class="text-muted">Terunggah pada {{ \App\Support\AppTime::format($bs->created_at) }}</small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> {{ $isPdf ? 'Buka PDF' : 'Buka Lampiran' }}
                </a>
                <a href="{{ route('pengajuan-bs.attachment', ['bs' => $bs, 'download' => 1]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-download me-1"></i> Unduh
                </a>
            </div>
        </div>
        @if($isPdf)
            <details class="mt-3 border rounded overflow-hidden">
                <summary class="p-3 bg-light fw-semibold text-primary" style="cursor: pointer;">Pratinjau PDF</summary>
                <iframe src="{{ $attachmentUrl }}"
                        title="Pratinjau PDF: {{ $attachmentName }}"
                        loading="lazy"
                        style="display: block; width: 100%; height: 65vh; min-height: 320px; max-height: 720px; border: 0;"></iframe>
                <p class="small text-muted p-3 mb-0">Jika pratinjau tidak tampil, gunakan tombol Buka PDF atau Unduh.</p>
            </details>
        @endif
        @if($forApproval ?? false)
            <p class="small text-muted mt-3 mb-0">Periksa lampiran sebelum memproses pengajuan.</p>
        @endif
    </div>
@endif
