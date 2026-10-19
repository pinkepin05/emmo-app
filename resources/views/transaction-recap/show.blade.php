@extends('layouts.app-with-sidebar', ['panel' => 'transaksi'])

@section('title', 'Detail Transaksi — '.$transaction->transaksi_id)

@section('content')
    @include('transaction-recap.assets')

    @php
    $fromMonitoring ??= false;
    $monitoringContext ??= null;
    $backToPeriod = false;
    $backFragment = 'pemakaian-bs';

    if ($fromMonitoring) {
        $backRoute = 'monitoring-finance.user';
        $backFilters = [
            'company' => $monitoringContext['company'], 'user' => $monitoringContext['user'],
            'periode' => $monitoringContext['periode'] ?? $transaction->periode, 'page' => $monitoringContext['page'] ?? 1,
        ];
        if (!empty($monitoringContext['jenis_filter'])) $backFilters['jenis_filter'] = $monitoringContext['jenis_filter'];
        $backFragment = 'monitoring-transactions';
    } else {
        $backFilters = request()->only(['pemakaian_periode', 'pemakaian_status', 'pemakaian_referensi', 'pemakaian_page']);
        $backFilters['pemakaian_periode'] = request()->query->has('pemakaian_periode') ? (request('pemakaian_periode') ?? '') : $transaction->periode;
        $backFilters['jenis'] = $jenis;
        $backRoute = 'transaction-recap.module';
        $backToPeriod = request('dari') === 'periode' && auth()->user()->can('akses.master-data');
        if ($backToPeriod) { $backRoute = 'periode.show'; $backFilters = ['periode' => $transaction->periode]; $backFragment = 'period-closing-review'; }
    }

    $workspaceContext = request()->only(['pemakaian_periode', 'pemakaian_status', 'pemakaian_referensi', 'pemakaian_page', 'dari', 'company', 'user', 'page', 'jenis_filter']);
    if ($fromMonitoring) {
        $workspaceContext = array_merge($workspaceContext, [
            'dari' => 'monitoring', 'company' => $monitoringContext['company'], 'user' => $monitoringContext['user'],
            'page' => $monitoringContext['page'] ?? 1, 'pemakaian_periode' => $monitoringContext['periode'] ?? $transaction->periode,
        ]);
        if (!empty($monitoringContext['jenis_filter'])) $workspaceContext['jenis_filter'] = $monitoringContext['jenis_filter'];
        else unset($workspaceContext['jenis_filter']);
    }
    if (request()->query->has('pemakaian_periode')) $workspaceContext['pemakaian_periode'] ??= '';

    $workspaceParams = ['jenis' => $jenis, 'source' => $transaction->id] + $workspaceContext;
    $detailUrl = route('transaction-recap.show', $workspaceParams);
    $reviewUrl = route('transaction-recap.show', $workspaceParams + ['pemeriksaan' => 1]);
   $isEvent = in_array($jenis, ['EDK', 'ELK'], true);

$statusLabel = match ($transaction->status) {
    'VALIDATED' => 'Dikonfirmasi',
    'DRAFT' => 'Belum dikonfirmasi',
    'PENDING_OWNER' => 'Menunggu pemohon',
    'REJECTED_OWNER' => 'Ditolak pemohon',
    default => $transaction->status,
};

$bs = $isEvent ? null : $transaction->bonSementara;

$isPerjalananDinas = $bs !== null
    && $bs->tipe_pengajuan === 'perjalanan_dinas';
@endphp
    <div class="recap-workspace">
       {{-- Tombol Kembali --}}
<a class="recap-back" href="{{ route($backRoute, $backFilters) }}#{{ $backFragment }}">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    @if($backToPeriod) Kembali ke Periode {{ $transaction->periode }}
    @elseif($fromMonitoring) Kembali ke Monitoring User
    @else Kembali ke Daftar {{ \App\Services\BsUsageCheckService::LABELS[$jenis] }}
    @endif
</a>

        <header class="recap-head">
            <div>
                <div class="recap-eyebrow">{{ \App\Services\BsUsageCheckService::LABELS[$jenis] }}</div>
                <h1>Detail Transaksi</h1>
                <p>{{ $transaction->transaksi_id ?: $jenis.' #'.$transaction->id }}</p>
            </div>
            <div class="recap-head-actions">
                <span class="recap-status {{ $transaction->status === 'VALIDATED' ? 'approved' : 'pending' }}">{{ $statusLabel }}</span>
                @if(!$reviewMode && $isFinance && $canReview)
                    <a class="btn btn-primary btn-sm" href="{{ $reviewUrl }}" data-review-action>
                        {{ $latestDecision ? 'Cek Ulang' : 'Periksa Pengajuan' }}
                    </a>
                @endif
            </div>
        </header>

        @include('bs-usage-checks.shared')
        @include('transaction-recap.correction')

        <nav class="recap-nav" aria-label="Bagian detail transaksi">
            <a href="{{ $detailUrl }}" @if(!$reviewMode) aria-current="page" @endif><i class="bi bi-file-earmark-text" aria-hidden="true"></i>Rincian &amp; Hasil</a>
        </nav>

        @if($reviewMode)
            @if($canReview)
                <h2 class="h5 mb-3">{{ $formData['draft'] ? 'Lanjutkan draft pemeriksaan' : ($latestDecision ? 'Cek ulang pengajuan' : 'Periksa pengajuan') }}</h2>
                @include('bs-usage-checks.form', $formData + ['unifiedWorkspace' => true])
            @else
                <div class="alert alert-info" role="status">{{ $periodClosed ? 'Periode transaksi sudah ditutup. Rincian dan hasil Finance tetap dapat dilihat.' : 'Menunggu konfirmasi pengajuan sebelum diperiksa Finance.' }}</div>
                <a class="btn btn-outline-primary" href="{{ $detailUrl }}">Lihat Rincian &amp; Hasil</a>
            @endif
        @else
        @if($closingExclusion)
            <div class="alert alert-warning" role="status"><strong>Dikecualikan saat tutup buku.</strong> Transaksi ini dikunci pada periode {{ $closingExclusion['period'] }} dan nominalnya tidak dihitung sebagai pengeluaran penutupan.</div>
        @endif
        <div class="recap-detail-grid">
            <div>
                {{-- ================= INFORMASI PENGAJUAN ================= --}}
                @include('transaction-recap.submission-info')

                {{-- 🎯 BARU: LAMPIRAN PERJALANAN DINAS (dari Pengajuan BS) --}}
                @if($isPerjalananDinas && !empty($bs->attachment_path))
                    <section class="recap-card mt-3">
                        <h2>Lampiran Perjalanan Dinas</h2>
                        <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: linear-gradient(135deg, #f0f4ff 0%, #e8efff 100%); border: 1px solid #c7d2fe;">
                            @php
                                $ext = strtolower(pathinfo($bs->attachment_path, PATHINFO_EXTENSION));
                                $icon = $ext === 'pdf' ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-success';
                            @endphp
                            <i class="bi {{ $icon }}" style="font-size: 2rem;"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size: 0.95rem;">
                                    {{ $bs->attachment_name ?? 'Dokumen Pendukung' }}
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-cloud-check me-1"></i>
                                    {{ strtoupper($ext) }} • Terunggah pada {{ \App\Support\AppTime::format($bs->created_at) }}
                                </small>
                            </div>
                            <a href="{{ asset('storage/' . $bs->attachment_path) }}"
                               target="_blank"
                               class="btn btn-sm btn-primary"
                               style="border-radius: 8px;">
                                <i class="bi bi-eye me-1"></i> Lihat / Unduh
                            </a>
                        </div>
                    </section>
                @endif

                {{-- ================= RINCIAN BIAYA ================= --}}
                @include('transaction-recap.cost-details')

                @if($isEvent)
                    @include('partials.event-confirmation', ['event' => $transaction, 'eventType' => $jenis])
                @endif

            </div>

        </div>
        @include('transaction-recap.decision-history')
        @endif
    </div>
@endsection
