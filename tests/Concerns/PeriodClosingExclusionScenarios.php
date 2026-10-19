<?php

namespace Tests\Concerns;

use App\Models\BsUsageCheckDraft;
use App\Models\Periode;
use App\Models\PeriodClosingLog;
use App\Models\User;
use App\Services\BsCashFlowService;
use App\Services\BsPeriodClosingService;
use App\Services\CashFlowService;
use App\Services\StockCashFlowService;
use Illuminate\Support\Facades\DB;

trait PeriodClosingExclusionScenarios
{
    private function closingExclusionPayload(string $period = '2026-09'): array
    {
        $this->actingAs(User::findOrFail(2));
        $page = $this->get(route('periode.show', $period))->assertOk();

        return ['exclude_pending' => '1', 'confirm_exclusions' => '1',
            'reason' => 'Arsip transaksi tertunda disepakati tidak dibebankan.',
            'review_token' => $page->viewData('closingReviewToken')];
    }

    public function test_period_exclusion_closes_with_all_pending_modules_and_preserves_source_data(): void
    {
        $bs = $this->bs();
        $draft = $this->expense($bs, amount: '100000', attributes: ['tanggal' => '2026-08-18']);
        $ownerPending = $this->expense($bs, status: 'PENDING_OWNER', amount: '200000', travel: true, attributes: ['user_id' => 3]);
        $financePending = $this->expense($bs, status: 'VALIDATED', amount: '300000');
        $stale = $this->expense($bs, status: 'VALIDATED', amount: '400000', travel: true);
        $this->recapDecision($stale, 'PD', 'APPROVED', '350000');
        $stale->details()->update(['nominal' => '450000']);
        $localDraft = $this->recapEvent('EDK', '50000');
        $outsidePending = $this->recapEvent('ELK', '60000');
        $this->recapConfirm($outsidePending, 'ELK');
        $approved = $this->expense($bs, status: 'VALIDATED', amount: '100000');
        $this->recapDecision($approved, 'OPR', 'APPROVED', '80000');
        // An unfinished recheck must retain the last confirmed approval.
        $form = $this->get(route('transaction-recap.show', ['OPR', $approved->id, 'pemeriksaan' => 1]))->assertOk();
        $this->post(route('bs-usage-checks.store', ['OPR', $approved->id]), $this->workspacePayload($form))->assertSessionHasNoErrors();
        $tables = [$draft->getTable(), $draft->details()->getRelated()->getTable(),
            $ownerPending->getTable(), $ownerPending->details()->getRelated()->getTable(),
            $localDraft->getTable(), $outsidePending->getTable(), 'bs_usage_checks', 'bs_usage_check_drafts'];
        $before = collect($tables)->mapWithKeys(fn ($table) => [$table => DB::table($table)->orderBy('id')->get()->toJson()]);
        $this->travelTo(now()->setDate(2026, 10, 12));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $payload = $this->closingExclusionPayload();
        $this->post(route('periode.close', $period), $payload)->assertSessionHasNoErrors()->assertRedirect(route('periode.show', '2026-09'));
        $this->assertSame('CLOSED', $period->fresh()->status);
        $this->assertEquals(2, $period->fresh()->closed_by);
        foreach ($before as $table => $data) $this->assertSame($data, DB::table($table)->orderBy('id')->get()->toJson(), $table);
        $log = PeriodClosingLog::sole();
        $this->assertCount(6, $log->excluded_transactions);
        $this->assertSame('EXCLUDE_PENDING', $log->mode);
        $this->assertSame('Atasan / Finance', $log->actor_name);
        $this->assertSame($payload['reason'], $log->reason);
        $this->assertSame(['OPR:'.$draft->id, 'OPR:'.$financePending->id, 'PD:'.$ownerPending->id, 'PD:'.$stale->id, 'EDK:'.$localDraft->id, 'ELK:'.$outsidePending->id],
            collect($log->excluded_transactions)->map(fn ($item) => $item['type'].':'.$item['id'])->all());
        foreach (['bs_cash_flow_snapshots', 'stock_cash_flow_snapshots'] as $table) {
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-09', 'total_keluar' => 80000, 'saldo_akhir' => 920000]);
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-10', 'saldo_awal' => 920000, 'saldo_akhir' => null]);
        }
        // The legacy summary keeps its original nominal rule, but also excludes the six pending sources.
        $this->assertDatabaseHas('cash_flow_snapshots', ['user_id' => 1, 'periode' => '2026-09', 'total_keluar' => 100000, 'saldo_akhir' => 900000]);
        $this->get(route('periode.show', '2026-09'))->assertOk()->assertSee('Riwayat Penutupan')->assertSee('6 transaksi dikecualikan')->assertSee($payload['reason']);
        $this->get(route('transaction-recap.show', ['PD', $stale->id]))->assertOk()->assertSee('Dikecualikan saat tutup buku.')->assertDontSee('>Cek Ulang<', false);
        $this->post(route('periode.close', $period), $payload)->assertSessionHasErrors('error');
        $this->assertDatabaseCount('period_closing_logs', 1);
        $this->assertSame('920000.00', app(StockCashFlowService::class)->recalculateSnapshot(1, '2026-09')->saldo_akhir);
    }

    public function test_period_exclusion_requires_authorization_reason_acknowledgement_and_current_review(): void
    {
        $pending = $this->expense($this->bs(), status: 'VALIDATED', amount: '100000');
        $this->travelTo(now()->setDate(2026, 10, 12));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $payload = $this->closingExclusionPayload();
        $url = route('periode.close', $period);
        $this->post($url)->assertSessionHasErrors('error');
        foreach (['reason' => '', 'confirm_exclusions' => '0', 'review_token' => ''] as $field => $invalid) {
            $this->post($url, array_replace($payload, [$field => $invalid]))->assertSessionHasErrors($field);
        }
        $this->post($url, array_replace($payload, ['review_token' => str_repeat('0', 64)]))->assertSessionHasErrors('error');
        $this->actingAs(User::findOrFail(1));
        $this->post($url, $payload)->assertForbidden();
        $this->actingAs(User::findOrFail(2));
        $pending->details()->update(['nominal' => '110000']);
        $this->post($url, $payload)->assertSessionHasErrors('error');
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertDatabaseCount('period_closing_logs', 0);
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
        // A Finance decision made after preview must also invalidate the reviewed exclusions.
        $payload = $this->closingExclusionPayload();
        $this->recapDecision($pending->fresh(), 'OPR', 'APPROVED', '90000');
        $this->post($url, $payload)->assertSessionHasErrors('error');
        $this->post($url)->assertSessionHasNoErrors();
        $this->assertSame('STANDARD', PeriodClosingLog::sole()->mode);
        $this->assertSame([], PeriodClosingLog::sole()->excluded_transactions);
    }

    public function test_period_exclusion_locks_edit_delete_owner_approval_and_both_finance_save_actions(): void
    {
        $bs = $this->bs();
        $draft = $this->expense($bs, amount: '100000');
        $travel = $this->expense($bs, amount: '120000', travel: true);
        $ownerPending = $this->expense($bs, status: 'PENDING_OWNER', travel: true, attributes: ['user_id' => 3]);
        $pending = $this->expense($bs, status: 'VALIDATED', amount: '200000');
        $event = $this->recapEvent('EDK', '50000');
        $this->actingAs(User::findOrFail(2));
        $financeUrl = route('bs-usage-checks.store', ['OPR', $pending->id]);
        $form = $this->get(route('transaction-recap.show', ['OPR', $pending->id, 'pemeriksaan' => 1]))->assertOk();
        $financePayload = $this->workspacePayload($form);
        $this->post($financeUrl, $financePayload)->assertSessionHasNoErrors();
        $form = $this->get(route('transaction-recap.show', ['OPR', $pending->id, 'pemeriksaan' => 1]))->assertOk();
        $financePayload = $this->workspacePayload($form);
        $savedDraft = BsUsageCheckDraft::sole()->getAttributes();
        $this->travelTo(now()->setDate(2026, 10, 12));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $this->post(route('periode.close', $period), $this->closingExclusionPayload())->assertSessionHasNoErrors();
        foreach (['draft', 'confirm'] as $action) {
            $this->post($financeUrl, array_replace($financePayload, ['save_action' => $action]))->assertSessionHasErrors('tanggal');
        }
        $this->get(route('bs-usage-checks.create', ['OPR', $pending->id]))->assertForbidden();
        $this->assertSame($savedDraft, BsUsageCheckDraft::sole()->getAttributes());
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $this->actingAs(User::findOrFail(1));
        $this->put(route('transaksi_operasional.update', $draft->id), ['tanggal' => '2026-10-12'])->assertSessionHasErrors('tanggal');
        $this->delete(route('transaksi_operasional.destroy', $draft->id))->assertSessionHasErrors('tanggal');
        $this->post(route('transaksi_operasional.validate', $draft->id))->assertSessionHasErrors('tanggal');
        $this->delete(route('transaksi_perjalanan_dinas.destroy', $travel->transaksi_id))->assertSessionHasErrors('tanggal');
        $this->post(route('bs-usage-approvals.approve', $ownerPending->id), ['approval_version' => 0])->assertSessionHasErrors('tanggal');
        $this->delete(route('perongkosan_event_dalam_kota.destroy', $event->id))->assertSessionHasErrors('tanggal');
        $this->assertSame('DRAFT', $draft->fresh()->status);
        $this->assertSame('DRAFT', $travel->fresh()->status);
        $this->assertSame('PENDING_OWNER', $ownerPending->fresh()->status);
        $this->assertNotNull($event->fresh());
    }

    public function test_period_exclusion_retains_calendar_order_and_opening_balance_guards(): void
    {
        $this->expense($this->bs());
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $payload = $this->closingExclusionPayload();
        $this->post(route('periode.close', $period), $payload)->assertSessionHasErrors('error');
        $this->travelTo(now()->setDate(2026, 11, 12));
        $prior = Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $this->post(route('periode.close', $period), $payload)->assertSessionHasErrors('error');
        $prior->update(['status' => 'CLOSED']);
        $next = Periode::create(['periode' => '2026-10', 'status' => 'CLOSED']);
        $this->post(route('periode.close', $period), $payload)->assertSessionHasErrors('error');
        $next->update(['status' => 'OPEN']);
        \App\Models\BsCashFlowSnapshot::create(['user_id' => 1, 'periode' => '2026-08', 'opening_final' => false, 'saldo_akhir' => 0]);
        $this->post(route('periode.close', $period), $payload)->assertSessionHasErrors('error');
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertDatabaseCount('period_closing_logs', 0);
    }

    public function test_period_exclusion_reopen_and_reclose_preserve_history_and_restore_normal_processing(): void
    {
        $pending = $this->expense($this->bs(), status: 'VALIDATED', amount: '100000');
        $this->travelTo(now()->setDate(2026, 10, 12));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $this->post(route('periode.close', $period), $this->closingExclusionPayload())->assertSessionHasNoErrors();
        $originalAudit = PeriodClosingLog::sole()->getAttributes();
        $this->post(route('periode.reopen', $period), ['reason' => 'Koreksi sebelum ada aktivitas bulan berikutnya'])->assertSessionHasNoErrors();
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertCount(0, PeriodClosingLog::activeExclusions('2026-09'));
        $this->get(route('periode.show', '2026-09'))->assertOk()->assertSee('Riwayat Penutupan');
        $this->recapDecision($pending, 'OPR', 'APPROVED', '80000');
        $this->post(route('periode.close', $period))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('period_closing_logs', 2);
        $this->assertSame($originalAudit, PeriodClosingLog::oldest('id')->firstOrFail()->getAttributes());
        $this->assertCount(0, PeriodClosingLog::activeExclusions('2026-09'));
        $this->assertDatabaseHas('stock_cash_flow_snapshots', ['user_id' => 1, 'periode' => '2026-10', 'saldo_awal' => 920000]);
        $this->assertDatabaseHas('cash_flow_snapshots', ['user_id' => 1, 'periode' => '2026-09', 'total_keluar' => 100000]);
    }

    public function test_period_exclusion_failure_rolls_back_status_snapshots_and_audit(): void
    {
        $this->expense($this->bs(), status: 'VALIDATED');
        $snapshot = app(BsCashFlowService::class)->recalculateSnapshot(1, '2026-09');
        $before = $snapshot->fresh()->getAttributes();
        $this->travelTo(now()->setDate(2026, 10, 12));
        $this->actingAs(User::findOrFail(2));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $payload = $this->closingExclusionPayload();
        $this->mock(CashFlowService::class, fn ($mock) => $mock->shouldReceive('ensureSnapshotForPeriod')->once()->andThrow(new \RuntimeException('Simulated exclusion failure')));
        try {
            app(BsPeriodClosingService::class)->close($period->id, true, $payload['reason'], $payload['review_token']);
            $this->fail('Expected closing failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated exclusion failure', $exception->getMessage());
        }
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertNull($period->fresh()->closed_at);
        $this->assertSame($before, $snapshot->fresh()->getAttributes());
        $this->assertDatabaseCount('period_closing_logs', 0);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
        $this->assertDatabaseMissing('periode', ['periode' => '2026-10']);
        $this->assertSame(0, DB::transactionLevel());
    }
}
