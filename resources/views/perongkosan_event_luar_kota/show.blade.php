@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Detail Event Luar Kota')
@section('content')
@include('partials.event-confirmation', ['eventType' => 'ELK'])
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Detail Event Luar Kota</h1>
        <p class="text-muted mb-0">{{ $event->transaksi_id ?: 'Event #'.$event->id }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('akses.cash-flow')
            <a href="{{ route('cash-flow.show', \Carbon\Carbon::parse($event->tanggal)->format('Ym')) }}" class="btn btn-outline-primary">Lihat Cash Flow</a>
        @endcan
        <a href="{{ route('perongkosan_event_luar_kota.index') }}" class="btn btn-outline-secondary">Daftar Event Luar Kota</a>
    </div>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
    <dl class="row mb-0">
        <dt class="col-sm-4">Tanggal</dt><dd class="col-sm-8">{{ \Carbon\Carbon::parse($event->tanggal)->translatedFormat('d F Y') }}</dd>
        <dt class="col-sm-4">Karyawan</dt><dd class="col-sm-8">{{ $event->user?->nama ?? '-' }}</dd>
        <dt class="col-sm-4">Lokasi Event</dt><dd class="col-sm-8">{{ $event->wilayah?->nama ?? '-' }}</dd>
        <dt class="col-sm-4">Kelompok Jam Kerja</dt><dd class="col-sm-8">{{ $event->jamKerja?->code ?? '-' }}</dd>
        <dt class="col-sm-4">Jarak</dt><dd class="col-sm-8">{{ number_format($event->jarak_km, 2, ',', '.') }} km</dd>
        <dt class="col-sm-4">Durasi</dt><dd class="col-sm-8">{{ number_format($event->durasi_jam, 2, ',', '.') }} jam</dd>
        <dt class="col-sm-4">Hari Libur</dt><dd class="col-sm-8">{{ $event->is_libur ? 'Ya' : 'Tidak' }}</dd>
        <dt class="col-sm-4">Keterangan</dt><dd class="col-sm-8 text-break" style="white-space: pre-wrap;">{{ $event->description ?? '-' }}</dd>
        <dt class="col-sm-4">Nominal</dt><dd class="col-sm-8 fw-bold">Rp {{ number_format($event->nominal, 2, ',', '.') }}</dd>
        @if($event->plafon_value !== null)
            <dt class="col-sm-4">Plafon</dt><dd class="col-sm-8">Rp {{ number_format($event->plafon_value, 2, ',', '.') }}</dd>
        @endif
        @if($event->is_over_plafon)
            <dt class="col-sm-4">Kelebihan Plafon</dt><dd class="col-sm-8 text-danger">Rp {{ number_format($event->selisih_plafon, 2, ',', '.') }}</dd>
        @endif
    </dl>
</div></div>
<div class="card border-0 shadow-sm"><div class="card-body p-4">
    <h2 class="h5 mb-3">Foto Bukti Event</h2>
    <div class="row g-3">
        @forelse(collect([$event->foto_event_1, $event->foto_event_2, $event->foto_event_3])->filter()->values() as $photo)
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="{{ asset('storage/'.$photo) }}" target="_blank" rel="noopener" class="d-block border rounded p-2 text-center">
                    <img src="{{ asset('storage/'.$photo) }}" alt="Bukti event {{ $loop->iteration }}" class="img-fluid rounded" style="height: 180px; width: 100%; object-fit: contain;" loading="lazy">
                    <span class="d-block mt-2">Lihat foto {{ $loop->iteration }}</span>
                </a>
            </div>
        @empty
            <p class="text-muted mb-0">Tidak ada foto bukti event yang dilampirkan.</p>
        @endforelse
    </div>
</div></div>
@endsection
