@php
    $sudahDicek = !empty($row->check_id)
        && empty($row->needs_recheck);
@endphp

@if($sudahDicek)
    <span class="recap-status approved">Sudah Dicek</span>

    @if($row->finance_status === 'REVISION')
        <div class="recap-meta">
            Hasil pemeriksaan lama; nominal belum menjadi konfirmasi final.
        </div>
    @else
        <div class="recap-meta">
            {{ $row->different_items ? 'Ada Selisih' : 'Sesuai' }}
        </div>
    @endif
@else
    <span class="recap-status pending">Belum Dicek</span>
    <div class="recap-meta">
        {{ !empty($row->needs_recheck)
            ? 'Transaksi berubah; menunggu pengecekan ulang'
            : 'Menunggu pemeriksaan Finance' }}
    </div>
@endif