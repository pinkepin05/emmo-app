@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Monitoring Finance — ' . $company->nama)
@section('content')
<link rel="stylesheet" href="{{ asset('css/finance-monitoring.css') }}">
<div class="container-fluid py-4 finance-monitoring-page">
    <a href="{{ route('monitoring-finance.index', ['periode' => $periode]) }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h1 class="h3 mt-2 mb-1">{{ $company->nama }}</h1>
    <div class="text-muted mb-4">Periode: <strong>{{ $periode }}</strong> @if (!$company->isActive()) <span class="badge bg-secondary ms-2">Nonaktif</span> @endif</div>

    @include('finance-monitoring.partials.filters', ['action' => route('monitoring-finance.company', ['company' => $company->id]), 'periode' => $periode, 'hidden' => []])
    @include('finance-monitoring.partials.cash-summary', ['summary' => $totals, 'title' => 'Ringkasan Company'])

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>User</th><th>Role</th><th>Status</th><th class="text-end">Masuk</th><th class="text-end">Keluar</th><th class="text-end">Sisa Saldo</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($users as $row)
                        @php $user = $row['user']; $userTotals = $row['totals']; @endphp
                        <tr>
                            <td class="fw-semibold">{{ $user->nama }}</td>
                            <td>{{ $user->role?->nama ?? '-' }}</td>
                            <td>@if ($user->isActive()) <span class="badge bg-success">Aktif</span> @else <span class="badge bg-secondary">Nonaktif</span> @endif</td>
                            <td class="text-end text-nowrap">Rp {{ number_format((float) $userTotals['total_masuk'], 2, ',', '.') }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format((float) $userTotals['total_keluar'], 2, ',', '.') }}</td>
                            <td class="text-end text-nowrap fw-bold">Rp {{ number_format((float) $userTotals['saldo_berjalan'], 2, ',', '.') }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('monitoring-finance.user', ['company' => $company->id, 'user' => $user->id, 'periode' => $periode]) }}">Lihat Transaksi</a></td>
                        </tr>
                        @empty <tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada user.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($users->hasPages()) <div class="card-footer bg-white">{{ $users->links() }}</div> @endif
    </div>
</div>
@endsection