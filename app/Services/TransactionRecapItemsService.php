<?php

namespace App\Services;

use App\Models\BsUsageCheck;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;

class TransactionRecapItemsService
{
    public function build(Model $transaction, iterable $items, ?BsUsageCheck $check, bool $isFinance, bool $currentPhotos = true): array
    {
        $isEvent = BsUsageCheckService::isEvent($transaction);
        $details = $isEvent ? collect() : $transaction->details()->get()->keyBy('id');
        $eventPhotos = [];
        if ($isEvent && $currentPhotos) {
            foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $field) {
                if ($transaction->$field) $eventPhotos[] = asset('storage/'.$transaction->$field);
            }
        }

        $rows = [];
        foreach ($check ? $check->details : $items as $item) {
            $original = Money::minor($item['nominal_asal']);
            $approved = $check ? Money::minor($item['nominal_disetujui']) : null;
            $detail = $details->get($item['source_detail_id'] ?? null);
            $photos = $eventPhotos;
            if (!$isEvent) {
                if ($check && $isFinance && $item['source_photo_path']) {
                    $photos[] = route('transaction-detail-photo.history', $item['id']);
                } elseif ($detail?->photo_path && (!$check || $detail->photo_path === $item['source_photo_path'])) {
                    // A current photo can illustrate a saved item only when it is the same evidence.
                    $photos[] = $detail->photo_url;
                }
            }
            $rows[] = [
                'item' => $item['cost_category_name'],
                'remark' => $item['source_remark'] ?? null,
                'photos' => $photos,
                'original' => $original,
                'approved' => $approved,
                'difference' => $approved === null ? null : $original - $approved,
                'internal_note' => $check && $isFinance ? $item['catatan'] : null,
            ];
        }

        $original = array_sum(array_column($rows, 'original'));
        $approved = $check ? array_sum(array_column($rows, 'approved')) : null;

        return [
            'rows' => $rows,
            'original' => $original,
            'approved' => $approved,
            'difference' => $approved === null ? null : $original - $approved,
            'grouped' => $check && !$isEvent && $check->details->isNotEmpty()
                && $check->details->every(fn ($item) => $item->source_detail_id === null),
        ];
    }
}
