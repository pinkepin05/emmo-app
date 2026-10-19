@extends('layouts.app-with-sidebar')
@section('title', 'Pengecekan Pemakaian BS — Finance')
@section('content')
    @include('transaction-recap.assets')
    @php
        $isDetail = $filters['tampilan'] === 'rincian';
        $isDraft = $filters['tampilan'] === 'draft';
        $selection = array_diff_key($filters, ['status_finance' => true, 'mode' => true]);
        $status = $filters['status_finance'] ?? '';
        $hasAdvanced = !empty($filters['tanggal_awal']) || !empty($filters['tanggal_akhir']) || !empty($filters['periode']) || !empty($filters['status_selisih']);
        $money = fn ($amount) => 'Rp '.number_format($amount, 2, ',', '.');
    @endphp
    <div class="recap-workspace" id="finance-workspace">
        <a class="recap-back" href="{{ route('dashboard.transaksi') }}#pemakaian-bs"><i class="bi bi-arrow-left" aria-hidden="true"></i>Kembali ke Dashboard Transaksi</a>
        <header class="recap-head">
            <div><div class="recap-eyebrow">Pemeriksaan dan laporan Finance</div><h1>Pengecekan Pemakaian BS</h1><p>Buka Detail Transaksi untuk memeriksa pengajuan, melihat hasil Finance, dan menelusuri jejak auditnya.</p></div>
        </header>
        @include('bs-usage-checks.shared')
        <nav class="recap-nav" aria-label="Tampilan pengecekan Finance">
            <a href="{{ route('bs-usage-checks.index', array_merge($selection, ['tampilan' => 'transaksi'])) }}" @if(!$isDetail && !$isDraft) aria-current="page" @endif><i class="bi bi-list-check" aria-hidden="true"></i>Daftar transaksi</a>
            <a href="{{ route('bs-usage-checks.index', array_merge(array_diff_key($selection, ['status_selisih' => true]), ['tampilan' => 'draft'])) }}" @if($isDraft) aria-current="page" @endif><i class="bi bi-save" aria-hidden="true"></i>Draft pemeriksaan</a>
        </nav>
        <form method="GET" action="{{ route('bs-usage-checks.index') }}" class="recap-filter" aria-label="Filter pengecekan dan laporan Finance">
            <input type="hidden" name="tampilan" value="{{ $filters['tampilan'] }}">
            @if(!$isDetail && !$isDraft)<input type="hidden" name="status_finance" value="{{ $status }}">@endif
            @if(!empty($filters['source_id']))
                <input type="hidden" name="source_id" value="{{ $filters['source_id'] }}">
                <input type="hidden" name="jenis_source" value="{{ $filters['jenis_source'] }}">
                <p class="small text-primary">Menampilkan satu referensi transaksi. Pilih Reset untuk melihat seluruh referensi.</p>
            @endif
            <div class="recap-filter-grid">
                <div class="recap-filter-search"><label for="referensi">Nomor pengajuan</label><input class="form-control" type="search" id="referensi" name="referensi" maxlength="64" value="{{ $filters['referensi'] ?? '' }}" placeholder="Cari nomor transaksi…"></div>
                <div><label for="jenis">Modul transaksi</label><select class="form-select" name="jenis" id="jenis"><option value="">Semua modul</option>@foreach(\App\Services\BsUsageCheckService::LABELS as $value => $label)<option value="{{ $value }}" @selected(($filters['jenis'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label for="user_id">Pemilik pengajuan</label><select class="form-select" name="user_id" id="user_id"><option value="">Semua user</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->nama }}</option>@endforeach</select></div>
                <div class="recap-filter-actions"><button class="btn btn-primary btn-sm">Tampilkan</button><a class="btn btn-outline-secondary btn-sm" href="{{ route('bs-usage-checks.index', ['tampilan' => $filters['tampilan']]) }}">Reset</a></div>
            </div>
            @if($isDetail)
                <div class="mt-3"><label for="mode">Riwayat yang ditampilkan</label><select class="form-select" name="mode" id="mode"><option value="terakhir" @selected($filters['mode'] === 'terakhir')>Cek terakhir per transaksi</option><option value="semua" @selected($filters['mode'] === 'semua')>Semua riwayat pemeriksaan</option></select></div>
            @endif
            <details class="mt-3" @if($hasAdvanced) open @endif>
                <summary class="small">{{ $isDraft ? 'Filter tanggal dan periode draft' : 'Filter tanggal, periode, dan selisih' }}</summary>
                <div class="row g-3 mt-0">
                    <div class="col-12 col-md-3"><label for="tanggal_awal">Tanggal pengecekan dari</label><input type="date" class="form-control" name="tanggal_awal" id="tanggal_awal" value="{{ $filters['tanggal_awal'] ?? '' }}"></div>
                    <div class="col-12 col-md-3"><label for="tanggal_akhir">Sampai</label><input type="date" class="form-control" name="tanggal_akhir" id="tanggal_akhir" value="{{ $filters['tanggal_akhir'] ?? '' }}"></div>
                    <div class="col-12 col-md-3"><label for="periode">Periode pengecekan</label><input type="month" class="form-control" name="periode" id="periode" value="{{ $filters['periode'] ?? '' }}"></div>
                    @if(!$isDraft)<div class="col-12 col-md-3"><label for="status_selisih">Status selisih per item</label><select class="form-select" name="status_selisih" id="status_selisih"><option value="">Semua</option><option value="sesuai" @selected(($filters['status_selisih'] ?? '') === 'sesuai')>Sesuai — seluruh item</option><option value="tidak_sesuai" @selected(($filters['status_selisih'] ?? '') === 'tidak_sesuai')>Tidak sesuai — ada selisih</option></select></div>@endif
                </div>
                <p class="recap-filter-note mt-2 mb-0">{{ $isDraft ? 'Tanggal dan periode mengikuti tanggal pengecekan yang disimpan pada draft.' : 'Tanggal dan periode mengikuti pemeriksaan Finance yang dikonfirmasi. Transaksi yang belum diperiksa tidak masuk hasil filter tanggal/periode. Cek terakhir dipilih berdasarkan urutan penyimpanan sebelum filter diterapkan.' }}</p>
            </details>
           {{-- ... (bagian atas file tetap sama) ... --}}
            @if(!$isDetail && !$isDraft)
                <nav class="recap-status-tabs" aria-label="Filter status Pengecekan Finance">
                    @foreach(['' => 'Semua status', 'PENDING' => 'Belum Dicek', 'REVIEWED' => 'Sudah Dicek'] as $value => $label)
                        <a href="{{ route('bs-usage-checks.index', array_merge($filters, ['status_finance' => $value])) }}" @if($status === $value) aria-current="true" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
            @endif
{{-- ... (bagian bawah file tetap sama) ... --}}
        </form>
        @if($isDetail)
            @include('bs-usage-checks.report')
        @elseif($isDraft)
            @include('bs-usage-checks.drafts')
        @else
            @include('bs-usage-checks.sources')
        @endif
    </div>
@endsection
