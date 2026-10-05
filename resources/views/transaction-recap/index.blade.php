@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])
@section('title', 'Daftar '.\App\Services\BsUsageCheckService::LABELS[$moduleType])
@section('content')
    @include('transaction-recap.panel')
@endsection
