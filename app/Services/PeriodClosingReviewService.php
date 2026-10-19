<?php

namespace App\Services;

use App\Support\Money;
use Illuminate\Support\Collection;

class PeriodClosingReviewService
{
    public function __construct(private FinanceBsExpenseService $finance, private PeriodService $periods) {}

    public function token(string $period, Collection $blockers): string
    {
        return hash_hmac('sha256', json_encode([$this->periods->normalize($period), $blockers->values()->all()], JSON_THROW_ON_ERROR), config('app.key'));
    }

    /** Read the same source periods and current Finance decisions used by closing. */
    public function blockers(string $period): Collection
    {
        $period = $this->periods->normalize($period);
        $rows = collect();
        foreach (BsUsageCheckService::TYPES as $type => $model) {
            foreach ($this->finance->sources($type, $period)->orderBy('id')->get() as $source) {
                $event = BsUsageCheckService::isEvent($source);
                $pendingSubmission = !$event && in_array($source->status, ['DRAFT', 'PENDING_OWNER'], true);
                if (!$pendingSubmission) {
                    if ($this->finance->owner($source) === null || $source->status === 'REJECTED_OWNER') continue;
                    $check = $this->finance->currentCheck($source);
                    if ($check && in_array($check->keputusan, ['APPROVED', 'REJECTED'], true)) continue;
                }

                $stage = match ($source->status) {
                    'DRAFT' => 'DRAFT',
                    'PENDING_OWNER' => 'PENDING_OWNER',
                    default => 'FINANCE_PENDING',
                };
                $amount = $event ? Money::minor($source->nominal)
                    : $source->details->sum(fn ($item) => Money::minor($item->nominal));
                $rows->push([
                    'type' => $type, 'module' => BsUsageCheckService::LABELS[$type],
                    'id' => (int) $source->id, 'reference' => $source->transaksi_id ?: $type.' #'.$source->id,
                    'date' => $source->tanggal, 'period' => $period,
                    'user_id' => (int) $source->user_id, 'user_name' => $source->user?->nama ?? 'User tidak tersedia',
                    'status' => $source->status, 'stage' => $stage, 'amount' => Money::decimal($amount),
                    'reason' => match ($stage) {
                        'DRAFT' => 'Draft belum dikonfirmasi',
                        'PENDING_OWNER' => 'Menunggu persetujuan pemilik saldo BS',
                        default => $source->financeChecks->isNotEmpty() ? 'Hasil Finance perlu diselesaikan atau diperiksa ulang' : 'Belum diperiksa Finance',
                    },
                ]);
            }
        }

        return $rows;
    }
}
