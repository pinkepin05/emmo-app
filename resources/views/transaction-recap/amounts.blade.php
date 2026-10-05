@php
    $amountIcons = [
        'APPROVED' => 'bi-check-circle-fill',
        'REJECTED' => 'bi-x-circle-fill',
        'PENDING' => 'bi-hourglass-split',
        'DIFFERENCE' => 'bi-arrow-left-right',
        'REVISION' => 'bi-pencil-square',
        'RECHECK' => 'bi-arrow-repeat',
    ];
    $amountGroups = [
        'APPROVED' => ['label' => 'Disetujui Finance', 'amount' => $totals->nominal_finance, 'count' => $totals->checked_count, 'tone' => 'approved'],
        'REJECTED' => ['label' => 'Ditolak', 'amount' => $totals->nominal_rejected, 'count' => $totals->rejected_count, 'tone' => 'rejected'],
        'PENDING' => ['label' => 'Pending / Belum Dicek', 'amount' => $totals->nominal_unchecked, 'count' => $totals->unchecked_count, 'tone' => 'pending'],
        'DIFFERENCE' => ['label' => 'Selisih Persetujuan Finance', 'amount' => $totals->selisih, 'count' => $totals->different_count, 'tone' => 'difference'],
        'REVISION' => ['label' => 'Perlu Revisi (keputusan lama)', 'amount' => $totals->nominal_revision, 'count' => $totals->revision_count, 'tone' => 'revision'],
        'RECHECK' => ['label' => 'Perlu Cek Ulang', 'amount' => $totals->nominal_recheck, 'count' => $totals->recheck_count, 'tone' => 'recheck'],
    ];
@endphp
<div class="recap-breakdown recap-breakdown-separated">
    @foreach($amountGroups as $statusCode => $group)
        @continue($statusCode === 'REVISION' && !$group['count'])
        <a class="{{ $group['tone'] }}" data-amount-status="{{ $statusCode }}"
           href="{{ route('transaction-recap.module', array_merge($amountFilters, ['pemakaian_status' => $statusCode])) }}#pemakaian-bs"
           aria-label="{{ $amountLabel }}: {{ $group['label'] }}, {{ $money($group['amount']) }}, {{ $group['count'] }} transaksi">
            <span>
                <i class="bi {{ $amountIcons[$statusCode] }} amount-icon" aria-hidden="true"></i>
                {{ $group['label'] }}
            </span>
            <strong>{{ $money($group['amount']) }}</strong>
            <small>{{ $group['count'] }} {{ $statusCode === 'DIFFERENCE' ? 'transaksi dengan selisih' : 'transaksi' }}</small>
        </a>
    @endforeach
</div>
<p class="recap-amount-note">Pending hanya untuk pengajuan yang belum dicek. Selisih persetujuan = nominal pengajuan − nominal disetujui Finance.</p>