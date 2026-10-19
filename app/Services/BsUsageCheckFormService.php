<?php

namespace App\Services;

use App\Models\BsUsageCheckDraft;
use Illuminate\Database\Eloquent\Model;

class BsUsageCheckFormService
{
    public function __construct(private BsUsageCheckService $service) {}

    public function data(Model $transaction, string $jenis): array
    {
        abort_unless(app(PeriodService::class)->isOpen(app(FinanceBsExpenseService::class)->period($transaction)),
            403, 'Periode transaksi sudah ditutup. Pemeriksaan Finance dikunci.');
        abort_unless($transaction->status === 'VALIDATED', 403,
            'Transaksi belum dikonfirmasi oleh pemohon. Pemeriksaan Finance hanya dapat dilakukan pada transaksi yang sudah divalidasi.');
        $service = $this->service;
        $source = (int) $transaction->id;
        $transaction->load('user');
        $snapshot = $service->snapshot($transaction);
        $draft = BsUsageCheckDraft::where('jenis_transaksi', $jenis)->where('transaksi_pemakaian_id', $source)->first();
        $revisionOf = $transaction->financeChecks()->latest('id')->with('details')->first();
        $previous = $draft ? collect($draft->details) : ($revisionOf?->details->map(fn ($detail) => $detail->getAttributes()) ?? collect());
        if (!$draft && $revisionOf?->keputusan === 'REJECTED') $previous = collect();
        $previousLines = $previous->filter(fn ($item) => !empty($item['source_detail_id']))->keyBy('source_detail_id');
        $previousCategories = $previous->filter(fn ($item) => empty($item['source_detail_id']))
            ->keyBy(fn ($item) => (int) $item['cost_category_id']);
        $categoryCounts = $snapshot['items']->countBy(fn ($item) => (int) $item['cost_category_id']);
        $needsItemReview = false;
        $items = $snapshot['items']->map(function ($item) use ($previousLines, $previousCategories, $categoryCounts, &$needsItemReview) {
            $saved = $previousLines->get($item['source_detail_id']);
            $legacy = $previousCategories->get((int) $item['cost_category_id']);
            $ambiguous = !$saved && $legacy && $categoryCounts[(int) $item['cost_category_id']] > 1;
            $needsItemReview = $needsItemReview || $ambiguous;
            if (!$saved && !$ambiguous) $saved = $legacy;
            $same = $saved && (int) $saved['cost_category_id'] === (int) $item['cost_category_id']
                && \App\Support\Money::minor($saved['nominal_asal']) === \App\Support\Money::minor($item['nominal_asal'])
                && (empty($saved['source_detail_id']) || ($saved['source_remark'] ?? '') === ($item['source_remark'] ?? ''))
                && ($saved['source_photo_path'] ?? null) === ($item['source_photo_path'] ?? null);
            return $item + [
                'nominal_disetujui' => $ambiguous ? null : ($same ? $saved['nominal_disetujui'] : $item['nominal_asal']),
                'catatan' => $same ? ($saved['catatan'] ?? '') : '',
            ];
        });
        $previousFingerprint = $draft?->source_fingerprint ?? $revisionOf?->source_fingerprint;
        $sourceChanged = $previousFingerprint && $previousFingerprint !== $snapshot['fingerprint'];
        $defaults = [
            'tanggal_pengecekan' => $draft?->tanggal_pengecekan->toDateString() ?? \App\Support\AppTime::now()->toDateString(),
            'keputusan' => $draft && !isset(BsUsageCheckService::DECISIONS[$draft->keputusan]) ? '' : ($draft?->keputusan ?? 'APPROVED'),
            'catatan_keputusan' => $draft?->catatan_keputusan ?? '',
        ];

        return [
            'transaction' => $transaction, 'jenis' => $jenis, 'items' => $items,
            'sourceToken' => $service->token($jenis, $source, $snapshot['fingerprint']),
            'reviewToken' => $service->reviewToken($jenis, $source, $revisionOf, $draft),
            'draft' => $draft, 'revisionOf' => $revisionOf, 'defaults' => $defaults, 'sourceChanged' => $sourceChanged,
            'needsItemReview' => $needsItemReview,
            'historyCount' => $transaction->financeChecks()->count(),
        ];
    }
}
