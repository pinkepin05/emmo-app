<?php

namespace Tests\Concerns;

use App\Models\BsUsageCheck;
use App\Support\Money;
use App\Models\Periode;
use App\Models\User;
use App\Services\BsCashFlowService;
use App\Services\BsUsageCheckService;
use App\Services\BsUsageDashboardService;
use Illuminate\Support\Facades\DB;

trait FinanceRejectionScenarios
{
    public function test_finance_rejection_owner_correction_resubmission_and_new_decision_preserve_history_and_cash(): void
    {
        foreach (['OPR', 'PD'] as $type) {
            $this->actingAs(User::findOrFail(1));
            $source = $this->expense($this->bs(), amount: '250000', travel: $type === 'PD');
            $prefix = $type === 'PD' ? 'transaksi_perjalanan_dinas' : 'transaksi_operasional';
            $key = $type === 'PD' ? $source->transaksi_id : $source->id;
            $this->post(route($prefix.'.validate', $key))->assertSessionHasNoErrors();
            $source->refresh();
            $this->assertEquals(1, $source->submission_version);
            $before = app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar'];
            $rejection = $this->recapDecision($source, $type, 'REJECTED', '0', 'Bukti perjalanan belum sesuai');
            $original = $rejection->load('details')->toArray();
            $this->assertSame($before, app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar']);
            $this->assertSame('VALIDATED', $source->fresh()->status);
            $this->actingAs(User::findOrFail(1));
            $detail = route('transaction-recap.show', [$type, $source->id]);
            $this->get($detail)->assertOk()->assertSee('Perbaiki Pengajuan')->assertSee('Bukti perjalanan belum sesuai')->assertDontSee('Catatan internal Finance');
            $this->post(route('transaction-recap.correct', [$type, $source->id]), ['check_id' => $rejection->id])
                ->assertSessionHasNoErrors()->assertRedirect(route($prefix.'.show', $key).'?edit=1');
            $this->assertSame('DRAFT', $source->fresh()->status);
            $this->get($detail)->assertOk()->assertSee('Lanjutkan Perbaikan')->assertSee($rejection->document_no);
            $this->get(route($prefix.'.show', $key).'?edit=1')->assertOk();
            $this->actingAs(User::findOrFail(2));
            $payload = ['tanggal_pengecekan' => '2026-09-16', 'source_token' => $this->recapToken($source, $type),
                'keputusan' => 'APPROVED', 'details' => [['source_detail_id' => $source->details()->first()->id, 'cost_category_id' => 1, 'nominal_disetujui' => '250000']]];
            $this->postJson(route('bs-usage-checks.store', [$type, $source->id]), $payload)
                ->assertUnprocessable()->assertJsonValidationErrors('source');
            $this->actingAs(User::findOrFail(1));
            // A corrected explanation with the SAME nominal must still trigger a fresh review.
            $item = $source->details()->firstOrFail();
            $edit = ['tanggal' => '2026-09-20', 'description' => 'Penjelasan diperbaiki',
                'details' => [$item->id => ['cost_category_id' => 1, 'nominal' => '250000', 'remark' => 'Keterangan bukti sudah diperbaiki']]];
            if ($type === 'PD') $edit += ['teritory' => 'Jawa', 'tujuan_dinas_id' => 1, 'jarak_km' => 20, 'durasi_jam' => 2, 'bs_owner_id' => 1];
            $this->put(route($prefix.'.update', $key), $edit)->assertSessionHasNoErrors();
            $this->assertSame('Keterangan bukti sudah diperbaiki', $item->fresh()->remark);
            $this->post(route($prefix.'.validate', $key))->assertSessionHasNoErrors();
            $source->refresh();
            $this->assertEquals(2, $source->submission_version);
            $this->assertEquals(2, DB::table('bs_usage_logs')->where('transaction_type', $type === 'PD' ? 'perjalanan_dinas' : 'operasional_sales')->where('transaction_id', $source->id)->count());
            $this->assertSame($before, app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar']);
            $row = app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(1), $type, $source->id);
            $this->assertSame('PENDING', $row->finance_status);
            $this->assertNull($row->selisih);
            $this->postJson(route('transaction-recap.correct', [$type, $source->id]), ['check_id' => $rejection->id])
                ->assertUnprocessable()->assertJsonValidationErrors('check_id');
            $approval = $this->recapDecision($source, $type, 'APPROVED', '200000');
            $this->assertNotSame($approval->document_no, $rejection->document_no);
            $this->assertEquals($rejection->id, $approval->revision_of_id);
            $this->assertSame($original, $rejection->fresh()->load('details')->toArray());
            $row = app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(1), $type, $source->id);
            $this->assertSame('APPROVED', $row->finance_status);
            $this->assertEquals(200000, $row->nominal_finance);
            $this->assertEquals(50000, $row->selisih);
            $this->assertSame(Money::decimal(Money::minor($before) + 20000000), app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar']);
            $this->get($detail)->assertOk()->assertSee($rejection->document_no)->assertSee($approval->document_no)->assertDontSee('Catatan internal Finance');
        }
    }

    public function test_finance_correction_requires_owner_permission_latest_rejection_and_open_period(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '100');
        $rejected = $this->recapDecision($source, 'OPR', 'REJECTED', '0');
        $url = route('transaction-recap.correct', ['OPR', $source->id]);
        foreach ([2, 3] as $id) {
            $this->actingAs(User::findOrFail($id));
            $this->postJson($url, ['check_id' => $rejected->id])->assertForbidden();
        }
        $this->actingAs(User::findOrFail(1));
        User::findOrFail(1)->revokePermissionTo('akses.transaksi-operasional');
        $this->actingAs(User::findOrFail(1));
        $this->postJson($url, ['check_id' => $rejected->id])->assertForbidden();
        User::findOrFail(1)->givePermissionTo('akses.transaksi-operasional');
        $this->actingAs(User::findOrFail(1));
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $this->postJson($url, ['check_id' => $rejected->id])->assertUnprocessable()->assertJsonValidationErrors('tanggal');
        Periode::where('periode', '2026-09')->update(['status' => 'OPEN']);
        $this->recapDecision($source, 'OPR', 'APPROVED', '100');
        $this->actingAs(User::findOrFail(1));
        $this->postJson($url, ['check_id' => $rejected->id])->assertUnprocessable()->assertJsonValidationErrors('check_id');
        $this->assertSame('VALIDATED', $source->fresh()->status);
    }

    public function test_finance_rejected_member_trip_requires_bs_owner_approval_again(): void
    {
        $source = $this->pendingMemberUsage('250000');
        $this->actingAs(User::findOrFail(1));
        app(\App\Services\BsValidationService::class)->approve($source, $source->approval_version);
        $source->refresh();
        $rejected = $this->recapDecision($source, 'PD', 'REJECTED', '0');
        $this->actingAs(User::findOrFail(3));
        $this->post(route('transaction-recap.correct', ['PD', $source->id]), ['check_id' => $rejected->id])->assertSessionHasNoErrors();
        $this->post(route('transaksi_perjalanan_dinas.validate', $source->transaksi_id))->assertSessionHasNoErrors();
        $source->refresh();
        $this->assertSame('PENDING_OWNER', $source->status);
        $this->assertEquals(1, $source->submission_version);
        $this->assertNull(app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(3), 'PD', $source->id));
        $this->actingAs(User::findOrFail(1));
        app(\App\Services\BsValidationService::class)->approve($source, $source->approval_version);
        $source->refresh();
        $this->assertSame('VALIDATED', $source->status);
        $this->assertEquals(2, $source->submission_version);
        $this->assertSame('PENDING', app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(3), 'PD', $source->id)->finance_status);
        $again = $this->recapDecision($source, 'PD', 'REJECTED', '0', 'Bukti masih tidak cocok');
        $this->assertEquals($rejected->id, $again->revision_of_id);
        $this->assertSame('REJECTED', app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(3), 'PD', $source->id)->finance_status);
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar']);
    }
}
