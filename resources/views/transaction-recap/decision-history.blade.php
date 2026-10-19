@include('bs-usage-checks.audit-trail', [
    'auditHistory' => $financeHistory,
    'auditReference' => $transaction->transaksi_id ?: $jenis.' #'.$transaction->id,
    'auditSelectedId' => $selectedDecision?->id,
])
