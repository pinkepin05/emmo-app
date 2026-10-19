<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Persetujuan Pemakaian BS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.mobile-assets')
</head>
<body class="bg-light">
    @include('partials.application-time')
    <main class="container py-4">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><h1 class="h3">Persetujuan Pemakaian BS</h1><a href="{{ route('dashboard.transaksi') }}" class="btn btn-outline-secondary">Kembali</a></div>
        <p>Permintaan anggota grup dinas untuk memakai saldo BS Anda. Setelah Anda menyetujui rincian, Finance memeriksa pengajuan. Saldo baru berkurang setelah konfirmasi Finance. Permintaan yang menunggu tidak menyisihkan saldo.</p>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <form class="d-flex flex-wrap gap-2 mb-3" method="GET">
            <label for="status" class="col-form-label">Status</label>
            <select name="status" id="status" class="form-select w-auto">
                @foreach(['PENDING_OWNER' => 'Menunggu Persetujuan', 'REJECTED_OWNER' => 'Ditolak', 'VALIDATED' => 'Disetujui', 'ALL' => 'Semua'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Tampilkan</button>
        </form>
        <div class="card mb-3"><div class="table-responsive"><table class="table mb-0 align-middle">
            <thead><tr><th>Anggota</th><th>Transaksi</th><th>Tanggal Dinas</th><th>Tujuan</th><th class="text-end">Total Biaya</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($requests as $item)
                    <tr><td>{{ $item->user?->nama }}</td><td>{{ $item->transaksi_id }}</td><td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td><td>{{ $item->tujuanDinas?->nama }}</td><td class="text-end text-nowrap">Rp {{ number_format($item->details_sum_nominal, 2, ',', '.') }}</td><td>{{ $item->status_label }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('bs-usage-approvals.show', $item->id) }}">Periksa Rincian</a></td></tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada permintaan pada status ini.</td></tr>
                @endforelse
            </tbody>
        </table></div></div>
        {{ $requests->links() }}
    </main>
</body>
</html>
