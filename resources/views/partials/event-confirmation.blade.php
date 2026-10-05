<section class="alert {{ $event->status === 'VALIDATED' ? 'alert-success' : 'alert-warning' }} my-3" aria-label="Konfirmasi pengajuan Event">
    @if(session('success'))<p role="status">{{ session('success') }}</p>@endif
    @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    @if($event->status === 'VALIDATED')
        <strong><i class="bi bi-check-circle me-1" aria-hidden="true"></i>Pengajuan sudah dikonfirmasi.</strong>
        <div class="small mt-1">{{ \App\Support\AppTime::format($event->validated_at) }}. Jika rincian Event diubah, konfirmasi ulang diperlukan.</div>
        <a class="btn btn-sm btn-outline-success mt-3" href="{{ route('transaction-recap.module', ['jenis' => $eventType, 'pemakaian_periode' => $event->periode, 'pemakaian_referensi' => $event->transaksi_id]) }}">Lihat Status Finance <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
    @else
        <strong>Siap diajukan ke Finance?</strong>
        <p class="small mt-1 mb-2">Pengajuan belum dikonfirmasi. Periksa nominal, keterangan, dan bukti kegiatan terlebih dahulu. Setelah dikonfirmasi, Event akan muncul di rekap pengajuan dan siap diperiksa Finance.</p>
        @if((int) $event->user_id === (int) auth()->id() && auth()->user()->can($eventType === 'EDK' ? 'akses.event-dalam-kota' : 'akses.event-luar-kota'))
            @php($eventSnapshot = app(\App\Services\BsUsageCheckService::class)->snapshot($event))
            <form class="mt-3" method="POST" action="{{ route('transaction-recap.confirm-event', [$eventType, $event->id]) }}">
                @csrf
                <input type="hidden" name="source_token" value="{{ app(\App\Services\BsUsageCheckService::class)->token($eventType, $event->id, $eventSnapshot['fingerprint']) }}">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Konfirmasi Pengajuan</button>
            </form>
        @else
            <div class="small">Konfirmasi dilakukan oleh pemilik transaksi ini.</div>
        @endif
    @endif
</section>
