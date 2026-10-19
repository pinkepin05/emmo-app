@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Riwayat Pengajuan & Pencairan BS')
@section('content')
    @include('transaction-recap.assets')
    @php
        $titles = [
            'diajukan' => 'Riwayat Pengajuan BS',
            'dicairkan' => 'Pengajuan BS yang Dicairkan',
            'pending' => 'Pengajuan BS Menunggu Persetujuan',
            'ditolak' => 'Penolakan dan Selisih Pencairan BS',
        ];

        $amountLabels = [
            'diajukan' => 'Diajukan',
            'dicairkan' => 'Dicairkan',
            'pending' => 'Pending',
            'ditolak' => 'Ditolak / Selisih',
        ];
        $money = fn ($amount) => 'Rp '.number_format($amount, 2, ',', '.');
    @endphp
    <section class="recap-workspace">
        <a class="recap-back" href="{{ route('dashboard.transaksi', array_intersect_key($filters, ['pemakaian_periode' => true])) }}#pemakaian-bs"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke Dashboard Transaksi</a>
        <header class="recap-head"><div><div class="recap-eyebrow">{{ auth()->user()->can('akses.approval-finance') || auth()->user()->canSeeAllTransactions() ? 'Rekap seluruh user' : 'Rekap pengajuan Anda' }}</div><h1>{{ $titles[$stage] }}</h1></div></header>
        <form class="recap-filter" method="GET" action="{{ route('transaction-recap.funding') }}">
            <input type="hidden" name="tahap" value="{{ $stage }}">
            <div class="recap-filter-grid recap-filter-grid-module">
                <div><label for="funding-reference">Nomor pengajuan{{ $isPayment ? ' / pencairan' : '' }}</label><input class="form-control" type="search" id="funding-reference" name="pemakaian_referensi" maxlength="64" value="{{ $filters['pemakaian_referensi'] ?? '' }}"></div>
                <div><label for="funding-period">Periode {{ $isPayment ? 'pencairan' : 'pengajuan' }}</label><input class="form-control" type="month" id="funding-period" name="pemakaian_periode" value="{{ $filters['pemakaian_periode'] }}"></div>
                <div class="recap-filter-actions"><button class="btn btn-primary" type="submit">Tampilkan</button><a class="btn btn-outline-secondary" href="{{ route('transaction-recap.funding', ['tahap' => $stage, 'pemakaian_periode' => '']) }}">Semua periode</a></div>
            </div>
        </form>
        <div class="recap-section-head"><h2>{{ $rows->total() }} {{ $isPayment ? 'pencairan' : 'pengajuan' }}</h2><p>Total seluruh hasil filter: <strong>{{ $money($total) }}</strong></p></div>
        @if($isPayment)<p class="small text-muted">Persetujuan Finance dan pencairan BS dicatat bersamaan. Nominal di sini mengikuti dokumen pencairan yang sudah dikonfirmasi.</p>@endif
        <div class="recap-card p-0">
            <div class="table-responsive">
                <table class="table small mb-0 align-middle">
                    <thead><tr><th scope="col">Nomor Pengajuan</th>@if($isPayment)<th scope="col">Nomor Pencairan</th>@endif<th scope="col">Tanggal {{ $isPayment ? 'Pencairan' : 'Pengajuan' }}</th><th scope="col">User</th><th scope="col">Status Pengajuan</th><th scope="col" class="text-end">Nominal {{ $amountLabels[$stage] }}</th><th scope="col">Detail</th></tr></thead>
                    <tbody>
                        @forelse($rows as $record)
                            @php
                                $bs = $isPayment ? $record->pengajuanBs : $record;
                                $detailUrl = auth()->user()->can('akses.approval-finance') ? route('approval-finance.detail', $bs->id)
                                    : (auth()->user()->can('akses.pengajuan-bs') ? route('pengajuan-bs.show', $bs->id) : null);
                            @endphp
                            <tr>
                                <td>{{ $bs->document_no }}</td>@if($isPayment)<td>{{ $record->document_no }}</td>@endif
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($isPayment ? $record->tanggal_cair : $bs->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $bs->user?->nama ?? 'User tidak tersedia' }}</td><td>{{ str_replace('_', ' ', $bs->status) }}</td>
                                <td class="text-end text-nowrap">{{ $money($record->nominal_rekap) }}</td>
                                <td>@if($detailUrl)<a class="btn btn-sm btn-outline-primary" href="{{ $detailUrl }}">Detail</a>@else<span class="text-muted">—</span>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isPayment ? 7 : 6 }}" class="text-center text-muted p-4">Belum ada {{ $isPayment ? 'pencairan' : 'pengajuan' }} sesuai filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rows->hasPages())<nav aria-label="Halaman riwayat BS">{{ $rows->links() }}</nav>@endif
    </section>
@endsection
