<section class="recap-card" aria-labelledby="finance-result-title">
    <h2 id="finance-result-title">Hasil Finance terakhir</h2>
    @include('transaction-recap.status')
    @php($financeMoney = fn ($amount) => 'Rp '.number_format($amount, 2, ',', '.'))
    <dl class="mt-3">
        <dt>Disetujui Finance</dt><dd>{{ in_array($row->finance_status, ['APPROVED', 'REJECTED']) ? $financeMoney($row->nominal_finance) : '—' }}</dd>
        @if($row->finance_status === 'PENDING')
            <dt>Pending / Belum Dicek</dt><dd>{{ $financeMoney($row->nominal_user) }}</dd>
        @elseif($row->finance_status === 'REVISION')
            <dt>Perlu Revisi (keputusan lama)</dt><dd>{{ $financeMoney($row->nominal_user) }}</dd>
        @elseif($row->finance_status === 'REJECTED')
            <dt>Ditolak</dt><dd>{{ $financeMoney($row->nominal_user) }}</dd>
        @endif
        <dt>Selisih Persetujuan Finance</dt><dd>{{ $row->finance_status === 'APPROVED' ? $financeMoney($row->selisih) : '—' }}</dd>
        @if($row->check_id)
            <dt>{{ $row->needs_recheck ? 'Dokumen sebelumnya' : 'Dokumen pemeriksaan' }}</dt><dd>{{ $row->check_no }}</dd>
            <dt>Checker</dt><dd>{{ $row->checker_name }}</dd>
            <dt>Tanggal pengecekan</dt><dd>{{ \Carbon\Carbon::parse($row->tanggal_pengecekan)->format('d/m/Y') }}</dd>
            <dt>Disimpan</dt><dd>{{ \App\Support\AppTime::format($row->checked_at) }}</dd>
        @endif
    </dl>
    @if($row->catatan_keputusan)
        <div class="mt-3"><h3 class="h6">{{ $row->needs_recheck ? 'Catatan keputusan sebelumnya' : 'Catatan keputusan untuk user' }}</h3><p class="small text-break mb-0" style="white-space:pre-wrap">{{ $row->catatan_keputusan }}</p></div>
    @endif
</section>
