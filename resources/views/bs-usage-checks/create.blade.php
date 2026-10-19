@extends('layouts.app-with-sidebar')
@section('title', 'Form Pengecekan Pemakaian BS')
@section('content')
    @include('transaction-recap.assets')
    <div class="recap-workspace">
        <a class="recap-back" href="{{ route('bs-usage-checks.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke daftar pengecekan</a>
        <header class="recap-head"><div><div class="recap-eyebrow">Pemeriksaan Finance</div><h1>{{ $draft ? 'Lanjutkan draft pemeriksaan' : ($historyCount ? 'Periksa kembali pengajuan' : 'Periksa pengajuan') }}</h1><p>Simpan pekerjaan sebagai draft atau konfirmasikan hasil pemeriksaan. Penyesuaian nominal dilakukan secara terpisah untuk setiap item rincian biaya.</p></div></header>
        @include('bs-usage-checks.shared')
        @include('bs-usage-checks.form')
    </div>
@endsection
