@php
    $decision = match ($check->keputusan) {
        'REJECTED' => [
            'tone' => 'rejected', 'icon' => 'bi-x-circle', 'title' => 'Sudah Dicek',
            'description' => 'Pemeriksaan selesai dengan nominal konfirmasi Rp0.',
            'note' => 'Alasan penolakan',
        ],
        'REVISION' => [
            'tone' => 'revision', 'icon' => 'bi-pencil-square', 'title' => 'Sudah Dicek',
            'description' => 'Hasil dari alur pemeriksaan sebelumnya. Nilai pemeriksaan masih berupa usulan; belum menjadi konfirmasi final.',
            'note' => 'Catatan perbaikan',
        ],
        default => [
            'tone' => 'approved', 'icon' => 'bi-check-circle',
            'title' => 'Sudah Dicek',
            'description' => 'Finance telah mengonfirmasi nominal sesuai hasil pemeriksaan.',
            'note' => 'Catatan Finance',
        ],
    };
@endphp
<section class="check-result check-result--{{ $decision['tone'] }}" aria-labelledby="check-result-title">
    <div class="check-result-body">
        <div class="check-result-overview">
            <div class="check-result-heading">
                <span class="check-result-icon"><i class="bi {{ $decision['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <p class="check-result-eyebrow">Hasil Pengecekan</p>
                    <div class="check-result-title-row">
                        <h2 id="check-result-title">{{ $decision['title'] }}</h2>
                        @if($check->keputusan === 'REVISION')<span class="check-result-legacy">Keputusan lama</span>@endif
                    </div>
                    <p class="check-result-description">{{ $decision['description'] }}</p>
                </div>
            </div>
            <div class="check-result-amount">
                <span>{{ $check->keputusan === 'REVISION' ? 'Usulan pemeriksaan' : 'Nominal Konfirmasi' }}</span>
                <strong>Rp {{ number_format(\App\Support\Money::decimal($disetujui), 2, ',', '.') }}</strong>
            </div>
        </div>
        @if($check->catatan_keputusan)
            <div class="check-result-note">
                <h3>{{ $decision['note'] }}</h3>
                <p>{{ $check->catatan_keputusan }}</p>
            </div>
        @endif
        <div class="check-result-meta">
            <span><i class="bi bi-person-check" aria-hidden="true"></i>Diperiksa oleh <strong>{{ $check->checker_name }}</strong></span>
            <span><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $check->tanggal_pengecekan->format('d/m/Y') }}</span>
        </div>
    </div>
    @if($check->revisionOf)
        <div class="check-result-history">
            <i class="bi bi-clock-history" aria-hidden="true"></i>
            <div><p>Pembaruan dari <a href="{{ route('bs-usage-checks.show', $check->revisionOf) }}">{{ $check->revisionOf->document_no }}<i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></p><span>Hasil sebelumnya tetap tersimpan dalam riwayat.</span></div>
        </div>
    @endif
</section>
