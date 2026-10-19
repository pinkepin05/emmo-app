<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Services\StockCashFlowService;
use Maatwebsite\Excel\Facades\Excel;

trait SharedBsUsageScenarios
{
    public function test_personal_lists_and_exports_only_show_inputters_own_transactions(): void
    {
        $bs = $this->bs();
        foreach (['transaksi_operasional', 'transaksi_perjalanan_dinas'] as $route) {
            $travel = $route === 'transaksi_perjalanan_dinas';
            $own = $this->expense($bs, 'VALIDATED', '200000', $travel);
            $legacy = $this->expense($bs, 'VALIDATED', '100000', $travel, ['user_id' => 3]);
            $pooled = $this->expense($bs, 'VALIDATED', '50000', $travel, ['user_id' => 3, 'bs_owner_id' => 1, 'bs_id' => null]);
            $draft = $this->expense($bs, 'DRAFT', '20000', $travel, ['user_id' => 3, 'bs_owner_id' => 1]);
            $this->actingAs(User::findOrFail(1));
            foreach ([[], ['periode' => '2026-09', 'dilaporkan' => 1, 'user_id' => 3]] as $filters) {
                $this->get(route($route.'.index', $filters))->assertOk()->assertSee($own->transaksi_id)
                    ->assertDontSee($legacy->transaksi_id)->assertDontSee($pooled->transaksi_id)->assertDontSee($draft->transaksi_id)
                    ->assertViewHas('transaksis', fn ($rows) => $rows->total() === 1 && $rows->first()->user_id == 1);
            }
            Excel::fake();
            $this->get(route($route.'.export', ['periode' => '2026-09', 'user_id' => 3]))->assertOk();
            $name = $travel ? 'perjalanan-dinas' : 'operasional-sales';
            Excel::assertDownloaded('laporan-'.$name.'-2026-09.xlsx', function ($export) use ($own) {
                $this->assertSame([$own->transaksi_id, 'TOTAL'], array_column($export->array(), 0));
                return true;
            });
            $this->get(route($route.'.show', $travel ? $legacy->transaksi_id : $legacy->id))->assertOk();
            $this->actingAs(User::findOrFail(3));
            $this->get(route($route.'.index'))->assertOk()->assertDontSee($own->transaksi_id)
                ->assertSee($legacy->transaksi_id)->assertSee($pooled->transaksi_id)->assertSee($draft->transaksi_id)
                ->assertViewHas('transaksis', fn ($rows) => $rows->total() === 3 && $rows->every(fn ($row) => $row->user_id == 3));
            $this->actingAs($this->transactionActionUser());
            $this->get(route($route.'.index'))->assertOk()->assertViewHas('transaksis', fn ($rows) => $rows->total() === 4);
        }
    }

    public function test_shared_bs_cash_flow_is_visible_to_both_users_and_only_debits_its_owner(): void
    {
        $bs = $this->bs();
        $this->bs(['user_id' => 3, 'nominal_approved' => '500000']);
        $source = $this->expense($bs, 'VALIDATED', '200000', true, ['user_id' => 3]);
        $draft = $this->expense($bs, 'DRAFT', '20000', true, ['user_id' => 3]);
        $stock = app(StockCashFlowService::class);
        foreach ([1, 3] as $viewer) {
            $this->actingAs(User::findOrFail($viewer));
            $this->get('/cash-flow/202609')->assertOk()->assertSee('Pemakaian BS Bersama')
                ->assertSee('Pemakai: <strong>Anggota</strong>', false)->assertSee('Pemilik BS: <strong>Pemilik</strong>', false)
                ->assertSee($source->transaksi_id)->assertDontSee($draft->transaksi_id)
                ->assertViewHas('pagedSharedUsage', fn ($rows) => $rows->total() === 1
                    && $rows->first()['reported'] === '200000.00' && $rows->first()['approved'] === null
                    && $rows->first()['finance_status'] === 'Belum Dicek Finance')
                ->assertViewHas('totalKeluar', 0);
        }

        $this->recapDecision($source, 'PD', 'APPROVED', '170000');
        foreach ([1 => '830000.00', 3 => '500000.00'] as $viewer => $balance) {
            $this->actingAs(User::findOrFail($viewer));
            $this->get('/cash-flow/202609')->assertOk()
                ->assertViewHas('pagedSharedUsage', fn ($rows) => $rows->first()['approved'] === '170000.00')
                ->assertViewHas('snapshot', fn ($snapshot) => $snapshot->saldo_berjalan === $balance)
                ->assertViewHas('totalKeluar', $viewer === 1 ? 170000 : 0);
            $book = $this->cashFlowWorkbook('/cash-flow/202609/export', 'stok-kas-202609.xlsx');
            $row = $book->getSheetByName('Pemakaian BS Bersama')->rangeToArray('C2:H2', null, false, false)[0];
            $this->assertEquals([$source->transaksi_id, 'Anggota', 'Pemilik', 200000, 170000, 'Dikonfirmasi Finance'], $row);
            $this->assertEquals($viewer === 1 ? 170000 : 0, $book->getSheetByName('Ringkasan Stok Kas')->getCell('H2')->getValue());
            $book->disconnectWorksheets();
        }
        $this->actingAs(User::findOrFail(2));
        $this->get('/cash-flow/202609?user_id=1')->assertOk()->assertDontSee($source->transaksi_id)
            ->assertViewHas('pagedSharedUsage', fn ($rows) => $rows->total() === 0);
        $this->assertCount(0, $stock->sharedUsage(1, '2026-08'));
        $this->assertCount(0, $stock->sharedUsage(3, '2026-08'));
        $source->details()->first()->update(['nominal' => '210000']);
        $this->assertNull($stock->sharedUsage(3, '2026-09')->first()['approved']);
    }

    public function test_shared_bs_usage_uses_transaction_month_and_preserves_pagination_scope(): void
    {
        $bs = $this->bs();
        $this->travelTo(now()->setDate(2026, 10, 16));
        for ($i = 0; $i < 11; $i++) {
            $this->expense($bs, 'VALIDATED', '1000', true, [
                'user_id' => 3, 'bs_owner_id' => 1, 'bs_id' => null,
                'periode' => '2026-09', 'tanggal' => '2026-10-05',
            ]);
        }
        foreach ([1, 3] as $viewer) {
            $this->actingAs(User::findOrFail($viewer));
            $this->get('/cash-flow?semua_periode=1')->assertOk()->assertSee('202610');
            $this->get('/cash-flow/202609')->assertOk()->assertViewHas('pagedSharedUsage', fn ($rows) => $rows->total() === 0);
            $page = $this->get('/cash-flow/202610?semua_periode=1&shared_page=2')->assertOk()
                ->assertViewHas('pagedSharedUsage', fn ($rows) => $rows->total() === 11 && $rows->count() === 1
                    && $rows->first()['user_id'] === 3 && $rows->first()['owner_id'] === 1);
            $this->assertStringContainsString('semua_periode=1', $page->viewData('pagedSharedUsage')->url(1));
            $this->assertStringContainsString('shared_page=1', $page->viewData('pagedSharedUsage')->url(1));
        }
    }
}
