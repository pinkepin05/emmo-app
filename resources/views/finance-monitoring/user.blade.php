@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Monitoring Finance — ' . $user->nama)
@section('content')
<link rel="stylesheet" href="{{ asset('css/finance-monitoring.css') }}">
<div class="container-fluid py-4 finance-monitoring-page">
    <a href="{{ $back_url }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke {{ $company->nama }}</a>
    <h1 class="h3 mt-2 mb-1">{{ $user->nama }}</h1>
    <div class="text-muted mb-4">Company: <strong>{{ $company->nama }}</strong> · Periode: <strong>{{ $periode }}</strong> @if (!$user->isActive()) <span class="badge bg-secondary ms-2">Nonaktif</span> @endif</div>

    @include('finance-monitoring.partials.filters', ['action' => route('monitoring-finance.user', ['company' => $company->id, 'user' => $user->id]), 'periode' => $periode, 'hidden' => ['jenis_filter' => $current_filter]])
    @include('finance-monitoring.partials.cash-summary', ['summary' => $cash_summary, 'title' => 'Stok Kas User'])
    @include('finance-monitoring.partials.transactions', ['transactions' => $transactions, 'user' => $user, 'company' => $company, 'periode' => $periode, 'current_filter' => $current_filter])
    
{{-- BARU: pilihan modul --}}
@include('finance-monitoring.partials.modules')

@include('finance-monitoring.partials.transactions', ['transactions' => $transactions, 'user' => $user, 'company' => $company, 'periode' => $periode, 'current_filter' => $current_filter])
</div>
@endsection