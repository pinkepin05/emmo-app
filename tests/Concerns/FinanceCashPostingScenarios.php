<?php

namespace Tests\Concerns;

use App\Models\BsUsageCheck;
use App\Models\BsUsageCheckDraft;
use App\Models\Periode;
use App\Models\User;
use App\Services\BonSementaraService;
use App\Services\BsCashFlowService;
use App\Services\BsPeriodClosingService;
use App\Services\BsUsageCheckService;
use App\Services\StockCashFlowService;
use App\Support\Money;

trait FinanceCashPostingScenarios
{
    public function test_finance_cash_opening_continues_published_september_closing_and_preserves_archives(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 7));
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        Periode::create(['periode' => '2026-10', 'status' => 'OPEN']);
        $oldBs = \App\Models\BsCashFlowSnapshot::create([
            'user_id' => 1, 'periode' => '2026-09', 'saldo_awal' => 21100000, 'total_masuk' => 9200000,
            'total_keluar' => 11370000, 'saldo_akhir' => 18930000, 'opening_final' => true,
        ]);
        $oldStock = \App\Models\StockCashFlowSnapshot::create([
            'user_id' => 1, 'periode' => '2026-09', 'saldo_awal' => 19150000, 'total_masuk' => 9200000,
            'total_keluar_bs' => 11370000, 'total_event_luar' => 200000, 'total_keluar' => 11570000,
            'saldo_akhir' => 16780000, 'opening_final' => true,
        ]);
        $frozenBs = $oldBs->fresh()->getAttributes();
        $frozenStock = $oldStock->fresh()->getAttributes();
        $bs = $this->bs(['tanggal' => '2026-10-01', 'periode' => '2026-10', 'nominal_approved' => 1200000]);
        $source = $this->expense($bs, status: 'VALIDATED', amount: '100000');
        $this->dashboardFinanceCheck($source, date: '2026-10-07');
        for ($refresh = 0; $refresh < 2; $refresh++) {
            $this->assertFinanceCash('100000.00', '17880000.00', period: '2026-10');
            $stock = app(StockCashFlowService::class)->recalculateSnapshot(1, '2026-10');
            $this->assertSame('16780000.00', $stock->saldo_awal);
        }
        $this->get('/cash-flow')->assertOk()->assertSee('Rp 16.780.000')->assertSee('Rp 17.880.000');
        $book = $this->cashFlowWorkbook('/cash-flow/202610/export', 'stok-kas-202610.xlsx');
        $this->assertEquals(16780000, $book->getSheet(0)->getCell('C2')->getValue());
        $this->assertSame($frozenBs, $oldBs->fresh()->getAttributes());
        $this->assertSame($frozenStock, $oldStock->fresh()->getAttributes());
        $this->travelTo(now()->setDate(2026, 11, 7));
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-10')->value('id'));
        $this->assertFinanceCash('0.00', '17880000.00', period: '2026-11');
    }

    public function test_finance_cash_carries_zero_negative_and_unfinalized_stock_closings_by_owner(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 7));
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        foreach ([1 => '0.00', 3 => '-200000.25'] as $owner => $closing) {
            \App\Models\BsCashFlowSnapshot::create([
                'user_id' => $owner, 'periode' => '2026-09', 'saldo_akhir' => 900000, 'opening_final' => true,
            ]);
            \App\Models\StockCashFlowSnapshot::create([
                'user_id' => $owner, 'periode' => '2026-09', 'saldo_akhir' => $closing, 'opening_final' => true,
            ]);
            $this->assertFinanceCash('0.00', $closing, $owner, '2026-10');
        }
        \App\Models\StockCashFlowSnapshot::where('user_id', 1)->where('periode', '2026-09')->update(['opening_final' => false]);
        $this->assertFalse(app(BsCashFlowService::class)->calculate(1, '2026-10')['opening_final']);
        Periode::where('periode', '2026-09')->update(['status' => 'OPEN']);
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(3, '2026-10')['saldo_awal']);
        $this->assertFalse(app(BsCashFlowService::class)->calculate(3, '2026-10')['opening_final']);
    }

    private function confirmUsageFinance($source): void
    {
        $source->refresh();
        $type = array_search($source::class, BsUsageCheckService::TYPES, true);
        $this->dashboardFinanceCheck($source, $type);
    }

    /** Seed report fixtures, including imported historical deficits, without the write workflow. */
    private function seedFinanceApprovals(string $period = '2026-09'): void
    {
        $posting = app(\App\Services\FinanceBsExpenseService::class);
        foreach (BsUsageCheckService::TYPES as $type => $model) {
            foreach ($posting->sources($type, $period)->get() as $source) {
                if (BsUsageCheckService::isEvent($source)) $source->forceFill(['status' => 'VALIDATED'])->save();
                if ($source->status !== 'VALIDATED' || $posting->owner($source) === null) continue;
                $snapshot = app(BsUsageCheckService::class)->snapshot($source);
                $check = BsUsageCheck::create([
                    'document_no' => 'CEKBS-FIXTURE-'.(BsUsageCheck::count() + 1),
                    'tanggal_pengecekan' => $period.'-16', 'periode' => $period,
                    'jenis_transaksi' => $type, 'transaksi_pemakaian_id' => $source->id,
                    'no_referensi' => $source->transaksi_id ?: $type.' #'.$source->id,
                    'user_id' => $source->user_id, 'user_name' => $source->user->nama,
                    'user_checker_id' => 2, 'checker_name' => 'Finance',
                    'source_fingerprint' => $snapshot['fingerprint'], 'source_revision' => $source->submission_version,
                    'keputusan' => 'APPROVED',
                ]);
                $check->details()->createMany($snapshot['items']->map(fn ($item) =>
                    $item + ['nominal_disetujui' => $item['nominal_asal']])->all());
            }
        }
    }

    private function assertFinanceCash(string $outgoing, string $balance, int $owner = 1, string $period = '2026-09'): void
    {
        $bs = app(BsCashFlowService::class)->recalculateSnapshot($owner, $period);
        $stock = app(StockCashFlowService::class)->recalculateSnapshot($owner, $period);
        $this->assertSame($outgoing, $bs->total_keluar);
        $this->assertSame($outgoing, $stock->total_keluar);
        $this->assertSame($balance, app(BonSementaraService::class)->getRealtimeSisaSaldo($owner, $period));
        $this->assertSame($balance, $stock->saldo_berjalan);
        $rows = app(StockCashFlowService::class)->details($owner, $period)['keluar'];
        $this->assertSame($outgoing, Money::decimal(collect($rows)->sum(fn ($row) => Money::minor($row['nominal']))));
    }

    public function test_finance_cash_user_confirmation_waits_and_all_four_modules_use_approved_amounts(): void
    {
        $bs = $this->bs();
        $sources = [
            'OPR' => $this->expense($bs, amount: '300000'),
            'PD' => $this->expense($bs, amount: '200000', travel: true),
            'EDK' => $this->recapEvent('EDK', '150000'),
            'ELK' => $this->recapEvent('ELK', '100000'),
        ];
        foreach ($sources as $type => $source) {
            if (in_array($type, ['EDK', 'ELK'], true)) $this->recapConfirm($source, $type);
            else {
                $this->actingAs(User::findOrFail(1));
                app(\App\Services\BsValidationService::class)->finalize($source);
                $source->refresh();
            }
            $this->assertFinanceCash('0.00', '1000000.00');
        }
        foreach (['OPR' => '250000.25', 'PD' => '180000.25', 'EDK' => '120000.25', 'ELK' => '80000.25'] as $type => $amount) {
            $this->recapDecision($sources[$type], $type, 'APPROVED', $amount);
        }
        $this->assertFinanceCash('630001.00', '369999.00');
        $stock = app(StockCashFlowService::class)->recalculateSnapshot(1, '2026-09');
        $this->assertSame('430000.50', $stock->total_keluar_bs);
        $this->assertSame('250000.25', $stock->total_keluar_operasional);
        $this->assertSame('180000.25', $stock->total_keluar_perjalanan_dinas);
        $this->assertSame('120000.25', $stock->total_event_dalam);
        $this->assertSame('80000.25', $stock->total_event_luar);
        $this->actingAs(User::findOrFail(1));
        foreach (['/cash-flow', '/cash-flow/202609'] as $url) {
            $this->assertCashModuleCards($this->get($url)->assertOk()->getContent(), [
                'Operasional Sales' => 'Rp 250.000', 'Perjalanan Dinas' => 'Rp 180.000',
                'Event Dalam Kota' => 'Rp 120.000', 'Event Luar Kota' => 'Rp 80.000',
            ]);
        }
        foreach (['/cash-flow', '/cash-flow/202609', route('dashboard.transaksi')] as $url) {
            $this->get($url)->assertOk()->assertSee('Saldo BS')->assertSee('369.999')->assertSee('630.001');
        }
        $workbook = $this->cashFlowWorkbook('/cash-flow/202609/export', 'stok-kas-202609.xlsx');
        $this->assertEquals(630001, $workbook->getSheet(0)->getCell('H2')->getValue());
        $this->assertEquals(630001, array_sum(array_column($workbook->getSheetByName('Uang Keluar')->rangeToArray('A2:D5', null, false, false), 3)));
    }

    public function test_finance_cash_drafts_and_rechecks_replace_only_the_latest_confirmed_amount(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '300000');
        $service = app(BsUsageCheckService::class);
        $data = ['save_action' => 'draft', 'tanggal_pengecekan' => '2026-10-01',
            'source_token' => $this->recapToken($source, 'OPR'),
            'review_token' => $service->reviewToken('OPR', $source->id, null, null),
            'details' => [['source_detail_id' => $source->details()->first()->id, 'cost_category_id' => 1, 'nominal_disetujui' => '250000']]];
        $draft = $service->store(User::findOrFail(2), 'OPR', $source->id, $data);
        $this->assertInstanceOf(BsUsageCheckDraft::class, $draft);
        $this->assertFinanceCash('0.00', '1000000.00');
        $data['save_action'] = 'confirm';
        $data['review_token'] = $service->reviewToken('OPR', $source->id, null, $draft);
        $first = $service->store(User::findOrFail(2), 'OPR', $source->id, $data);
        $this->assertFinanceCash('250000.00', '750000.00');
        $data['save_action'] = 'draft';
        $data['details'][0]['nominal_disetujui'] = '200000';
        $data['review_token'] = $service->reviewToken('OPR', $source->id, $first, null);
        $draft = $service->store(User::findOrFail(2), 'OPR', $source->id, $data);
        $this->assertFinanceCash('250000.00', '750000.00');
        $data['save_action'] = 'confirm';
        $data['review_token'] = $service->reviewToken('OPR', $source->id, $first, $draft);
        $second = $service->store(User::findOrFail(2), 'OPR', $source->id, $data);
        $this->assertEquals($first->id, $second->revision_of_id);
        $this->assertFinanceCash('200000.00', '800000.00');
        $this->recapDecision($source, 'OPR', 'APPROVED', '0');
        $this->assertFinanceCash('0.00', '1000000.00');
        $this->recapDecision($source, 'OPR', 'APPROVED', '300000');
        $this->recapDecision($source, 'OPR', 'REJECTED', '0');
        $this->assertFinanceCash('0.00', '1000000.00');
        $this->assertDatabaseCount('bs_usage_checks', 5);
        $this->assertDatabaseCount('bs_usage_check_drafts', 0);
        $this->assertSame('250000.00', $first->details()->first()->nominal_disetujui);
    }

    public function test_finance_cash_checks_available_funds_at_finance_confirmation_and_allows_replacement(): void
    {
        $bs = $this->bs();
        $one = $this->expense($bs, amount: '600000');
        $two = $this->expense($bs, amount: '600000', travel: true);
        foreach ([$one, $two] as $source) app(\App\Services\BsValidationService::class)->finalize($source);
        $this->recapDecision($one, 'OPR', 'APPROVED', '600000');
        $this->assertFinanceCash('600000.00', '400000.00');
        $this->actingAs(User::findOrFail(2));
        $this->postJson(route('bs-usage-checks.store', ['PD', $two->id]), [
            'tanggal_pengecekan' => '2026-09-16', 'source_token' => $this->recapToken($two, 'PD'),
            'details' => [['source_detail_id' => $two->details()->first()->id, 'cost_category_id' => 1, 'nominal_disetujui' => '600000']],
        ])->assertUnprocessable()->assertJsonValidationErrors('bs_balance');
        $this->assertDatabaseCount('bs_usage_checks', 1);
        $this->recapDecision($one, 'OPR', 'APPROVED', '700000');
        $this->recapDecision($two, 'PD', 'APPROVED', '300000');
        $this->assertFinanceCash('1000000.00', '0.00');
    }

    public function test_finance_cash_group_trip_posts_to_bs_owner_only_after_finance(): void
    {
        $source = $this->pendingMemberUsage('300000');
        $this->actingAs(User::findOrFail(1));
        app(\App\Services\BsValidationService::class)->approve($source, $source->approval_version);
        $this->assertFinanceCash('0.00', '1000000.00');
        $this->recapDecision($source, 'PD', 'APPROVED', '250000');
        $this->assertFinanceCash('250000.00', '750000.00');
        $this->assertFinanceCash('0.00', '0.00', 3);
        $this->assertSame('250000.00', app(StockCashFlowService::class)->recalculateSnapshot(1, '2026-09')->total_keluar_perjalanan_dinas);
        $this->assertSame('0.00', app(StockCashFlowService::class)->recalculateSnapshot(3, '2026-09')->total_keluar_perjalanan_dinas);
    }

    private function assertCashModuleCards(string $html, array $expected): void
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($dom);
        $cards = $xpath->query('(//div[contains(concat(" ", normalize-space(@class), " "), " bd-grid ")])[1]/div');
        $values = [];
        foreach ($cards as $card) {
            $spans = $xpath->query('./span', $card);
            $values[trim($spans[0]->textContent)] = trim($spans[1]->textContent);
        }
        foreach ($expected as $label => $value) $this->assertSame($value, $values[$label] ?? null, $label);
    }

    public function test_finance_cash_pd_cards_show_170000_from_two_items_and_follow_latest_confirmation(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 12));
        Periode::create(['periode' => '2026-10', 'status' => 'OPEN']);
        $bs = $this->bs(['tanggal' => '2026-10-12', 'periode' => '2026-10', 'nominal_approved' => '250000']);
        $source = $this->expense($bs, status: 'VALIDATED', amount: '100000', travel: true);
        $source->details()->create(['cost_category_id' => 1, 'remark' => 'Cek oli', 'nominal' => '100000']);
        $items = $source->details()->orderBy('id')->get();
        $service = app(BsUsageCheckService::class);
        foreach (['confirm' => '70000', 'draft' => '50000', 'confirm_again' => '50000'] as $action => $secondAmount) {
            $service->store(User::findOrFail(2), 'PD', $source->id, [
                'save_action' => $action === 'draft' ? 'draft' : 'confirm',
                'tanggal_pengecekan' => '2026-10-12', 'source_token' => $this->recapToken($source, 'PD'),
                'review_token' => $service->reviewToken('PD', $source->id, $source->financeChecks()->latest('id')->first(),
                    BsUsageCheckDraft::where('jenis_transaksi', 'PD')->where('transaksi_pemakaian_id', $source->id)->first()),
                'details' => $items->map(fn ($item, $index) => [
                    'source_detail_id' => $item->id, 'cost_category_id' => 1,
                    'nominal_disetujui' => $index === 0 ? '100000' : $secondAmount,
                ])->all(),
            ]);
            $expected = $action === 'confirm_again' ? '150000.00' : '170000.00';
            $stock = app(StockCashFlowService::class)->recalculateSnapshot(1, '2026-10');
            $this->assertSame($expected, $stock->total_keluar);
            $this->assertSame($expected, $stock->total_keluar_perjalanan_dinas);
            $this->assertSame('0.00', $stock->total_keluar_operasional);
            foreach (['/cash-flow', '/cash-flow/202610'] as $url) {
                $this->assertCashModuleCards($this->get($url)->assertOk()->getContent(), [
                    'Pencairan BS' => 'Rp 250.000', 'Operasional Sales' => 'Rp 0',
                    'Perjalanan Dinas' => 'Rp '.number_format($expected, 0, ',', '.'),
                    'Event Dalam Kota' => 'Rp 0', 'Event Luar Kota' => 'Rp 0',
                ]);
            }
        }
    }

    public function test_finance_cash_legacy_breakdown_does_not_rewrite_closed_snapshots(): void
    {
        $bs = $this->bs();
        $this->expense($bs, status: 'VALIDATED', amount: '30000');
        $this->expense($bs, status: 'VALIDATED', amount: '170000', travel: true);
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $frozen = \App\Models\StockCashFlowSnapshot::create([
            'user_id' => 1, 'periode' => '2026-09', 'saldo_awal' => '100000', 'total_masuk' => '1000000',
            'total_keluar_bs' => '200000', 'total_event_dalam' => '10000', 'total_keluar' => '210000',
            'saldo_akhir' => '890000', 'opening_final' => true,
        ]);
        $before = $frozen->fresh()->getAttributes();
        foreach (['/cash-flow', '/cash-flow/202609'] as $url) {
            $this->assertCashModuleCards($this->get($url)->assertOk()->getContent(), [
                'Operasional Sales' => 'Rp 30.000', 'Perjalanan Dinas' => 'Rp 170.000',
            ]);
        }
        $this->assertSame($before, $frozen->fresh()->getAttributes());
        (require database_path('migrations/2026_10_09_032839_fix_operasional_perjalanan_dinas_nullable.php'))->up();
        $this->assertSame($before, $frozen->fresh()->getAttributes());
        $frozen->update(['total_keluar_bs' => '250000']);
        $this->assertCashModuleCards($this->get('/cash-flow')->assertOk()->getContent(), [
            'Operasional Sales' => 'Belum tersedia', 'Perjalanan Dinas' => 'Belum tersedia',
        ]);
    }

    public function test_finance_cash_close_waits_for_review_then_rolls_over_once_and_rejects_closed_recheck(): void
    {
        $this->bs();
        $event = $this->recapEvent('EDK', '300000');
        $this->recapConfirm($event, 'EDK');
        $this->travelTo(now()->setDate(2026, 10, 2));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        try {
            app(BsPeriodClosingService::class)->close($period->id);
            $this->fail('Unreviewed event must block closing.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertStringContainsString('Finance', $exception->getMessage());
        }
        $this->recapDecision($event, 'EDK', 'APPROVED', '250000');
        app(BsPeriodClosingService::class)->close($period->id);
        $this->assertFinanceCash('250000.00', '750000.00');
        $this->assertFinanceCash('0.00', '750000.00', period: '2026-10');
        $this->actingAs(User::findOrFail(2));
        $this->postJson(route('bs-usage-checks.store', ['EDK', $event->id]), [
            'tanggal_pengecekan' => '2026-10-02', 'source_token' => $this->recapToken($event, 'EDK'),
            'details' => [['source_detail_id' => null, 'cost_category_id' => null, 'nominal_disetujui' => '200000']],
        ])->assertUnprocessable()->assertJsonValidationErrors('tanggal');
        $this->assertDatabaseCount('bs_usage_checks', 1);
        app(BsPeriodClosingService::class)->reopen($period->id, 'Koreksi Finance');
        $this->recapDecision($event, 'EDK', 'APPROVED', '200000');
        app(BsPeriodClosingService::class)->close($period->id);
        $this->assertFinanceCash('0.00', '800000.00', period: '2026-10');
    }

    public function test_finance_cash_rolls_back_check_and_keeps_draft_when_snapshot_write_fails(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '300000');
        $service = app(BsUsageCheckService::class);
        $data = ['save_action' => 'draft', 'tanggal_pengecekan' => '2026-09-16',
            'source_token' => $this->recapToken($source, 'OPR'),
            'review_token' => $service->reviewToken('OPR', $source->id, null, null),
            'details' => [['source_detail_id' => $source->details()->first()->id, 'cost_category_id' => 1, 'nominal_disetujui' => '250000']]];
        $draft = $service->store(User::findOrFail(2), 'OPR', $source->id, $data);
        $data['save_action'] = 'confirm';
        $data['review_token'] = $service->reviewToken('OPR', $source->id, null, $draft);
        $this->mock(StockCashFlowService::class)->shouldReceive('recalculateSnapshot')->once()->andThrow(new \RuntimeException('Snapshot failed'));
        try {
            $service->store(User::findOrFail(2), 'OPR', $source->id, $data);
            $this->fail('Confirmation should roll back.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Snapshot failed', $exception->getMessage());
        }
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $this->assertDatabaseCount('bs_usage_check_drafts', 1);
        $this->app->forgetInstance(StockCashFlowService::class);
        $this->assertFinanceCash('0.00', '1000000.00');
    }

    public function test_finance_cash_stale_versions_and_changed_categories_wait_for_recheck(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '300000');
        $this->recapDecision($source, 'OPR', 'APPROVED', '250000');
        $this->assertFinanceCash('250000.00', '750000.00');
        $source->forceFill(['submission_version' => (int) $source->submission_version + 1])->save();
        $this->assertFinanceCash('0.00', '1000000.00');
        $this->recapDecision($source, 'OPR', 'APPROVED', '200000');
        $this->assertFinanceCash('200000.00', '800000.00');
        \Illuminate\Support\Facades\DB::table('cost_category')->insert(['id' => 2, 'nama' => 'Makan']);
        $source->details()->first()->update(['cost_category_id' => 2]);
        $this->assertFinanceCash('0.00', '1000000.00');
        $this->travelTo(now()->setDate(2026, 10, 2));
        try {
            app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
            $this->fail('A stale Finance check cannot finalize the period.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertStringContainsString('Finance', $exception->getMessage());
        }
        $this->recapDecision($source, 'OPR', 'APPROVED', '150000');
        $this->assertFinanceCash('150000.00', '850000.00');
    }
}
