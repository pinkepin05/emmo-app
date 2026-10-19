@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Monitoring Finance')
@section('content')
<link rel="stylesheet" href="{{ asset('css/finance-monitoring.css') }}">
<div class="container-fluid py-4 finance-monitoring-page">
    <h1 class="h3 mb-4">Monitoring Finance</h1>
    @include('finance-monitoring.partials.filters', ['action' => route('monitoring-finance.index'), 'periode' => $periode, 'hidden' => []])
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Kode</th><th>Nama Company</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($companies as $company)
                        <tr>
                            <td>{{ $company->code }}</td>
                            <td class="fw-semibold">{{ $company->nama }}</td>
                            <td>@if ($company->isActive()) <span class="badge bg-success">Aktif</span> @else <span class="badge bg-secondary">Nonaktif</span> @endif</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('monitoring-finance.company', ['company' => $company->id, 'periode' => $periode]) }}">Lihat User</a></td>
                        </tr>
                        @empty <tr><td colspan="4" class="text-center py-5 text-muted">Tidak ada Company.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection