<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Saldo BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    @include('partials.application-time')
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
            <h1 class="h3 mb-0">Rincian Saldo BS</h1>
            <a href="{{ route('dashboard.transaksi') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
        <p>Seluruh pencairan menambah satu saldo BS. Realisasi Operasional Sales dan Perjalanan Dinas, termasuk pemakaian oleh anggota grup, mengurangi saldo pemilik dana.</p>
        @include('partials.bs-approval-link')
        <form method="GET" class="d-flex gap-2 mb-4">
            <label for="periode" class="col-form-label">Periode</label>
            <select name="periode" id="periode" class="form-select w-auto">
                @foreach($periodOptions as $option)<option value="{{ $option }}" @selected($option === $periode)>{{ $option }}</option>@endforeach
            </select>
            <button class="btn btn-primary">Tampilkan</button>
        </form>
        @if(!$snapshot->opening_final)<div class="alert alert-warning">Saldo awal menunggu penutupan periode sebelumnya.</div>@endif
        <div class="card border-0 shadow-sm mb-4"><div class="card-body row g-3">
            @foreach(['Saldo Awal' => $snapshot->saldo_awal, 'Total Pencairan' => $snapshot->total_masuk, 'Total Pemakaian' => $snapshot->total_keluar, 'Sisa Saldo BS' => $available] as $label => $amount)
                <div class="col-sm-6 col-lg-3"><div class="text-muted">{{ $label }}</div><strong>Rp {{ number_format($amount, 2, ',', '.') }}</strong></div>
            @endforeach
        </div></div>
        <div class="card border-0 shadow-sm"><div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Tanggal</th><th>Aktivitas</th><th>Dokumen</th><th class="text-end">Masuk</th><th class="text-end">Keluar</th><th class="text-end">Saldo</th></tr></thead>
                <tbody>
                    <tr><td colspan="5">Saldo awal</td><td class="text-end text-nowrap">Rp {{ number_format($snapshot->saldo_awal, 2, ',', '.') }}</td></tr>
                    @forelse($rows as $row)
                        <tr>
                            <td class="text-nowrap">{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d/m/Y') : 'Tanggal tidak tercatat' }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td><a href="{{ $row['url'] }}">{{ $row['description'] }}</a></td>
                            @foreach(['incoming', 'outgoing', 'balance'] as $column)<td class="text-end text-nowrap">Rp {{ number_format($row[$column], 2, ',', '.') }}</td>@endforeach
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada mutasi saldo BS pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
        <p class="text-muted small mt-3">Draft dan permintaan yang menunggu persetujuan belum mengurangi saldo. Nomor pengajuan menunjukkan asal pencairan; nomor transaksi menunjukkan pemakaian.</p>
    </main>
</body>
</html>
