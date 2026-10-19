@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Daftar Pengecekan Finance — '.\App\Services\BsUsageCheckService::LABELS[$moduleType])
@section('content')
    @include('transaction-recap.panel')
@endsection
