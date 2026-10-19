@extends('layouts.app-with-sidebar')
@section('title', 'Hasil Pengecekan — '.$check->document_no)
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bs-usage-check-result.css') }}?v={{ filemtime(public_path('css/bs-usage-check-result.css')) }}">
@endpush
@section('content')
    @php
        $asal = $check->details->sum(fn ($item) => \App\Support\Money::minor($item->nominal_asal));
        $disetujui = $check->details->sum(fn ($item) => \App\Support\Money::minor($item->nominal_disetujui));
        $sesuai = $check->details->every(fn ($item) => $item->selisih === '0.00');
    @endphp
    <div class="check-result-page">
    <header class="check-result-header">
        <div>
            <h1 class="h3">Hasil Pengecekan Pemakaian BS</h1>
            <div class="check-result-document"><span>{{ $check->document_no }}</span><span class="check-result-confirmed"><i class="bi bi-file-earmark-check" aria-hidden="true"></i>Dikonfirmasi</span></div>
        </div>
        <div class="check-result-actions">
            <a class="btn btn-outline-secondary" href="{{ route('bs-usage-checks.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Daftar Pemakaian</a>
            @if($check->source)<a class="btn btn-primary" href="{{ route('transaction-recap.show', ['jenis' => $check->jenis_transaksi, 'source' => $check->transaksi_pemakaian_id, 'pemeriksaan' => 1]) }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Ubah Hasil Pemeriksaan</a>@endif
        </div>
    </header>
    @include('bs-usage-checks.shared', ['compactFeedback' => true])
    @include('bs-usage-checks.result')
    <div class="check-card"><div class="row g-3">
        @foreach([
            'Transaksi ID' => $check->id, 'Document No' => $check->document_no,
            'Tanggal Pengecekan' => $check->tanggal_pengecekan->format('d/m/Y'), 'Periode' => $check->periode,
            'Jenis Transaksi' => $check->jenis_transaksi, 'No Referensi' => $check->no_referensi,
            'Transaksi Pemakaian BS ID' => $check->transaksi_pemakaian_id,
            'User Pemilik Transaksi' => $check->user_name, 'User Checker' => $check->checker_name,
            'Waktu Simpan' => \App\Support\AppTime::format($check->created_at),
        ] as $label => $value)<div class="col-md-4"><div class="small text-muted">{{ $label }}</div><div class="fw-semibold">{{ $value }}</div></div>@endforeach
    </div></div>
    @if(!$check->source)<div class="alert alert-info">Transaksi sumber sudah tidak tersedia. Hasil pengecekan dan nomor referensi historis tetap tersimpan.</div>@endif
    <div class="check-card"><div class="d-flex justify-content-between mb-3"><h2 class="h5">Rincian Hasil Pengecekan</h2><span class="badge {{ $sesuai ? 'bg-success' : 'bg-warning text-dark' }} align-self-start">{{ $sesuai ? 'Sesuai' : 'Tidak Sesuai' }}</span></div>
        @include('bs-usage-checks.detail-items')
        <p class="small text-muted mb-0">Selisih = Nominal Asal − Nominal Disetujui. Hasil konfirmasi Finance terakhir menentukan pengeluaran Stok Kas dan pengurangan saldo BS. Dokumen lama tetap tersimpan sebagai riwayat.</p>
    </div>
    @include('bs-usage-checks.audit-trail', [
        'auditHistory' => $history,
        'auditReference' => $check->no_referensi ?: $check->jenis_transaksi.' #'.$check->transaksi_pemakaian_id,
        'auditSelectedId' => $check->id,
    ])
    </div>
@endsection
