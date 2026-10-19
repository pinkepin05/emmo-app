@php
    $amountIcons = [
        'APPROVED' => 'bi-check-circle-fill',
        'NOT_APPROVED' => 'bi-x-circle-fill',
        'PENDING' => 'bi-hourglass-split',
        'REVISION' => 'bi-pencil-square',
    ];
    $amountGroups = [
        'APPROVED' => ['label' => 'Dikonfirmasi Finance', 'amount' => $totals->nominal_finance, 'count' => $totals->checked_count, 'tone' => 'approved'],
        'NOT_APPROVED' => ['label' => 'Total Ditolak', 'amount' => $totals->nominal_not_approved, 'count' => $totals->not_approved_count, 'tone' => 'rejected'],
        'PENDING' => ['label' => 'Pending / Belum Dicek', 'amount' => $totals->nominal_unchecked, 'count' => $totals->unchecked_count, 'tone' => 'pending'],
        'REVISION' => ['label' => 'Perlu Revisi (keputusan lama)', 'amount' => $totals->nominal_revision, 'count' => $totals->revision_count, 'tone' => 'revision'],
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
            <small>{{ $group['count'] }} transaksi</small>
        </a>
    @endforeach
</div>
@unless($isDashboard)
    <p class="recap-amount-note">Total ditolak mencakup penolakan penuh dan selisih yang tidak disetujui Finance. Pending hanya untuk pengajuan yang belum dicek.</p>
@endunless
