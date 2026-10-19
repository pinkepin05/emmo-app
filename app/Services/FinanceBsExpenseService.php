<?php

namespace App\Services;

use App\Models\BsUsageCheck;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/** The latest confirmed Finance decision is the single source of posted BS usage. */
class FinanceBsExpenseService
{
    public function owner(Model $source): ?int
    {
        return BsUsageCheckService::isEvent($source) ? (int) $source->user_id : $source->bsCashOwnerId();
    }

    public function period(Model $source): string
    {
        $periods = app(PeriodService::class);

        return BsUsageCheckService::isEvent($source)
            ? $periods->ofDate($source->tanggal) : $periods->normalize($source->periode);
    }

    public function sources(string $type, string $period, ?int $owner = null)
    {
        $model = BsUsageCheckService::TYPES[$type];
        $event = BsUsageCheckService::isEvent(new $model);
        $query = $model::query();
        if ($event) {
            $query->where('tanggal', '>=', $period.'-01')
                ->where('tanggal', '<', app(PeriodService::class)->next($period).'-01');
            if ($owner !== null) $query->where('user_id', $owner);
        } else {
            $query->where('periode', $period);
            if ($owner !== null) $query->forBsOwner($owner);
        }

        return $query->with([
            'financeChecks' => fn ($checks) => $checks->latestPerSource()->with('details'),
            ...($event ? ['user'] : ['user', 'bonSementara', 'details']),
        ]);
    }

    public function currentCheck(Model $source): ?BsUsageCheck
    {
        if ($source->status !== 'VALIDATED') return null;
        $check = $source->relationLoaded('financeChecks') ? $source->financeChecks->first()
            : $source->financeChecks()->latest('id')->with('details')->first();
        if (!$check || (int) $check->source_revision !== (int) $source->submission_version) return null;
        if (BsUsageCheckService::isEvent($source) && $check->source_revision === null) return null;

        if (!BsUsageCheckService::isEvent($source) && $check->details->contains(fn ($item) => $item->source_detail_id !== null)) {
            $original = $source->details->mapWithKeys(fn ($item) => [(int) $item->id => [
                (int) $item->cost_category_id, Money::minor($item->nominal), $item->remark ?? '',
            ]])->all();
            $checked = $check->details->mapWithKeys(fn ($item) => [(int) $item->source_detail_id => [
                (int) $item->cost_category_id, Money::minor($item->nominal_asal), $item->source_remark ?? '',
            ]])->all();
            ksort($original);
            ksort($checked);

            return $original === $checked ? $check : null;
        }

        // Historical category-level approvals retain their original amounts and matching rules.
        $original = BsUsageCheckService::isEvent($source)
            ? [0 => Money::minor($source->nominal)]
            : $source->details->groupBy('cost_category_id')->map(fn ($items) =>
                $items->sum(fn ($item) => Money::minor($item->nominal)))->all();
        $checked = $check->details->mapWithKeys(fn ($item) =>
            [(int) $item->cost_category_id => Money::minor($item->nominal_asal)])->all();
        ksort($original);
        ksort($checked);

        return $original === $checked ? $check : null;
    }

    public function approvedAmount(Model $source): int
    {
        $check = $this->currentCheck($source);

        return $check?->keputusan === 'APPROVED'
            ? $check->details->sum(fn ($item) => Money::minor($item->nominal_disetujui)) : 0;
    }

    public function expenses(int $owner, string $period): Collection
    {
        $period = app(PeriodService::class)->normalize($period);
        $rows = collect();
        foreach (BsUsageCheckService::TYPES as $type => $model) {
            foreach ($this->sources($type, $period, $owner)->where('status', 'VALIDATED')->get() as $source) {
                $check = $this->currentCheck($source);
                if ($check?->keputusan !== 'APPROVED') continue;
                $rows->push([
                    'type' => BsUsageCheckService::LABELS[$type], 'module' => $type,
                    'date' => $source->tanggal,
                    'description' => ($source->transaksi_id ?: $type.' #'.$source->id)
                        .(BsUsageCheckService::isEvent($source) && $source->description ? ' / '.$source->description : '')
                        .' (diinput: '.($source->user?->nama ?? '-').'; Finance: '.$check->document_no.')',
                    'nominal' => Money::decimal($this->approvedAmount($source)),
                    'url' => match ($type) {
                        'OPR' => route('transaksi_operasional.show', $source->id),
                        'PD' => route('transaksi_perjalanan_dinas.show', $source->transaksi_id),
                        'EDK' => route('perongkosan_event_dalam_kota.show', $source->id),
                        'ELK' => route('perongkosan_event_luar_kota.show', $source->id),
                    },
                ]);
            }
        }

        return $rows->sortBy('date')->values();
    }
}
