<?php

namespace Tests\Concerns;

use App\Models\BsActivityLog;
use App\Models\Periode;
use App\Models\User;
use App\Notifications\BsRejected;
use App\Services\BsPeriodClosingService;
use Illuminate\Support\Facades\Notification;

trait BsApprovalPeriodScenarios
{
    public function test_atasan_can_approve_an_old_open_period_even_when_current_period_is_closed(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $bs = $this->bs(['status' => 'PENDING_ATASAN', 'periode' => '202608', 'tanggal' => '2026-08-15'], false);
        $this->actingAs(User::findOrFail(2));

        $this->get(route('approval-atasan.detail', $bs))->assertOk()
            ->assertSee('Setujui & Teruskan ke Finance', false);
        $this->post(route('approval-atasan.approve', $bs))->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('APPROVED_ATASAN', $bs->fresh()->status);
        $this->assertDatabaseMissing('bs_activity_logs', ['activity_code' => 'REJECTED_SYSTEM']);
        $this->assertDatabaseCount('approval_bs_finance', 0);
    }

    public function test_atasan_closed_period_direct_actions_reject_by_system_only_once(): void
    {
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $this->actingAs(User::findOrFail(2));
        foreach (['approve', 'reject'] as $action) {
            $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
            // A stale page cannot approve or replace the automatic reason with a manual one.
            $this->post(route('approval-atasan.'.$action, $bs), ['rejection_reason' => 'Alasan manual'])
                ->assertSessionHas('error');
            $bs->refresh();
            $this->assertSame('REJECTED_ATASAN', $bs->status);
            $this->assertSame('Ditolak otomatis oleh sistem karena periode pengajuan 2026-09 sudah ditutup.', $bs->rejection_reason);
            $this->assertDatabaseHas('bs_activity_logs', [
                'pengajuan_bs_id' => $bs->id, 'activity_code' => 'REJECTED_SYSTEM',
                'activity_name' => 'Ditolak Sistem', 'actor_user_id' => null,
            ]);
            $this->post(route('approval-atasan.approve', $bs))->assertSessionHas('error');
            $this->assertSame(1, $bs->logs()->count());
        }
        Notification::assertCount(2);
        Notification::assertSentTo(User::findOrFail(1), BsRejected::class, function ($notification) {
            $this->assertStringContainsString('ditolak oleh sistem', $notification->toArray(User::findOrFail(1))['message']);
            return $notification->bySystem;
        });
        $this->assertDatabaseCount('approval_bs_finance', 0);
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
    }

    public function test_atasan_index_cleans_closed_pending_requests_only_in_own_scope(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'CLOSED']);
        $old = ['periode' => '2026-08', 'tanggal' => '2026-08-15'];
        $expired = $this->bs($old + ['status' => 'PENDING_ATASAN'], false);
        $other = $this->bs($old + ['status' => 'PENDING_ATASAN', 'atasan_user_id' => 3], false);
        $approved = $this->bs($old + ['status' => 'APPROVED_ATASAN'], false);
        $open = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));

        $this->get(route('approval-atasan.index', ['periode' => '2026-08']))->assertOk()
            ->assertSee('Ditolak Sistem')->assertSee('periode pengajuan 2026-08 sudah ditutup.')
            ->assertDontSee('data-action="'.route('approval-atasan.approve', $expired).'"', false);
        $this->assertSame('REJECTED_ATASAN', $expired->fresh()->status);
        $this->assertSame('PENDING_ATASAN', $other->fresh()->status);
        $this->assertSame('APPROVED_ATASAN', $approved->fresh()->status);
        $this->assertSame('PENDING_ATASAN', $open->fresh()->status);
        $this->assertDatabaseCount('bs_activity_logs', 1);
    }

    public function test_atasan_detail_and_requester_history_identify_system_rejection(): void
    {
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));
        $this->get(route('approval-atasan.detail', $bs))->assertOk()
            ->assertSee('Ditolak Sistem')->assertSee('Pengajuan ini ditolak oleh sistem.')
            ->assertDontSee('Anda menolak pengajuan ini.')
            ->assertDontSee('data-action="'.route('approval-atasan.approve', $bs).'"', false);
        $this->actingAs(User::findOrFail(1));
        $this->get(route('pengajuan-bs.index'))->assertOk()->assertSee('Ditolak Sistem');
        $this->get(route('pengajuan-bs.show', $bs))->assertOk()
            ->assertSee('Ditolak Sistem')->assertSee('periode pengajuan 2026-09 sudah ditutup.');
        Notification::assertCount(1);
    }

    public function test_atasan_unauthorized_actions_do_not_reject_closed_bs(): void
    {
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $bs = $this->bs(['status' => 'PENDING_ATASAN', 'atasan_user_id' => 3], false);
        $this->actingAs(User::findOrFail(2));
        $this->get(route('approval-atasan.detail', $bs))->assertForbidden();
        foreach (['approve', 'reject'] as $action) {
            $this->post(route('approval-atasan.'.$action, $bs))->assertForbidden();
        }
        $this->assertSame('PENDING_ATASAN', $bs->fresh()->status);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        Notification::assertNothingSent();
    }

    public function test_atasan_period_cleanup_command_preserves_open_and_processed_bs(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $oldOpen = $this->bs(['status' => 'PENDING_ATASAN', 'periode' => '2026-08', 'tanggal' => '2026-08-01'], false);
        $missing = $this->bs(['status' => 'PENDING_ATASAN', 'periode' => '202607', 'tanggal' => '2026-07-01'], false);
        $open = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $processed = [];
        foreach (['APPROVED_ATASAN', 'DISBURSED', 'SETTLED', 'REJECTED_ATASAN', 'REJECTED_FINANCE'] as $status) {
            $processed[] = $this->bs(['status' => $status, 'periode' => '2026-07', 'tanggal' => '2026-07-01'], false);
        }
        $this->artisan('bs:reject-closed-periods')->expectsOutput('1 pengajuan BS ditolak otomatis oleh sistem.')->assertSuccessful();
        $this->artisan('bs:reject-closed-periods')->expectsOutput('0 pengajuan BS ditolak otomatis oleh sistem.')->assertSuccessful();
        $this->assertSame('REJECTED_ATASAN', $missing->fresh()->status);
        foreach ([$oldOpen, $open, ...$processed] as $bs) {
            $this->assertSame($bs->status, $bs->fresh()->status);
        }
        $this->assertDatabaseCount('bs_activity_logs', 1);
        Notification::assertCount(1);
    }

    public function test_atasan_pending_bs_is_rejected_during_close_and_stays_rejected_after_reopening(): void
    {
        $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $this->travelTo(now()->setDate(2026, 10, 2));
        $this->actingAs(User::findOrFail(2));
        $this->post(route('periode.close', $period))->assertSessionHasNoErrors();
        $this->assertSame('CLOSED', $period->fresh()->status);
        $this->assertSame('REJECTED_ATASAN', $bs->fresh()->status);
        $this->post(route('periode.reopen', $period), ['reason' => 'Koreksi periode'])->assertSessionHasNoErrors();
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertSame('REJECTED_ATASAN', $bs->fresh()->status);
        $this->assertDatabaseCount('bs_activity_logs', 1);
        Notification::assertCount(1);
    }

    public function test_atasan_system_rejection_rolls_back_when_audit_fails(): void
    {
        $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $this->actingAs(User::findOrFail(2));
        BsActivityLog::creating(fn () => throw new \RuntimeException('Simulated audit failure'));
        try {
            $this->post(route('approval-atasan.approve', $bs))->assertStatus(500);
        } finally {
            BsActivityLog::flushEventListeners();
        }
        $this->assertSame('PENDING_ATASAN', $bs->fresh()->status);
        $this->assertNull($bs->fresh()->rejection_reason);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        Notification::assertNothingSent();
    }

    public function test_atasan_system_rejection_rolls_back_when_period_close_fails(): void
    {
        $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $this->travelTo(now()->setDate(2026, 10, 2));
        $legacy = \Mockery::mock(\App\Services\CashFlowService::class);
        $legacy->shouldReceive('ensureSnapshotForPeriod')->once()->andThrow(new \RuntimeException('Simulated closing failure'));
        $this->app->instance(\App\Services\CashFlowService::class, $legacy);
        try {
            app(BsPeriodClosingService::class)->close($period->id);
            $this->fail('Expected closing failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated closing failure', $e->getMessage());
        }
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertSame('PENDING_ATASAN', $bs->fresh()->status);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        Notification::assertNothingSent();
    }
}
