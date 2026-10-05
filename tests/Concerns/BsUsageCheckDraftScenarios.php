<?php

namespace Tests\Concerns;

use App\Models\BsUsageCheck;
use App\Models\BsUsageCheckDetail;
use App\Models\BsUsageCheckDraft;
use App\Models\User;
use App\Services\BsUsageCheckService;
use App\Services\BsUsageDashboardService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait BsUsageCheckDraftScenarios
{
    public function test_new_finance_decisions_exclude_revision_and_legacy_draft_requires_an_explicit_choice(): void
    {
        $source = $this->source();
        $url = route('bs-usage-checks.store', ['OPR', $source->id]);
        $data = $this->draftPayload($source);
        foreach (['draft', 'confirm'] as $action) {
            $this->postJson($url, array_replace($data, ['save_action' => $action, 'keputusan' => 'REVISION', 'catatan_keputusan' => 'Perbaiki']))
                ->assertUnprocessable()->assertJsonValidationErrors('keputusan');
        }
        $this->post($url, $data)->assertSessionHasNoErrors();
        // This simulates a draft saved before the two-decision workflow was introduced.
        DB::table('bs_usage_check_drafts')->update(['keputusan' => 'REVISION', 'catatan_keputusan' => 'Catatan lama tetap tersedia']);
        $form = $this->get(route('bs-usage-checks.create', ['OPR', $source->id]))->assertOk()
            ->assertSee('Catatan lama tetap tersedia')->assertDontSee('value="REVISION"', false);
        $this->assertSame('', $form->viewData('defaults')['keputusan']);
        $data = $this->draftPayload($source);
        $data['save_action'] = 'confirm';
        $this->postJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('keputusan');
        $this->postJson($url, $data + ['keputusan' => ''])->assertUnprocessable()->assertJsonValidationErrors('keputusan');
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $this->post($url, $data + ['keputusan' => 'REJECTED', 'catatan_keputusan' => 'Bukti tidak sesuai'])
            ->assertSessionHasNoErrors();
        $this->assertSame('REJECTED', BsUsageCheck::firstOrFail()->keputusan);
        $this->assertSame('0.00', BsUsageCheck::first()->details->first()->nominal_disetujui);
    }

    private function draftPayload(Model $source, string $type = 'OPR', array $amounts = []): array
    {
        $form = $this->get(route('bs-usage-checks.create', [$type, $source->id]))->assertOk();
        return $this->payload($source, $type, $amounts) + [
            'save_action' => 'draft', 'review_token' => $form->viewData('reviewToken'),
        ];
    }

    public function test_finance_draft_saves_incomplete_work_without_publishing_a_result(): void
    {
        $source = $this->source();
        $source->update(['status' => 'VALIDATED']);
        $data = $this->draftPayload($source);
        $data['details'][0]['nominal_disetujui'] = null;
        $data['details'][0]['catatan'] = 'PRIVATE DRAFT NOTE';
        $data['keputusan'] = 'APPROVED';
        $data['catatan_keputusan'] = null;
        $this->post(route('bs-usage-checks.store', ['OPR', $source->id]), $data)
            ->assertSessionHasNoErrors()->assertRedirect(route('bs-usage-checks.create', ['OPR', $source->id]));
        $draft = BsUsageCheckDraft::firstOrFail();
        $this->assertSame(1, $draft->version);
        $this->assertNull($draft->details[0]['nominal_disetujui']);
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $this->assertDatabaseCount('document_number_sequences', 0);
        $this->assertFalse($source->financeChecks()->exists());
        $this->assertSame('PENDING', app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(1), 'OPR', $source->id)->finance_status);
        $this->get(route('bs-usage-checks.index'))->assertOk()->assertSee('Lanjutkan Draft');
        $this->get(route('bs-usage-checks.index', ['tampilan' => 'draft']))->assertOk()
            ->assertSee('PRIVATE DRAFT NOTE')->assertViewHas('drafts', fn ($drafts) => $drafts->total() === 1);
        $this->get(route('bs-usage-checks.report', ['mode' => 'semua']))->assertOk()->assertDontSee('PRIVATE DRAFT NOTE')
            ->assertViewHas('rows', fn ($rows) => $rows->isEmpty());
        $form = $this->get(route('bs-usage-checks.create', ['OPR', $source->id]))->assertOk()->assertSee('Simpan Draft');
        $this->assertNull($form->viewData('items')->first()['nominal_disetujui']);
        $this->assertSame('APPROVED', $form->viewData('defaults')['keputusan']);
        $this->actingAs(User::findOrFail(1));
        $this->get(route('bs-usage-checks.index', ['tampilan' => 'draft']))->assertForbidden();
        $this->postJson(route('bs-usage-checks.store', ['OPR', $source->id]), $data)->assertForbidden();
    }

    public function test_finance_draft_update_and_confirmation_keep_earlier_documents_immutable(): void
    {
        $source = $this->source();
        $source->update(['status' => 'VALIDATED']);
        $first = $this->save($source, approved: [1 => '80']);
        $original = $first->load('details')->toArray();
        $data = $this->draftPayload($source, amounts: [1 => '70']);
        $url = route('bs-usage-checks.store', ['OPR', $source->id]);
        $this->post($url, $data)->assertSessionHasNoErrors();
        $draftId = BsUsageCheckDraft::firstOrFail()->id;
        $this->assertEquals($first->id, BsUsageCheckDraft::first()->revision_of_id);
        $this->assertEquals(130, app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(1), 'OPR', $source->id)->nominal_finance);
        $data = $this->draftPayload($source, amounts: [1 => '60']);
        $this->post($url, $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('bs_usage_check_drafts', 1);
        $this->assertSame($draftId, BsUsageCheckDraft::first()->id);
        $this->assertSame(2, BsUsageCheckDraft::first()->version);
        $data = $this->draftPayload($source, amounts: [1 => '60']);
        $data['save_action'] = 'confirm';
        $this->post($url, $data)->assertSessionHasNoErrors()->assertRedirect();
        $last = BsUsageCheck::latest('id')->first();
        $this->assertNotSame($first->document_no, $last->document_no);
        $this->assertEquals($first->id, $last->revision_of_id);
        $this->assertSame($original, $first->fresh()->load('details')->toArray());
        $this->assertDatabaseCount('bs_usage_check_drafts', 0);
        $this->assertDatabaseCount('bs_usage_checks', 2);
        $this->assertEquals($last->id, app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(1), 'OPR', $source->id)->check_id);
        $this->get(route('bs-usage-checks.show', $last))->assertOk()->assertSee('Ubah Hasil Pemeriksaan')->assertSee($first->document_no);
        $this->postJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('review_token');
        $this->assertDatabaseCount('bs_usage_checks', 2);
    }

    public function test_finance_draft_rejects_stale_tabs_and_tracks_the_latest_finance_editor(): void
    {
        $source = $this->source();
        $url = route('bs-usage-checks.store', ['OPR', $source->id]);
        $old = $this->draftPayload($source);
        $this->post($url, $old)->assertSessionHasNoErrors();
        $this->postJson($url, $old)->assertUnprocessable()->assertJsonValidationErrors('review_token');
        $firstTab = $this->draftPayload($source);
        $otherFinance = User::findOrFail(3);
        $otherFinance->givePermissionTo('akses.approval-finance');
        $this->actingAs($otherFinance);
        $new = $this->draftPayload($source, amounts: [1 => '45']);
        $new['user_checker_id'] = 1;
        $this->post($url, $new)->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail(2));
        $firstTab['save_action'] = 'confirm';
        $this->postJson($url, $firstTab)->assertUnprocessable()->assertJsonValidationErrors('review_token');
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $draft = BsUsageCheckDraft::firstOrFail();
        $this->assertEquals(3, $draft->user_checker_id);
        $this->assertSame('45.00', $draft->details[0]['nominal_disetujui']);
        $new['review_token'] = 'tampered';
        $this->postJson($url, $new)->assertUnprocessable()->assertJsonValidationErrors('review_token');
        $this->assertSame(2, $draft->fresh()->version);
    }

    public function test_finance_draft_refreshes_changed_source_items_without_publishing_stale_amounts(): void
    {
        $source = $this->source();
        $first = $this->save($source);
        $url = route('bs-usage-checks.store', ['OPR', $source->id]);
        $this->post($url, $this->draftPayload($source, amounts: [1 => '80', 2 => '40']))->assertSessionHasNoErrors();
        $stale = $this->draftPayload($source);
        $source->details()->where('cost_category_id', 1)->update(['nominal' => 200]);
        $stale['save_action'] = 'confirm';
        $this->postJson($url, $stale)->assertUnprocessable()->assertJsonValidationErrors('source_token');
        $form = $this->get(route('bs-usage-checks.create', ['OPR', $source->id]))->assertOk()->assertSee('Pengajuan berubah');
        $items = $form->viewData('items')->keyBy('cost_category_id');
        $this->assertSame('200.00', $items[1]['nominal_disetujui']);
        $this->assertSame('40.00', $items[2]['nominal_disetujui']);
        $this->assertSame('100.00', BsUsageCheckDraft::first()->details[0]['nominal_asal']);
        $this->assertDatabaseCount('bs_usage_checks', 1);
        $this->get(route('bs-usage-checks.index'))->assertOk()
            ->assertViewHas('sources', fn ($sources) => $sources->first()->finance_status === 'RECHECK' && $sources->first()->selisih === null);
        $data = $this->draftPayload($source, amounts: [1 => '190', 2 => '40']);
        $data['save_action'] = 'confirm';
        $this->post($url, $data)->assertSessionHasNoErrors();
        $this->assertEquals($first->id, BsUsageCheck::latest('id')->first()->revision_of_id);
        $this->assertSame('100.00', $first->details()->first()->nominal_asal);
        $this->assertDatabaseCount('bs_usage_check_drafts', 0);
    }

    public function test_finance_draft_confirmation_validates_complete_amounts_and_decision_notes(): void
    {
        $source = $this->source();
        $url = route('bs-usage-checks.store', ['OPR', $source->id]);
        $data = $this->draftPayload($source);
        $data['details'][0]['nominal_disetujui'] = null;
        $data['keputusan'] = 'APPROVED';
        $this->post($url, $data)->assertSessionHasNoErrors();
        $data = $this->draftPayload($source);
        $data['details'][0]['nominal_disetujui'] = null;
        $data['keputusan'] = 'REJECTED';
        $data['save_action'] = 'confirm';
        $this->postJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors(['details.0.nominal_disetujui', 'catatan_keputusan']);
        $this->assertDatabaseCount('bs_usage_check_drafts', 1);
        $this->assertDatabaseCount('bs_usage_checks', 0);
        foreach (['-1', '1.001', '1e2', 'bad'] as $amount) {
            $invalid = $this->draftPayload($source);
            $invalid['details'][0]['nominal_disetujui'] = $amount;
            $this->postJson($url, $invalid)->assertUnprocessable()->assertJsonValidationErrors('details.0.nominal_disetujui');
        }
        $data = $this->draftPayload($source);
        $data['save_action'] = 'confirm';
        $data['keputusan'] = 'REJECTED';
        $data['catatan_keputusan'] = 'Tidak sesuai pengajuan';
        $this->post($url, $data)->assertSessionHasNoErrors();
        $this->assertEquals(0, BsUsageCheckDetail::sum('nominal_disetujui'));
        $this->assertDatabaseCount('bs_usage_check_drafts', 0);
    }

    public function test_finance_draft_survives_failure_during_confirmation(): void
    {
        $source = $this->source();
        $this->post(route('bs-usage-checks.store', ['OPR', $source->id]), $this->draftPayload($source))->assertSessionHasNoErrors();
        $original = BsUsageCheckDraft::first()->toArray();
        $data = $this->draftPayload($source);
        $data['save_action'] = 'confirm';
        BsUsageCheckDetail::creating(function ($detail) {
            if ($detail->cost_category_id === 2) throw new \RuntimeException('Draft confirmation failed');
        });
        try {
            app(BsUsageCheckService::class)->store(User::findOrFail(2), 'OPR', $source->id, $data);
            $this->fail('Expected an insert failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Draft confirmation failed', $exception->getMessage());
        } finally {
            BsUsageCheckDetail::flushEventListeners();
        }
        $this->assertSame($original, BsUsageCheckDraft::first()->toArray());
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $this->assertDatabaseCount('bs_usage_check_details', 0);
        $this->assertDatabaseCount('document_number_sequences', 0);
    }

    public function test_finance_drafts_cover_both_event_modules_and_pd_and_keep_owner_confirmation_required(): void
    {
        foreach (['PD', 'EDK', 'ELK'] as $type) {
            if ($type === 'PD') {
                $source = $this->source('PD');
            } else {
                $model = BsUsageCheckService::TYPES[$type];
                $id = DB::table((new $model)->getTable())->insertGetId([
                    'transaksi_id' => $type.'-DRAFT-TEST', 'user_id' => 1, 'nominal' => '120',
                    'tanggal' => '2026-09-01', 'periode' => '2026-09', 'status' => 'DRAFT',
                ]);
                $source = $model::findOrFail($id);
                $this->postJson(route('bs-usage-checks.store', [$type, $id]), $this->draftPayload($source, $type))
                    ->assertUnprocessable()->assertJsonValidationErrors('source');
                $source->forceFill(['status' => 'VALIDATED'])->save();
            }
            $url = route('bs-usage-checks.store', [$type, $source->id]);
            $this->post($url, $this->draftPayload($source, $type))->assertSessionHasNoErrors();
            $data = $this->draftPayload($source, $type);
            $data['save_action'] = 'confirm';
            $this->post($url, $data)->assertSessionHasNoErrors();
            $this->assertDatabaseHas('bs_usage_checks', ['jenis_transaksi' => $type, 'transaksi_pemakaian_id' => $source->id]);
        }
        $this->assertDatabaseCount('bs_usage_check_drafts', 0);
        $this->assertDatabaseCount('bs_usage_checks', 3);
    }

    public function test_finance_drafts_can_be_found_by_filters_even_when_source_is_unavailable(): void
    {
        $opr = $this->source();
        $pd = $this->source('PD');
        foreach (['OPR' => $opr, 'PD' => $pd] as $type => $source) {
            $this->post(route('bs-usage-checks.store', [$type, $source->id]), $this->draftPayload($source, $type))->assertSessionHasNoErrors();
        }
        $opr->delete();
        $filters = ['tampilan' => 'draft', 'jenis_source' => 'OPR', 'source_id' => $opr->id, 'periode' => '202609', 'user_id' => 1];
        $this->get(route('bs-usage-checks.index', $filters))->assertOk()->assertSee('Sumber sudah tidak tersedia.')
            ->assertViewHas('drafts', fn ($drafts) => $drafts->total() === 1 && $drafts->first()->jenis_transaksi === 'OPR');
        $this->get(route('bs-usage-checks.index', ['tampilan' => 'draft', 'periode' => '2027-01']))->assertOk()
            ->assertViewHas('drafts', fn ($drafts) => $drafts->isEmpty());
    }
}
