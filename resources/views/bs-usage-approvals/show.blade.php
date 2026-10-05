<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pemeriksaan Pemakaian BS - {{ $transaksi->transaksi_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light">
    @include('partials.application-time')
    <main class="container py-4" style="max-width: 1000px">
        <div class="d-flex justify-content-between gap-3 mb-3"><h1 class="h3">Pemeriksaan Pemakaian BS</h1><a href="{{ route('bs-usage-approvals.index') }}" class="btn btn-outline-secondary">Daftar Permintaan</a></div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @include('partials.travel-confirmation', ['canEdit' => false])
        <div class="card mb-3"><div class="card-body">
            <h2 class="h5">{{ $transaksi->transaksi_id }}</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4">Anggota yang memakai</dt><dd class="col-sm-8">{{ $transaksi->user?->nama }}</dd>
                <dt class="col-sm-4">Tanggal / Tujuan Dinas</dt><dd class="col-sm-8">{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y') }} / {{ $transaksi->tujuanDinas?->nama }}</dd>
                <dt class="col-sm-4">Keterangan</dt><dd class="col-sm-8">{{ $transaksi->description }}</dd>
                <dt class="col-sm-4">Jarak / Durasi</dt><dd class="col-sm-8">{{ $transaksi->jarak_km }} km / {{ $transaksi->durasi_jam }} jam</dd>
                <dt class="col-sm-4">Saldo BS tersedia ({{ $transaksi->periode }})</dt><dd class="col-sm-8">Rp {{ number_format(app(\App\Services\BsCashFlowService::class)->availableBalance($balance), 2, ',', '.') }}</dd>
            </dl>
            @if(!$balance['opening_final'])<p class="text-warning mb-0">Saldo awal masih menunggu tutup buku bulan sebelumnya.</p>@endif
            @if($transaksi->is_over_plafon)<p class="text-danger mt-2 mb-0">Biaya melebihi plafon Rp {{ number_format($transaksi->plafon_value, 2, ',', '.') }}. Selisih: Rp {{ number_format($transaksi->selisih_plafon, 2, ',', '.') }}.</p>@endif
        </div></div>
        <div class="card mb-3"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Jenis Biaya</th><th>Keterangan</th><th class="text-end">Nominal</th></tr></thead>
            <tbody>@foreach($transaksi->details as $detail)<tr><td>{{ $detail->costCategory?->nama ?? $detail->costCategory?->name ?? $detail->costCategory?->description ?? '-' }}</td><td>{{ $detail->remark }}</td><td class="text-end text-nowrap">Rp {{ number_format($detail->nominal, 2, ',', '.') }}</td></tr>@endforeach</tbody>
            <tfoot><tr><th colspan="2">Total Pemakaian yang Diajukan</th><th class="text-end text-nowrap">Rp {{ number_format($transaksi->details->sum('nominal'), 2, ',', '.') }}</th></tr></tfoot>
        </table></div></div>
        @include('bs-usage-approvals.actions')
        @include('bs-usage-approvals.history')
    </main>
</body>
</html>
