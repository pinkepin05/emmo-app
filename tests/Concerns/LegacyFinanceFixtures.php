<?php

namespace Tests\Concerns;

use App\Models\BsUsageCheck;
use App\Services\BsUsageCheckService;
use App\Services\DocumentNumberService;

trait LegacyFinanceFixtures
{
    private function legacyFinanceRevision($source, string $type, string $amount, ?string $note): BsUsageCheck
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($source, $type, $amount, $note) {
        $source->refresh()->load('user');
        $snapshot = app(BsUsageCheckService::class)->snapshot($source);
        $check = BsUsageCheck::create([
            'document_no' => app(DocumentNumberService::class)->next('CEKBS', '2026-10-01'),
            'tanggal_pengecekan' => '2026-10-01', 'periode' => '2026-10',
            'jenis_transaksi' => $type, 'transaksi_pemakaian_id' => $source->id,
            'no_referensi' => $source->transaksi_id, 'user_id' => $source->user_id,
            'user_name' => $source->user->nama, 'user_checker_id' => 2, 'checker_name' => 'Finance',
            'source_fingerprint' => $snapshot['fingerprint'], 'source_revision' => $source->submission_version,
            'keputusan' => 'REVISION', 'catatan_keputusan' => $note,
            'revision_of_id' => $source->financeChecks()->latest('id')->value('id'),
        ]);
        $check->details()->createMany($snapshot['items']->map(fn ($item) => $item + [
            'nominal_disetujui' => $amount, 'catatan' => 'Catatan internal Finance',
        ])->all());
        return $check;
        });
    }
}
