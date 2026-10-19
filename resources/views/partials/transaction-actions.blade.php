@php
$monitoringReadOnly = $monitoringReadOnly ?? false;
$isEvent = in_array($transactionType, ['edk', 'elk'], true);
$periods = app(\App\Services\PeriodService::class);
$canDelete = !$monitoringReadOnly
    && auth()->user()->canDeleteTransaction($transaction)
    && ($isEvent || $transaction->status === 'DRAFT')
    && $periods->isOpen($isEvent ? $periods->ofDate($transaction->tanggal) : $transaction->periode);
@endphp
<div class="transaction-actions" data-transaction-actions>
    <a href="{{ $monitoringReadOnly ? ($transaction->monitoring_detail_url ?? $detailUrl) : $detailUrl }}"
       class="transaction-action transaction-action-detail"
       aria-label="Detail transaksi {{ $transaction->transaksi_id }}">
        <i class="bi bi-eye" aria-hidden="true"></i> Detail
    </a>
    @if($canDelete)
        <form action="{{ $deleteUrl }}" method="POST"
              onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="transaction-action transaction-action-delete"
                    aria-label="Hapus transaksi {{ $transaction->transaksi_id }}">
                <i class="bi bi-trash" aria-hidden="true"></i> Hapus
            </button>
        </form>
    @endif
</div>
