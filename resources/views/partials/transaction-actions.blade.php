@php
    $menuId = 'transaction-actions-' . $transactionType . '-' . $transaction->getKey();
    $canChange = $transaction->status === 'DRAFT'
        && auth()->user()->canAccessTransaction($transaction)
        && app(\App\Services\PeriodService::class)->isOpen($transaction->periode);
@endphp
<div class="transaction-actions" data-transaction-actions>
    <button type="button" class="btn-action detail" data-action-toggle
            aria-expanded="false" aria-controls="{{ $menuId }}"
            aria-label="Aksi transaksi {{ $transaction->transaksi_id }}">
        Aksi <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div id="{{ $menuId }}" class="transaction-actions-menu" data-action-menu hidden>
        <a href="{{ $detailUrl }}" class="transaction-actions-item">
            <i class="bi bi-eye" aria-hidden="true"></i> Detail
        </a>
        @if($canChange)
            @isset($confirmUrl)
                <form action="{{ $confirmUrl }}" method="POST">
                    @csrf
                    <button type="submit" class="transaction-actions-item text-success"
                            onclick="return confirm('Konfirmasi transaksi ini? Pemakaian BS akan dibukukan. Transaksi final tidak dapat diedit.')">
                        <i class="bi bi-check-circle" aria-hidden="true"></i> Konfirmasi
                    </button>
                </form>
            @endisset
            <form action="{{ $deleteUrl }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="transaction-actions-item text-danger"
                        onclick="return confirm('Yakin hapus draft transaksi ini?')">
                    <i class="bi bi-trash" aria-hidden="true"></i> Hapus
                </button>
            </form>
        @endif
    </div>
</div>
