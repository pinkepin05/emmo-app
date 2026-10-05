@if($latestDecision && in_array($latestDecision->keputusan, ['REJECTED', 'REVISION'], true))
    @php
        $awaitingConfirmation = $transaction->status !== 'VALIDATED';
        $sameVersion = (int) $latestDecision->source_revision === (int) $transaction->submission_version;
    @endphp
    <section class="recap-card mb-3" aria-labelledby="correction-title">
        <h2 id="correction-title">{{ $awaitingConfirmation ? 'Perbaikan belum diajukan kembali' : ($sameVersion ? ($latestDecision->keputusan === 'REVISION' ? 'Permintaan perbaikan dari keputusan lama' : 'Pengajuan ditolak oleh Finance') : 'Pengajuan kembali menunggu pemeriksaan') }}</h2>
        <p class="small">{{ $latestDecision->keputusan === 'REVISION' ? 'Dokumen lama meminta perbaikan.' : 'Penolakan adalah keputusan Finance untuk versi pengajuan yang diperiksa.' }} Diskusi dengan Finance tidak mengubah keputusan yang tersimpan.</p>
        <div class="alert alert-light border"><strong>Alasan / catatan Finance</strong><p class="mb-0 text-break" style="white-space:pre-wrap">{{ $latestDecision->catatan_keputusan }}</p></div>
        @if($canCorrect && $periodClosed)
            <p class="small mb-0">Periode transaksi sudah ditutup. Perbaikan mengikuti aturan pembukaan periode oleh pihak yang berwenang.</p>
        @elseif($canCorrect && ($sameVersion || $awaitingConfirmation))
            @if($transaction->status === 'VALIDATED' && !$isEvent)
                <p class="small">Perbaiki Pengajuan mengembalikan transaksi menjadi draft. Simpan perubahan, lalu konfirmasikan kembali agar Finance dapat memeriksanya. Pemakaian BS mengikuti status transaksi sumber.</p>
                <form method="POST" action="{{ route('transaction-recap.correct', [$jenis, $transaction->id]) }}">
                    @csrf
                    <input type="hidden" name="check_id" value="{{ $latestDecision->id }}">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Perbaiki Pengajuan</button>
                </form>
            @elseif($isEvent || $transaction->status === 'DRAFT')
                <p class="small">Simpan perbaikan lalu konfirmasikan pengajuan kembali. Finance baru memeriksa hasil perbaikan setelah pengajuan dikonfirmasi.</p>
                <a class="btn btn-outline-primary" href="{{ $editUrl }}"><i class="bi bi-pencil-square me-1" aria-hidden="true"></i>{{ $awaitingConfirmation ? 'Lanjutkan Perbaikan' : 'Perbaiki Pengajuan' }}</a>
            @else
                <p class="small mb-0">Selesaikan persetujuan pemilik BS pada Perjalanan Dinas sebelum pengajuan diperiksa kembali oleh Finance.</p>
            @endif
        @endif
    </section>
@endif
