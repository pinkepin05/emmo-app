<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Periode {{ $periode }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{background:#f8fafc;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#0f172a;}
        .card-x{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;}
        .table-x td,.table-x th{padding:.6rem .9rem;font-size:.85rem;border-bottom:1px solid #eef2f7;vertical-align:middle;}
        .table-x tbody tr:last-child td{border-bottom:none;}
        .badge-status{padding:.4em .8em;border-radius:6px;font-weight:600;font-size:.75rem;}
        .sum-card{font-size:.78rem;color:#64748b;}
        .sum-card b{display:block;font-size:1.05rem;color:#0f172a;margin-top:.15rem;}
        .btn-nav{background:#fff;border:1px solid #e2e8f0;color:#64748b;padding:.5rem 1rem;border-radius:8px;text-decoration:none;font-size:.875rem;}
    </style>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @include('partials.mobile-assets')
</head>
<body class="py-4">
    @include('partials.application-time')
<div class="container" style="max-width:1050px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-clock-history"></i> Riwayat Periode {{ $periode }}
            <span class="badge-status text-white ms-2 {{ $status === 'OPEN' ? 'bg-success' : 'bg-secondary' }}">{{ $status }}</span>
        </h4>
        <a href="{{ route('periode.index') }}" class="btn-nav"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    @if($row && $row->closed_at)
        <div class="alert alert-secondary small" style="border-radius:12px;">
            <i class="bi bi-lock-fill me-1"></i> Ditutup pada {{ \App\Support\AppTime::format($row->closed_at) }}
            @if($closedByName) oleh <strong>{{ $closedByName }}</strong> @endif
            @if($row->reopened_at)
                · <i class="bi bi-unlock-fill me-1"></i> Dibuka kembali {{ \App\Support\AppTime::format($row->reopened_at) }} — {{ $row->reopen_reason }}
            @endif
        </div>
    @endif

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    @if($status === 'OPEN')
        @include('periode.closing-review')
    @endif
    @include('periode.closing-history')

    {{-- RINGKASAN NOMINAL PER PERIODE --}}
    <p class="small text-muted">Nominal transaksi di bawah mengikuti pengajuan asal. Pengeluaran dan saldo penutupan mengikuti hasil Finance pada Stok Kas; transaksi yang dikecualikan tetap tercantum sebagai arsip.</p>
    <div class="row g-3 mb-4">
        <div class="col"><div class="card-x p-3 sum-card">BS Disetujui ({{ $bs->count() }})<b>Rp {{ number_format($totals['bsApproved'], 0, ',', '.') }}</b></div></div>
        <div class="col"><div class="card-x p-3 sum-card">Operasional ({{ $operasional->count() }})<b>Rp {{ number_format($totals['operasional'], 0, ',', '.') }}</b></div></div>
        <div class="col"><div class="card-x p-3 sum-card">Perjalanan Dinas ({{ $perdin->count() }})<b>Rp {{ number_format($totals['perdin'], 0, ',', '.') }}</b></div></div>
        <div class="col"><div class="card-x p-3 sum-card">Event Dalam ({{ $eventDalam->count() }})<b>Rp {{ number_format($totals['eventDalam'], 0, ',', '.') }}</b></div></div>
        <div class="col"><div class="card-x p-3 sum-card">Event Luar ({{ $eventLuar->count() }})<b>Rp {{ number_format($totals['eventLuar'], 0, ',', '.') }}</b></div></div>
    </div>

    @php
        $sections = [
            ['Pengajuan Bon Sementara (BS)', $bs, 'pengajuan-bs.show', 'nominal_approved'],
            ['Operasional Area Sales', $operasional, 'transaksi_operasional.show', null],
            ['Perjalanan Dinas', $perdin, 'transaksi_perjalanan_dinas.show', null],
            ['Event Marketing Dalam Kota', $eventDalam, 'perongkosan_event_dalam_kota.show', 'nominal'],
            ['Event Marketing Luar Kota', $eventLuar, 'perongkosan_event_luar_kota.show', 'nominal'],
        ];
    @endphp

    @foreach($sections as [$title, $rows, $routeShow, $nominalCol])
        <div class="card-x mb-4">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <strong>{{ $title }}</strong>
                <span class="text-muted small">{{ $rows->count() }} transaksi</span>
            </div>
            @if($rows->isEmpty())
                <div class="p-3 text-muted small">Tidak ada transaksi pada periode ini.</div>
            @else
                <div class="table-responsive">
                    <table class="table-x w-100 mb-0">
                        <thead>
                            <tr>
                                <th>ID</th><th>Tanggal</th><th>User</th><th>Deskripsi</th><th>Status</th>
                                @if($nominalCol)<th class="text-end">Nominal</th>@endif
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $r)
                            <tr>
                                <td><code>{{ $r->document_no ?? $r->transaksi_id }}</code></td>
                                <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}
                                    @if(in_array($routeShow, ['transaksi_operasional.show', 'transaksi_perjalanan_dinas.show']) && substr($r->tanggal, 0, 7) !== $periode)
                                        <div class="small text-muted">Dicatat di periode {{ $periode }}</div>
                                    @endif
                                </td>
                                <td>{{ $r->user_nama ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($r->description ?? '-', 60) }}</td>
                                <td><span class="badge text-bg-{{ ($r->status ?? '') === 'VALIDATED' ? 'success' : 'secondary' }}">{{ match($r->status ?? '') { 'DRAFT' => 'Draft', 'PENDING_OWNER' => 'Menunggu Pemohon', 'REJECTED_OWNER' => 'Ditolak Pemohon', 'VALIDATED' => 'Dikonfirmasi', default => $r->status ?? '—' } }}</span></td>
                                @if($nominalCol)
                                    <td class="text-end">{{ number_format($r->{$nominalCol} ?? 0, 0, ',', '.') }}</td>
                                @endif
                                <td class="text-end">
                                    @php($detailType = match($routeShow) { 'transaksi_operasional.show' => 'OPR', 'transaksi_perjalanan_dinas.show' => 'PD', 'perongkosan_event_dalam_kota.show' => 'EDK', 'perongkosan_event_luar_kota.show' => 'ELK', default => null })
                                    <a class="btn btn-sm btn-outline-primary" href="{{ $detailType ? route('transaction-recap.show', ['jenis' => $detailType, 'source' => $r->id, 'pemakaian_periode' => $periode, 'dari' => 'periode']) : route($routeShow, $r->id) }}">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach
</div>
</body>
</html>
