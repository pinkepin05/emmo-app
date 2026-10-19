<?php

namespace Tests\Concerns;

use App\Models\Periode;
use App\Models\User;
use App\Services\PeriodClosingReviewService;
use Illuminate\Support\Facades\DB;

trait PeriodClosingReviewScenarios
{
    public function test_period_review_finds_other_users_and_transactions_dated_outside_the_accounting_month(): void
    {
        $bs = $this->bs();
        $oldDate = $this->expense($bs, amount: '100000', attributes: ['tanggal' => '2026-08-18']);
        $travel = $this->expense($bs, status: 'PENDING_OWNER', amount: '700000', travel: true, attributes: ['user_id' => 3]);
        $pendingFinance = $this->expense($bs, status: 'VALIDATED', amount: '50000');
        $otherPeriod = $this->expense($bs, amount: '123000', attributes: ['periode' => '2026-10']);
        $before = DB::table($oldDate->getTable())->orderBy('id')->get()->toJson();
        $this->travelTo(now()->setDate(2026, 10, 12));
        $this->actingAs(User::findOrFail(2));
        $response = $this->get(route('periode.show', '2026-09'))->assertOk()
            ->assertSee('3 transaksi masih menghalangi tutup buku.')
            ->assertSee('Dicatat di periode 2026-09')->assertSee($oldDate->transaksi_id)
            ->assertSee($travel->transaksi_id)->assertSee($pendingFinance->transaksi_id)
            ->assertDontSee($otherPeriod->transaksi_id)
            ->assertSee(route('transaction-recap.show', ['jenis' => 'PD', 'source' => $travel->id, 'pemakaian_periode' => '2026-09', 'dari' => 'periode']))
            ->assertSee('Tutup dengan Pengecualian')->assertSee('name="exclude_pending" value="1"', false);
        $blockers = $response->viewData('closingBlockers');
        $this->assertCount(3, $blockers);
        $this->assertEquals(['DRAFT' => 1, 'PENDING_OWNER' => 1, 'FINANCE_PENDING' => 1], $blockers->countBy('stage')->all());
        $this->assertSame('Anggota', $blockers->firstWhere('type', 'PD')['user_name']);
        $this->assertSame('100000.00', $blockers->firstWhere('id', $oldDate->id)['amount']);
        $this->assertSame($before, DB::table($oldDate->getTable())->orderBy('id')->get()->toJson());
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
        $this->get(route('periode.index'))->assertOk()
            ->assertSee(route('periode.show', '2026-09').'#period-closing-review', false);
        $this->get(route('transaction-recap.show', ['jenis' => 'PD', 'source' => $travel->id, 'dari' => 'periode']))->assertOk()
            ->assertSee(route('periode.show', '2026-09').'#period-closing-review', false);
        $this->actingAs(User::findOrFail(1));
        $this->get(route('periode.show', '2026-09'))->assertForbidden();
    }

    public function test_period_review_matches_closing_guards_without_counting_resolved_or_unfunded_archives(): void
    {
        $bs = $this->bs();
        $unfundedDraft = $this->expense($bs, amount: '100');
        $unfundedDraft->forceFill(['bs_id' => null, 'bs_owner_id' => null])->save();
        $unfundedArchive = $this->expense($bs, status: 'VALIDATED', amount: '200');
        $unfundedArchive->forceFill(['bs_id' => null, 'bs_owner_id' => null])->save();
        $ownerRejected = $this->expense($bs, status: 'REJECTED_OWNER', amount: '300', travel: true);
        $approved = $this->expense($bs, status: 'VALIDATED', amount: '1000');
        $this->recapDecision($approved, 'OPR', 'APPROVED', '800');
        $rejected = $this->expense($bs, status: 'VALIDATED', amount: '1000');
        $this->recapDecision($rejected, 'OPR', 'REJECTED', '0', 'Tidak sesuai');
        $stale = $this->expense($bs, status: 'VALIDATED', amount: '1000');
        $this->recapDecision($stale, 'OPR', 'APPROVED', '800');
        $stale->details()->update(['nominal' => '1200']);
        $event = $this->recapEvent('EDK', '500');

        $blockers = app(PeriodClosingReviewService::class)->blockers('202609');
        $this->assertSame(['OPR:'.$unfundedDraft->id, 'OPR:'.$stale->id, 'EDK:'.$event->id],
            $blockers->map(fn ($item) => $item['type'].':'.$item['id'])->all());
        $this->assertSame('Hasil Finance perlu diselesaikan atau diperiksa ulang', $blockers->firstWhere('id', $stale->id)['reason']);
        $this->assertSame('DRAFT', $blockers->firstWhere('type', 'EDK')['stage']);
    }

    public function test_period_review_offers_closing_only_after_blockers_are_resolved(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '100000');
        $this->travelTo(now()->setDate(2026, 10, 12));
        $this->actingAs(User::findOrFail(2));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $this->get(route('periode.show', $period->periode))->assertOk()
            ->assertViewHas('closingBlockers', fn ($rows) => $rows->count() === 1);
        $this->recapDecision($source, 'OPR', 'APPROVED', '80000');
        $this->get(route('periode.show', $period->periode))->assertOk()
            ->assertViewHas('closingBlockers', fn ($rows) => $rows->isEmpty())
            ->assertSee('action="'.route('periode.close', $period).'"', false);
        $this->post(route('periode.close', $period))->assertSessionHasNoErrors();
        $this->assertSame('CLOSED', $period->fresh()->status);
        $this->get(route('periode.show', $period->periode))->assertOk()->assertDontSee('id="period-closing-review"', false);
    }
}
