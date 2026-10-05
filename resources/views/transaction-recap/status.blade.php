@if($row->finance_status === 'PENDING')
    <span class="recap-status pending">Belum Dicek</span>
    <div class="recap-meta">Menunggu pemeriksaan Finance</div>
@elseif($row->finance_status === 'RECHECK')
    <span class="recap-status revision">Perlu Cek Ulang</span>
    <div class="recap-meta">Rincian berubah setelah pemeriksaan</div>
@elseif($row->finance_status === 'REVISION')
    <span class="recap-status revision">Perlu Revisi (keputusan lama)</span>
    <div class="recap-meta">Lihat catatan keputusan</div>
@elseif($row->finance_status === 'REJECTED')
    <span class="recap-status rejected">Ditolak</span>
@else
    <span class="recap-status approved">Disetujui Finance</span>
    <div class="recap-meta">{{ $row->different_items ? 'Tidak Sesuai · Ada Selisih' : 'Sesuai' }}</div>
@endif
