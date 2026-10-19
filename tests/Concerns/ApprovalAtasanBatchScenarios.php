<?php

namespace Tests\Concerns;

use App\Models\BsActivityLog;
use App\Models\Periode;
use App\Models\User;
use App\Notifications\BsApproved;
use App\Notifications\BsRejected;
use Illuminate\Support\Facades\Notification;

trait ApprovalAtasanBatchScenarios
{
    public function test_atasan_batch_approves_only_selected_requests_and_keeps_the_period(): void
    {
        $first = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $second = $this->bs(['status' => 'PENDING_ATASAN', 'user_id' => 3], false);
        $unselected = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));
        $this->post(route('approval-atasan.batch-approve'), [
            'ids' => [$second->id, $first->id], 'approval_message' => 'Silakan diteruskan', 'periode' => '2026-09',
        ])->assertSessionHasNoErrors()->assertSessionHas('success')
            ->assertRedirect(route('approval-atasan.index', ['periode' => '2026-09']));
        foreach ([$first, $second] as $bs) {
            $this->assertSame('APPROVED_ATASAN', $bs->fresh()->status);
            $this->assertSame('Silakan diteruskan', $bs->fresh()->approval_message);
            $this->assertDatabaseHas('bs_activity_logs', ['pengajuan_bs_id' => $bs->id, 'activity_code' => 'APPROVED_ATASAN', 'actor_user_id' => 2]);
        }
        $this->assertSame('PENDING_ATASAN', $unselected->fresh()->status);
        Notification::assertSentToTimes(User::findOrFail(2), BsApproved::class, 2);
        $this->assertDatabaseCount('bs_activity_logs', 2);
        $this->assertDatabaseCount('approval_bs_finance', 0);
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
    }

    public function test_atasan_batch_reject_requires_a_reason_and_applies_it_to_every_selection(): void
    {
        $first = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $second = $this->bs(['status' => 'PENDING_ATASAN', 'user_id' => 3], false);
        $this->actingAs(User::findOrFail(2));
        $url = route('approval-atasan.batch-reject');
        $ids = [$first->id, $second->id];
        foreach (['', '   ', str_repeat('a', 1001)] as $reason) {
            $this->postJson($url, ['ids' => $ids, 'rejection_reason' => $reason])->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');
        }
        $this->assertSame('PENDING_ATASAN', $first->fresh()->status);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        Notification::assertNothingSent();
        $this->post($url, ['ids' => $ids, 'rejection_reason' => 'Lengkapi rincian biaya'])->assertSessionHasNoErrors()->assertSessionHas('success');
        foreach ([$first, $second] as $bs) {
            $this->assertSame('REJECTED_ATASAN', $bs->fresh()->status);
            $this->assertSame('Lengkapi rincian biaya', $bs->fresh()->rejection_reason);
            $this->assertDatabaseHas('bs_activity_logs', ['pengajuan_bs_id' => $bs->id, 'activity_code' => 'REJECTED_ATASAN']);
            Notification::assertSentTo($bs->user, BsRejected::class);
        }
        $this->assertDatabaseCount('approval_bs_finance', 0);
    }

    public function test_atasan_batch_cannot_include_another_supervisors_request_or_bypass_permission(): void
    {
        $own = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $foreign = $this->bs(['status' => 'PENDING_ATASAN', 'atasan_user_id' => 3], false);
        foreach (['approve', 'reject'] as $action) {
            $this->actingAs(User::findOrFail(2));
            $this->postJson(route('approval-atasan.batch-'.$action), [
                'ids' => [$own->id, $foreign->id], 'rejection_reason' => 'Tidak diizinkan',
            ])->assertForbidden();
            $this->actingAs(User::findOrFail(1));
            $this->postJson(route('approval-atasan.batch-'.$action), [
                'ids' => [$own->id], 'rejection_reason' => 'Tidak diizinkan',
            ])->assertForbidden();
        }
        $this->assertSame('PENDING_ATASAN', $own->fresh()->status);
        $this->assertSame('PENDING_ATASAN', $foreign->fresh()->status);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        Notification::assertNothingSent();
    }

    public function test_atasan_batch_validates_ids_before_changing_any_request(): void
    {
        $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));
        foreach (['approve', 'reject'] as $action) {
            foreach ([[], [$bs->id, $bs->id], [$bs->id, 999999], ['invalid'], array_fill(0, 101, $bs->id)] as $ids) {
                $this->postJson(route('approval-atasan.batch-'.$action), ['ids' => $ids, 'rejection_reason' => 'Uji validasi'])->assertUnprocessable();
            }
        }
        $this->postJson(route('approval-atasan.batch-approve'), ['ids' => [$bs->id], 'approval_message' => str_repeat('a', 1001)])
            ->assertUnprocessable()->assertJsonValidationErrors('approval_message');
        $this->assertSame('PENDING_ATASAN', $bs->fresh()->status);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        Notification::assertNothingSent();
    }

    public function test_atasan_batch_skips_processed_requests_and_retries_do_not_duplicate_logs(): void
    {
        $pending = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $approved = $this->bs(['status' => 'APPROVED_ATASAN'], false);
        $rejected = $this->bs(['status' => 'REJECTED_ATASAN', 'rejection_reason' => 'Alasan sebelumnya'], false);
        $this->actingAs(User::findOrFail(2));
        $ids = [$pending->id, $approved->id, $rejected->id];
        $this->post(route('approval-atasan.batch-approve'), ['ids' => $ids])->assertSessionHas('warning');
        $this->assertSame('APPROVED_ATASAN', $pending->fresh()->status);
        $this->post(route('approval-atasan.batch-approve'), ['ids' => $ids])->assertSessionHas('error');
        $this->post(route('approval-atasan.batch-reject'), ['ids' => $ids, 'rejection_reason' => 'Alasan baru'])->assertSessionHas('error');
        $this->assertSame('APPROVED_ATASAN', $approved->fresh()->status);
        $this->assertSame('Alasan sebelumnya', $rejected->fresh()->rejection_reason);
        $this->assertDatabaseCount('bs_activity_logs', 1);
        Notification::assertCount(1);
    }

    public function test_atasan_batch_uses_each_requests_period_and_preserves_system_rejections(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $this->actingAs(User::findOrFail(2));
        foreach (['approve', 'reject'] as $action) {
            $open = $this->bs(['status' => 'PENDING_ATASAN', 'periode' => '202608', 'tanggal' => '2026-08-15'], false);
            $closed = $this->bs(['status' => 'PENDING_ATASAN'], false);
            $this->post(route('approval-atasan.batch-'.$action), [
                'ids' => [$closed->id, $open->id], 'rejection_reason' => 'Alasan manual',
            ])->assertSessionHasNoErrors()->assertSessionHas('warning');
            $this->assertSame($action === 'approve' ? 'APPROVED_ATASAN' : 'REJECTED_ATASAN', $open->fresh()->status);
            $this->assertSame('REJECTED_ATASAN', $closed->fresh()->status);
            $this->assertStringContainsString('Ditolak otomatis oleh sistem', $closed->fresh()->rejection_reason);
            $this->assertDatabaseHas('bs_activity_logs', ['pengajuan_bs_id' => $closed->id, 'activity_code' => 'REJECTED_SYSTEM', 'actor_user_id' => null]);
        }
        $this->assertDatabaseCount('bs_activity_logs', 4);
        $this->assertDatabaseCount('approval_bs_finance', 0);
    }

    public function test_atasan_batch_rolls_back_all_decisions_and_notifications_when_audit_fails(): void
    {
        $first = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $second = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));
        foreach (['approve', 'reject'] as $action) {
            BsActivityLog::creating(function ($log) use ($second) {
                if ((int) $log->pengajuan_bs_id === (int) $second->id) throw new \RuntimeException('Simulated second audit failure');
            });
            try {
                $this->postJson(route('approval-atasan.batch-'.$action), [
                    'ids' => [$first->id, $second->id], 'rejection_reason' => 'Uji rollback',
                ])->assertStatus(500);
            } finally {
                BsActivityLog::flushEventListeners();
            }
            $this->assertSame('PENDING_ATASAN', $first->fresh()->status);
            $this->assertSame('PENDING_ATASAN', $second->fresh()->status);
            $this->assertDatabaseCount('bs_activity_logs', 0);
            Notification::assertNothingSent();
        }
    }

    public function test_atasan_batch_selection_contains_only_pending_requests_in_the_displayed_period(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $pending = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $approved = $this->bs(['status' => 'APPROVED_ATASAN'], false);
        $otherPeriod = $this->bs(['status' => 'PENDING_ATASAN', 'periode' => '2026-08', 'tanggal' => '2026-08-15'], false);
        $this->actingAs(User::findOrFail(2));
        $response = $this->get(route('approval-atasan.index', ['periode' => '2026-09']))->assertOk()
            ->assertSee('Select All (1 menunggu)')->assertSee('Approve Terpilih')->assertSee('Reject Terpilih')
            ->assertSee(route('approval-atasan.detail', $approved))->assertDontSee($otherPeriod->document_no);
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $inputs = (new \DOMXPath($document))->query('//input[@data-approval-select]');
        $this->assertCount(1, $inputs);
        $this->assertSame((string) $pending->id, $inputs->item(0)->getAttribute('value'));
    }
}
