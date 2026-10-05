@extends('layouts.app-with-sidebar')
@section('title', 'Hasil Pengecekan — '.$check->document_no)
@section('content')
    <div class="d-flex justify-content-between gap-3 flex-wrap mb-3"><div><h1 class="h3">Hasil Pengecekan Pemakaian BS</h1><p class="text-muted mb-0">{{ $check->document_no }} · Dikonfirmasi</p></div><div class="d-flex gap-2 align-items-start"><a class="btn btn-outline-secondary" href="{{ route('bs-usage-checks.index') }}">Daftar Pemakaian</a>@if($check->source)<a class="btn btn-primary" href="{{ route('bs-usage-checks.create', ['jenis' => $check->jenis_transaksi, 'source' => $check->transaksi_pemakaian_id]) }}">Ubah Hasil Pemeriksaan</a>@endif</div></div>
    @include('bs-usage-checks.shared')
    @if($check->revisionOf)<div class="alert alert-info">Keputusan ini menggantikan hasil pemeriksaan <a href="{{ route('bs-usage-checks.show', $check->revisionOf) }}">{{ $check->revisionOf->document_no }}</a> untuk rekap terbaru. Hasil sebelumnya tetap tersimpan dalam riwayat.</div>@endif
    <div class="alert {{ $check->keputusan === 'REJECTED' ? 'alert-danger' : ($check->keputusan === 'REVISION' ? 'alert-warning' : 'alert-success') }}"><strong>{{ \App\Services\BsUsageCheckService::decisionLabel($check->keputusan) }}</strong>@if($check->catatan_keputusan)<div style="white-space:pre-wrap;">{{ $check->catatan_keputusan }}</div>@endif @if($check->keputusan === 'REVISION')<div>Nilai pemeriksaan di bawah masih berupa usulan; belum menjadi persetujuan final.</div>@endif</div>
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
    @php
        $asal = $check->details->sum(fn ($item) => \App\Support\Money::minor($item->nominal_asal));
        $disetujui = $check->details->sum(fn ($item) => \App\Support\Money::minor($item->nominal_disetujui));
        $sesuai = $check->details->every(fn ($item) => $item->selisih === '0.00');
    @endphp
    <div class="check-card"><div class="d-flex justify-content-between mb-3"><h2 class="h5">Rincian Hasil Pengecekan</h2><span class="badge {{ $sesuai ? 'bg-success' : 'bg-warning text-dark' }} align-self-start">{{ $sesuai ? 'Sesuai' : 'Tidak Sesuai' }}</span></div>
        <div class="table-responsive"><table class="table check-table"><thead><tr><th>Item</th><th class="check-money">Nominal Asal (Rp)</th><th class="check-money">Nominal Disetujui (Rp)</th><th class="check-money">Selisih (Rp)</th><th>Catatan</th></tr></thead><tbody>
            @foreach($check->details as $item)<tr><td>{{ $item->cost_category_name }}</td><td class="check-money">{{ number_format($item->nominal_asal, 2, ',', '.') }}</td><td class="check-money">{{ number_format($item->nominal_disetujui, 2, ',', '.') }}</td><td class="check-money {{ $item->selisih !== '0.00' ? 'text-danger' : '' }}">{{ number_format($item->selisih, 2, ',', '.') }}</td><td class="check-note">{{ $item->catatan ?: '—' }}</td></tr>@endforeach
        </tbody><tfoot><tr class="fw-bold"><td>Total</td><td class="check-money">{{ number_format(\App\Support\Money::decimal($asal), 2, ',', '.') }}</td><td class="check-money">{{ number_format(\App\Support\Money::decimal($disetujui), 2, ',', '.') }}</td><td class="check-money">{{ number_format(\App\Support\Money::decimal($asal - $disetujui), 2, ',', '.') }}</td><td></td></tr></tfoot></table></div>
        <p class="small text-muted mb-0">Selisih = Nominal Asal − Nominal Disetujui. Hasil ini menyimpan rincian saat pengecekan dan tidak mengubah saldo BS.</p>
    </div>
    <div class="check-card"><div class="d-flex justify-content-between gap-2 mb-3"><h2 class="h5">Riwayat Pengecekan Referensi Ini</h2><a href="{{ route('bs-usage-checks.index', ['tampilan' => 'rincian', 'mode' => 'semua', 'jenis_source' => $check->jenis_transaksi, 'source_id' => $check->transaksi_pemakaian_id]) }}">Lihat semua riwayat</a></div>
        <div class="table-responsive"><table class="table"><thead><tr><th>Document No</th><th>Tanggal Pengecekan</th><th>Checker</th><th>Waktu Simpan</th></tr></thead><tbody>@foreach($history as $item)<tr class="{{ $item->id === $check->id ? 'table-primary' : '' }}"><td><a href="{{ route('bs-usage-checks.show', $item) }}">{{ $item->document_no }}</a></td><td>{{ $item->tanggal_pengecekan->format('d/m/Y') }}</td><td>{{ $item->checker_name }}</td><td>{{ \App\Support\AppTime::format($item->created_at) }}</td></tr>@endforeach</tbody></table></div>
        {{ $history->links() }}
    </div>
@endsection
