<?php

namespace Tests\Concerns;

use App\Models\BsUsageCheck;
use App\Models\BsUsageCheckDraft;
use App\Models\User;
use App\Services\BsUsageDashboardService;
use App\Services\FinanceBsExpenseService;

trait FinanceItemApprovalScenarios
{
    private function renderedFinanceDetails($response): array
    {
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);
        $details = [];
        foreach ($xpath->query('//form[@id="bs-check-form"]//*[@name]') as $input) {
            if (!preg_match('/^details\[(\d+)\]\[(\w+)\]$/', $input->getAttribute('name'), $parts)) continue;
            $details[$parts[1]][$parts[2]] = $input->tagName === 'textarea' ? $input->textContent : $input->getAttribute('value');
        }

        return $details;
    }

    public function test_finance_form_preserves_saved_amounts_and_notes_through_draft_and_confirmation(): void
    {
        foreach (['OPR', 'PD'] as $type) {
            $source = $this->source($type, []);
            foreach (['Bensin Mobil', 'Cek Oli'] as $remark) {
                $source->details()->create(['cost_category_id' => 1, 'nominal' => '100000', 'remark' => $remark]);
            }
            $lines = $source->details()->orderBy('id')->get();
            $data = $this->draftPayload($source, $type);
            $data['details'][0]['nominal_disetujui'] = '100000';
            $data['details'][1]['nominal_disetujui'] = '70000';
            $data['details'][1]['catatan'] = 'Oli disetujui sebagian';
            $url = route('bs-usage-checks.store', [$type, $source->id]);
            $this->post($url, $data)->assertSessionHasNoErrors();

            $form = $this->get(route('bs-usage-checks.create', [$type, $source->id]))->assertOk();
            $rendered = $this->renderedFinanceDetails($form);
            $this->assertSame('100000.00', $rendered[$lines[0]->id]['nominal_disetujui']);
            $this->assertSame('70000.00', $rendered[$lines[1]->id]['nominal_disetujui']);
            $this->assertSame('Oli disetujui sebagian', $rendered[$lines[1]->id]['catatan']);
            $data['details'] = $rendered;
            $data['save_action'] = 'confirm';
            $data['review_token'] = $form->viewData('reviewToken');
            $this->post($url, $data)->assertSessionHasNoErrors();
            $check = BsUsageCheck::latest('id')->firstOrFail();
            $this->assertEquals(170000, $check->details->sum('nominal_disetujui'));
            $this->assertEquals(30000, $check->details->sum('selisih'));
            $reopened = $this->get(route('bs-usage-checks.create', [$type, $source->id]))->assertOk();
            $reopenedDetails = $this->renderedFinanceDetails($reopened);
            foreach ($rendered as $id => $detail) {
                $this->assertSame(\App\Support\Money::minor($detail['nominal_disetujui']), \App\Support\Money::minor($reopenedDetails[$id]['nominal_disetujui']));
                $this->assertSame($detail['catatan'], $reopenedDetails[$id]['catatan']);
            }
        }
    }

    public function test_finance_form_preserves_empty_zero_fractional_and_validation_values(): void
    {
        $source = $this->source('PD', [1 => '100000', 2 => '100000']);
        $data = $this->draftPayload($source, 'PD');
        $data['details'][0]['nominal_disetujui'] = null;
        $data['details'][1]['nominal_disetujui'] = '0';
        $url = route('bs-usage-checks.store', ['PD', $source->id]);
        $formUrl = route('bs-usage-checks.create', ['PD', $source->id]);
        $this->post($url, $data)->assertSessionHasNoErrors();
        $form = $this->get($formUrl)->assertOk();
        $rendered = array_values($this->renderedFinanceDetails($form));
        $this->assertSame('', $rendered[0]['nominal_disetujui']);
        $this->assertSame('0.00', $rendered[1]['nominal_disetujui']);

        $data = $this->draftPayload($source, 'PD');
        $data['details'][0]['nominal_disetujui'] = '70000.50';
        $data['details'][1]['nominal_disetujui'] = '0.25';
        $this->post($url, $data)->assertSessionHasNoErrors();
        $form = $this->get($formUrl)->assertOk();
        $rendered = array_values($this->renderedFinanceDetails($form));
        $this->assertSame('70000.50', $rendered[0]['nominal_disetujui']);
        $this->assertSame('0.25', $rendered[1]['nominal_disetujui']);

        $data['details'] = $this->renderedFinanceDetails($form);
        $first = array_key_first($data['details']);
        $data['details'][$first]['nominal_disetujui'] = '65000.75';
        $data['details'][$first]['catatan'] = 'Catatan belum tersimpan';
        $data['tanggal_pengecekan'] = 'invalid';
        $this->from($formUrl)->post($url, $data)->assertSessionHasErrors('tanggal_pengecekan');
        $returned = $this->renderedFinanceDetails($this->get($formUrl)->assertOk());
        $this->assertSame('65000.75', $returned[$first]['nominal_disetujui']);
        $this->assertSame('Catatan belum tersimpan', $returned[$first]['catatan']);
    }

    private function itemApprovalSource(string $type)
    {
        $source = $this->source($type, []);
        $source->details()->create(['cost_category_id' => 1, 'nominal' => '150000', 'remark' => 'BBM']);
        $source->details()->create(['cost_category_id' => 1, 'nominal' => '25000', 'remark' => 'Uang Makan']);

        return $source;
    }

    public function test_finance_item_drafts_and_confirmations_keep_repeated_categories_separate(): void
    {
        foreach (['OPR', 'PD'] as $type) {
            $source = $this->itemApprovalSource($type);
            $lines = $source->details()->orderBy('id')->get();
            $form = $this->get(route('bs-usage-checks.create', [$type, $source->id]))->assertOk()
                ->assertSee('BBM')->assertSee('Uang Makan')->assertDontSee('Item dengan kategori sama dijumlahkan.');
            $this->assertCount(2, $form->viewData('items'));
            foreach ($lines as $line) $form->assertSee('name="details['.$line->id.'][source_detail_id]"', false);
            $data = $this->draftPayload($source, $type);
            $data['details'][0]['nominal_disetujui'] = '100000';
            $data['details'][0]['catatan'] = 'Catatan BBM';
            $data['details'][1]['nominal_disetujui'] = '20000';
            $data['details'][1]['catatan'] = 'Catatan makan';
            $data['details'] = array_reverse($data['details']);
            $url = route('bs-usage-checks.store', [$type, $source->id]);
            $this->post($url, $data)->assertSessionHasNoErrors();
            $this->get(route('bs-usage-checks.index', ['tampilan' => 'draft']))->assertOk()
                ->assertSee('BBM')->assertSee('Uang Makan');
            $form = $this->get(route('bs-usage-checks.create', [$type, $source->id]))->assertOk();
            $items = $form->viewData('items')->keyBy('source_detail_id');
            $this->assertSame('100000.00', $items[$lines[0]->id]['nominal_disetujui']);
            $this->assertSame('20000.00', $items[$lines[1]->id]['nominal_disetujui']);
            $this->assertSame('Catatan BBM', $items[$lines[0]->id]['catatan']);
            $data['save_action'] = 'confirm';
            $data['review_token'] = $form->viewData('reviewToken');
            $this->post($url, $data)->assertSessionHasNoErrors();
            $check = BsUsageCheck::latest('id')->firstOrFail();
            $saved = $check->details->keyBy('source_detail_id');
            $this->assertCount(2, $saved);
            $this->assertSame('150000.00', $saved[$lines[0]->id]->nominal_asal);
            $this->assertSame('100000.00', $saved[$lines[0]->id]->nominal_disetujui);
            $this->assertSame('50000.00', $saved[$lines[0]->id]->selisih);
            $this->assertSame('Catatan makan', $saved[$lines[1]->id]->catatan);
            $this->assertSame('BBM', $saved[$lines[0]->id]->source_remark);
            $this->assertSame(12000000, app(FinanceBsExpenseService::class)->approvedAmount($source->fresh()));
            $status = app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(2), $type, $source->id);
            $this->assertSame('APPROVED', $status->finance_status);
            $this->assertEquals(120000, $status->nominal_finance);
            $this->assertEquals(55000, $status->selisih);
            $this->get(route('bs-usage-checks.show', $check))->assertOk()->assertSee('BBM')->assertSee('Uang Makan');
            $this->get(route('bs-usage-checks.report', ['jenis_source' => $type, 'source_id' => $source->id]))
                ->assertOk()->assertSee('BBM')->assertSee('Uang Makan')
                ->assertViewHas('rows', fn ($rows) => $rows->total() === 2);
            $this->assertDatabaseCount('bs_usage_check_drafts', 0);
        }
    }

    public function test_finance_item_ids_cannot_be_missing_duplicated_or_borrowed_from_another_source(): void
    {
        $source = $this->itemApprovalSource('OPR');
        $other = $this->source(amounts: [1 => '150000']);
        $valid = $this->payload($source);
        $cases = [];
        $data = $valid;
        array_pop($data['details']);
        $cases[] = $data;
        $data = $valid;
        $data['details'][1]['source_detail_id'] = $data['details'][0]['source_detail_id'];
        $cases[] = $data;
        $data = $valid;
        $data['details'][0]['source_detail_id'] = $other->details()->first()->id;
        $cases[] = $data;
        $data = $valid;
        $data['details'][0]['cost_category_id'] = 2;
        $cases[] = $data;
        $data = $valid;
        unset($data['details'][0]['source_detail_id']);
        $cases[] = $data;
        $data = $valid;
        $data['details'][0]['source_remark'] = 'Keterangan palsu';
        $cases[] = $data;
        foreach ($cases as $data) $this->postJson(route('bs-usage-checks.store', ['OPR', $source->id]), $data)->assertUnprocessable();
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $this->assertDatabaseCount('bs_usage_check_details', 0);
    }

    public function test_finance_item_changes_require_recheck_even_when_category_totals_stay_the_same(): void
    {
        foreach (['OPR', 'PD'] as $type) {
            foreach (['amounts', 'remark', 'replacement'] as $change) {
                $source = $this->itemApprovalSource($type);
                $check = $this->save($source, $type);
                $lines = $source->details()->orderBy('id')->get();
                if ($change === 'amounts') {
                    $lines[0]->update(['nominal' => '140000']);
                    $lines[1]->update(['nominal' => '35000']);
                } elseif ($change === 'remark') {
                    $lines[0]->update(['remark' => 'BBM diperbaiki']);
                } else {
                    $lines[0]->delete();
                    $source->details()->create(['cost_category_id' => 1, 'nominal' => '150000', 'remark' => 'BBM']);
                }
                $this->assertNull(app(FinanceBsExpenseService::class)->currentCheck($source->fresh()));
                $status = app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(2), $type, $source->id);
                $this->assertSame('PENDING', $status->finance_status, $type.' '.$change);
                $this->assertNull($status->selisih);
                $this->assertSame('BBM', $check->details()->first()->source_remark);
                $this->assertSame('150000.00', $check->details()->first()->nominal_asal);
            }
        }
    }

    public function test_legacy_grouped_checks_keep_their_amounts_and_require_explicit_item_approvals_on_recheck(): void
    {
        $source = $this->itemApprovalSource('OPR');
        $old = $this->save($source);
        $old->details()->delete();
        $old->details()->create([
            'cost_category_id' => 1, 'cost_category_name' => 'Transport',
            'nominal_asal' => '175000', 'nominal_disetujui' => '100000', 'catatan' => 'Catatan gabungan lama',
        ]);
        $before = $old->fresh()->load('details')->toArray();
        $this->assertSame(10000000, app(FinanceBsExpenseService::class)->approvedAmount($source->fresh()));
        $this->assertSame('APPROVED', app(BsUsageDashboardService::class)->sourceResult(User::findOrFail(2), 'OPR', $source->id)->finance_status);
        $form = $this->get(route('bs-usage-checks.create', ['OPR', $source->id]))->assertOk()
            ->assertSee('Isi nominal persetujuan untuk setiap rincian');
        $this->assertTrue($form->viewData('needsItemReview'));
        $this->assertTrue($form->viewData('items')->every(fn ($item) => $item['nominal_disetujui'] === null));
        $this->get(route('bs-usage-checks.show', $old))->assertOk()->assertSee('gabungan per kategori')->assertSee('100.000,00');
        $data = $this->payload($source);
        $data['details'][0]['nominal_disetujui'] = '90000';
        $data['details'][1]['nominal_disetujui'] = '10000';
        $this->post(route('bs-usage-checks.store', ['OPR', $source->id]), $data)->assertSessionHasNoErrors();
        $this->assertSame($before, $old->fresh()->load('details')->toArray());
        $this->assertSame(10000000, app(FinanceBsExpenseService::class)->approvedAmount($source->fresh()));
    }

    public function test_legacy_grouped_drafts_are_not_silently_distributed_across_items(): void
    {
        $source = $this->itemApprovalSource('PD');
        $url = route('bs-usage-checks.store', ['PD', $source->id]);
        $this->post($url, $this->draftPayload($source, 'PD'))->assertSessionHasNoErrors();
        $draft = BsUsageCheckDraft::firstOrFail();
        $draft->update(['details' => [[
            'cost_category_id' => 1, 'cost_category_name' => 'Transport',
            'nominal_asal' => '175000.00', 'nominal_disetujui' => '100000.00', 'catatan' => 'Catatan gabungan lama',
        ]]]);
        $original = $draft->fresh()->toArray();
        $form = $this->get(route('bs-usage-checks.create', ['PD', $source->id]))->assertOk();
        $this->assertTrue($form->viewData('needsItemReview'));
        $this->assertTrue($form->viewData('items')->every(fn ($item) => $item['nominal_disetujui'] === null));
        $this->assertSame($original, $draft->fresh()->toArray());
        $data = $this->draftPayload($source, 'PD');
        $data['save_action'] = 'confirm';
        foreach ($data['details'] as &$item) $item['nominal_disetujui'] = null;
        unset($item);
        $this->postJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('details.0.nominal_disetujui');
        $this->assertSame($original, $draft->fresh()->toArray());
        $this->assertDatabaseCount('bs_usage_checks', 0);
    }
}
