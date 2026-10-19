@php
    $reportedTotal = $items->sum('nominal_asal');
    $headerMoney = fn ($value) => 'Rp '.number_format($value, 2, ',', '.');
    $headerFields = [
        'ID Transaksi' => $transaction->transaksi_id ?: $jenis.' #'.$transaction->id,
        'User (Karyawan)' => $transaction->user?->nama ?? $transaction->user?->name ?? '—',
        ($jenis === 'PD' ? 'Tanggal Dinas' : ($isEvent ? 'Tanggal Event' : 'Tanggal transaksi')) => \Carbon\Carbon::parse($transaction->tanggal)->translatedFormat('d F Y'),
        'Periode' => $transaction->periode ?: '—',
    ];

    if (!$isEvent) {
        $headerFields['Pemilik Saldo BS'] = $transaction->bsCashOwnerId()
            ? ($transaction->bsOwner?->nama ?? $transaction->bonSementara?->user?->nama ?? '—')
            : 'Arsip tanpa BS';
    }
    $headerFields['Status transaksi'] = $transaction->status === 'VALIDATED' ? 'Dikonfirmasi User' : $statusLabel;
    if ($transaction->status === 'VALIDATED') {
        $headerFields['Waktu konfirmasi'] = \App\Support\AppTime::format($transaction->validated_at);
    }

    if (in_array($jenis, ['PD', 'ELK'], true)) {
        $location = $jenis === 'PD' ? $transaction->tujuanDinas : $transaction->wilayah;
        $headerFields[$jenis === 'PD' ? 'Tujuan Dinas' : 'Lokasi Event'] = $location
            ? collect([$location->nama, $location->parent?->nama])->filter()->implode(', ')
            : '—';
        $headerFields['Territory'] = $location?->teritory ?: '—';
        $headerFields['Jarak'] = $transaction->jarak_km !== null ? number_format($transaction->jarak_km, 2, ',', '.').' KM' : '—';
        $headerFields['Durasi'] = $transaction->durasi_jam !== null ? number_format($transaction->durasi_jam, 2, ',', '.').' Jam' : '—';
    }
    if ($isEvent) {
        $headerFields['Kelompok jam kerja'] = $transaction->jamKerja?->code ?? '—';
        $headerFields['Hari libur'] = $transaction->is_libur ? 'Ya' : 'Tidak';
    }
    $headerFields['Total Pengeluaran'] = $headerMoney($reportedTotal);
    if ($jenis === 'PD' || $isEvent) {
        $headerFields['Plafon'] = $transaction->plafon_value !== null ? $headerMoney($transaction->plafon_value) : '—';
        $headerFields['Status Plafon'] = $transaction->is_over_plafon
            ? 'OVER PLAFON (Selisih: '.$headerMoney($transaction->selisih_plafon ?? 0).')'
            : ($transaction->plafon_value
                ? 'Dalam Plafon (Sisa: '.$headerMoney($transaction->plafon_value - $reportedTotal).')'
                : 'Tidak ada data plafon');
    }

    $dateLabel = $jenis === 'PD' ? 'Tanggal Dinas' : ($isEvent ? 'Tanggal Event' : 'Tanggal transaksi');
    $headerGroups = collect([
        'identity' => ['title' => 'Identitas transaksi', 'labels' => ['ID Transaksi', 'User (Karyawan)', 'Pemilik Saldo BS']],
        'timing' => ['title' => 'Tanggal & konfirmasi', 'labels' => [$dateLabel, 'Periode', 'Status transaksi', 'Waktu konfirmasi']],
        'activity' => ['title' => $jenis === 'PD' ? 'Tujuan & perjalanan' : 'Lokasi & kegiatan', 'labels' => ['Tujuan Dinas', 'Lokasi Event', 'Territory', 'Jarak', 'Durasi', 'Kelompok jam kerja', 'Hari libur']],
        'expense' => ['title' => $jenis === 'OPR' ? 'Pengeluaran' : 'Pengeluaran & plafon', 'labels' => ['Total Pengeluaran', 'Plafon', 'Status Plafon']],
    ])->map(function ($group) use ($headerFields) {
        $group['fields'] = collect($group['labels'])
            ->filter(fn ($label) => array_key_exists($label, $headerFields))
            ->mapWithKeys(fn ($label) => [$label => $headerFields[$label]]);
        return $group;
    })->filter(fn ($group) => $group['fields']->isNotEmpty());
@endphp
<section class="recap-card recap-submission-info" id="informasi-pengajuan" aria-labelledby="informasi-pengajuan-title">
    <h2 id="informasi-pengajuan-title">Informasi pengajuan</h2>
    <div class="recap-info-groups">
        @foreach($headerGroups as $key => $group)
            <section class="recap-info-group" data-info-group="{{ $key }}" aria-labelledby="recap-info-{{ $key }}-title">
                <h3 id="recap-info-{{ $key }}-title">{{ $group['title'] }}</h3>
                <dl class="recap-info-grid">
                    @foreach($group['fields'] as $label => $value)
                        <div @class(['recap-info-field', 'recap-info-wide' => in_array($label, ['ID Transaksi', 'Status Plafon'], true)])>
                            <dt>{{ $label }}</dt>
                            <dd @class(['recap-info-amount' => $label === 'Total Pengeluaran', 'text-danger' => $label === 'Status Plafon' && $transaction->is_over_plafon])>
                                @if($label === 'Status transaksi')
                                    <span class="recap-info-status {{ $transaction->status === 'VALIDATED' ? 'is-confirmed' : 'is-pending' }}">{{ $value }}</span>
                                @elseif($label === 'Waktu konfirmasi' && str_contains($value, ', '))
                                    @php($confirmationParts = explode(', ', $value, 2))
                                    <span class="recap-info-time-part">{{ $confirmationParts[0] }},</span> <span class="recap-info-time-part">{{ $confirmationParts[1] }}</span>
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach
    </div>
    <dl class="recap-info-grid recap-info-description-group">
        <div class="recap-info-field recap-info-wide">
            <dt>{{ $jenis === 'OPR' ? 'Keterangan Umum' : 'Deskripsi Kegiatan' }}</dt>
            <dd class="recap-info-description">{{ $transaction->description ?: '—' }}</dd>
        </div>
    </dl>
</section>
