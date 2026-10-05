@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Rincian Transaksi — '.$transaction->transaksi_id)
@section('content')
    @include('transaction-recap.assets')
    @php
        $backFilters = request()->only(['pemakaian_periode', 'pemakaian_status', 'pemakaian_referensi', 'pemakaian_page']);
        $backFilters['pemakaian_periode'] = request()->query->has('pemakaian_periode') ? (request('pemakaian_periode') ?? '') : $transaction->periode;
        $backFilters['jenis'] = $jenis;
        $backRoute = 'transaction-recap.module';
        $isEvent = in_array($jenis, ['EDK', 'ELK']);
        $statusLabel = match($transaction->status) { 'VALIDATED' => 'Dikonfirmasi', 'DRAFT' => 'Belum dikonfirmasi', 'PENDING_OWNER' => 'Menunggu pemohon', 'REJECTED_OWNER' => 'Ditolak pemohon', default => $transaction->status };
    @endphp
    <div class="recap-workspace">
        <a class="recap-back" href="{{ route($backRoute, $backFilters) }}#pemakaian-bs"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke Daftar {{ \App\Services\BsUsageCheckService::LABELS[$jenis] }}</a>
        <header class="recap-head"><div><div class="recap-eyebrow">{{ \App\Services\BsUsageCheckService::LABELS[$jenis] }}</div><h1>Rincian Transaksi</h1><p>{{ $transaction->transaksi_id ?: $jenis.' #'.$transaction->id }}</p></div><span class="recap-status {{ $transaction->status === 'VALIDATED' ? 'approved' : 'pending' }}">{{ $statusLabel }}</span></header>
        @include('bs-usage-checks.shared')
        @include('transaction-recap.correction')
        <div class="recap-detail-grid">
            <div>
                <section class="recap-card"><h2>Informasi pengajuan</h2><dl>
                    @foreach(['User' => $transaction->user->nama, 'Tanggal transaksi' => \Carbon\Carbon::parse($transaction->tanggal)->format('d/m/Y'), 'Periode' => $transaction->periode, 'Keterangan' => $transaction->description ?: '—'] as $label => $value)<dt>{{ $label }}</dt><dd>{{ $value }}</dd>@endforeach
                    @if($isEvent)
                        <dt>Kelompok jam kerja</dt><dd>{{ $transaction->jamKerja?->code ?? '—' }}</dd>
                        <dt>Hari libur</dt><dd>{{ $transaction->is_libur ? 'Ya' : 'Tidak' }}</dd>
                        @if($jenis === 'ELK')<dt>Lokasi Event</dt><dd>{{ $transaction->wilayah?->nama ?? '—' }}</dd>@endif
                        <dt>Plafon</dt><dd>{{ $transaction->plafon_value !== null ? 'Rp '.number_format($transaction->plafon_value, 2, ',', '.') : '—' }}</dd>
                    @endif
                </dl></section>
                <section class="recap-card"><h2>Rincian biaya yang diajukan</h2>
                    <div class="table-responsive"><table class="table small"><thead><tr><th scope="col">Item</th>@unless($isEvent)<th scope="col">Keterangan</th>@endunless<th scope="col" class="text-end">Nominal</th></tr></thead><tbody>
                        @if($isEvent)
                            @foreach($items as $item)<tr><td>{{ $item['cost_category_name'] }}</td><td class="text-end text-nowrap">Rp {{ number_format($item['nominal_asal'], 2, ',', '.') }}</td></tr>@endforeach
                        @else
                            @foreach($transaction->details()->with('costCategory')->get() as $detail)<tr><td>{{ $detail->costCategory?->nama }}</td><td class="text-break">{{ $detail->remark }}</td><td class="text-end text-nowrap">Rp {{ number_format($detail->nominal, 2, ',', '.') }}</td></tr>@endforeach
                        @endif
                    </tbody></table></div>
                    <p class="small text-muted mb-0">Nominal asli dari pengajuan user. Hasil Finance terakhir ditampilkan di bawah rincian ini.</p>
                    @if($transaction->foto_event_1 || $transaction->foto_event_2 || $transaction->foto_event_3)
                        <h3 class="h6 mt-4">Bukti kegiatan</h3><div class="recap-help-links">
                            @foreach(['foto_event_1', 'foto_event_2', 'foto_event_3'] as $photo)
                                @if($transaction->$photo)<a href="{{ asset('storage/'.$transaction->$photo) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="bi bi-image me-1" aria-hidden="true"></i>Bukti Event {{ $loop->iteration }} <span class="visually-hidden">(tab baru)</span></a>@endif
                            @endforeach
                        </div>
                    @endif
                </section>
                @if($financeResult)
                    @include('transaction-recap.finance-result', ['row' => $financeResult])
                @else
                    <section class="recap-card"><h2>Hasil Finance</h2><p class="small text-muted mb-0">Pengajuan belum dikonfirmasi. Hasil Finance masuk rekap setelah konfirmasi pengajuan.</p></section>
                @endif
                @if($isEvent)@include('partials.event-confirmation', ['event' => $transaction, 'eventType' => $jenis])@endif
                @include('transaction-recap.decision-history')
            </div>
            <aside>
                <section class="recap-card"><div class="recap-eyebrow">Total pengajuan</div><div class="recap-detail-amount">Rp {{ number_format($items->sum('nominal_asal'), 2, ',', '.') }}</div><p class="recap-meta">{{ $items->count() }} kategori biaya</p>
                    @can('akses.approval-finance')
                        @if(!\App\Services\BsUsageCheckService::awaitingResubmission($transaction))<a class="btn btn-primary w-100 mt-2" href="{{ route('bs-usage-checks.create', [$jenis, $transaction->id]) }}">Periksa Pengajuan</a>@else<p class="small text-muted">Menunggu konfirmasi pengajuan sebelum diperiksa Finance.</p>@endif
                    @endcan
                </section>
                <section class="recap-card"><h2>Alur pengajuan</h2><ol class="recap-step-list"><li>User melengkapi rincian dan mengonfirmasi pengajuan.</li><li>Finance menyetujui atau menolak dengan alasan.</li><li>Jika ditolak, user dapat berdiskusi dengan Finance, memperbaiki transaksi, lalu mengonfirmasi pengajuan kembali.</li><li>Finance memeriksa ulang dan menyimpan keputusan baru. Riwayat keputusan tetap tersimpan.</li></ol></section>
            </aside>
        </div>
    </div>
@endsection
