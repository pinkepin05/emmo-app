<?php

namespace Tests\Concerns;

use App\Models\BsUsageCheck;
use App\Models\BsUsageCheckDraft;
use App\Models\Periode;
use App\Models\User;
use App\Services\FinanceBsExpenseService;
use App\Support\Money;

trait UnifiedTransactionScenarios
{
    private function combinedCostTable($response): array
    {
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);
        $tables = $xpath->query('//*[@id="rincian-pengajuan"]//table');
        $this->assertCount(1, $tables);
        $table = $tables->item(0);
        $this->assertSame(['Item', 'Keterangan', 'Foto', 'Nominal Asal', 'Nominal Disetujui', 'Selisih'],
            array_map(fn ($cell) => trim($cell->textContent), iterator_to_array($xpath->query('.//thead//th', $table))));

        return [$xpath, $table];
    }

    public function test_combined_cost_table_matches_photos_and_amounts_to_each_source_item(): void
    {
        foreach (['OPR', 'PD'] as $type) {
            $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '100000', travel: $type === 'PD');
            $first = $source->details()->first();
            $first->update(['remark' => 'Bensin Mobil', 'photo_path' => 'receipts/bensin.png']);
            $second = $source->details()->create(['cost_category_id' => 1, 'remark' => 'Cek Oli', 'nominal' => '100000']);
            $url = route('transaction-recap.show', [$type, $source->id]);
            $this->actingAs(User::findOrFail(1));
            [$xpath, $table] = $this->combinedCostTable($this->get($url)->assertOk());
            $this->assertSame('—', trim($xpath->query('.//tfoot//td', $table)->item(1)->textContent));
            $this->assertCount(2, $xpath->query('.//tbody/tr', $table));

            $this->actingAs(User::findOrFail(2));
            $form = $this->get(route('transaction-recap.show', ['jenis' => $type, 'source' => $source->id, 'pemeriksaan' => 1]))->assertOk();
            $payload = $this->workspacePayload($form, 'confirm');
            $payload['details'][$first->id]['nominal_disetujui'] = '100000';
            $payload['details'][$second->id]['nominal_disetujui'] = '70000';
            $payload['details'] = array_reverse($payload['details'], true);
            $this->post(route('bs-usage-checks.store', [$type, $source->id]), $payload)->assertSessionHasNoErrors();
            $check = $source->financeChecks()->latest('id')->firstOrFail();

            $this->actingAs(User::findOrFail(1));
            [$xpath, $table] = $this->combinedCostTable($this->get($url)->assertOk());
            foreach (['Bensin Mobil' => ['Rp 100.000,00', 'Rp 100.000,00', 'Rp 0,00'], 'Cek Oli' => ['Rp 100.000,00', 'Rp 70.000,00', 'Rp 30.000,00']] as $remark => $amounts) {
                $row = $xpath->query('.//tbody/tr[td[2]/div[text()="'.$remark.'"]]', $table)->item(0);
                $this->assertNotNull($row);
                $this->assertSame($amounts, array_map(fn ($cell) => trim($cell->textContent), iterator_to_array($xpath->query('./td[position()>=4]', $row))));
            }
            $this->assertSame($first->photo_url, $xpath->query('.//tbody/tr/td[3]//a', $table)->item(0)->getAttribute('href'));
            $this->assertSame(['Rp 200.000,00', 'Rp 170.000,00', 'Rp 30.000,00'],
                array_map(fn ($cell) => trim($cell->textContent), iterator_to_array($xpath->query('.//tfoot//td', $table))));

            // Revisions show current costs as pending; selected history retains its saved amounts and evidence.
            $first->update(['nominal' => '110000', 'photo_path' => 'receipts/replaced.png']);
            [$xpath, $table] = $this->combinedCostTable($this->get($url)->assertOk()->assertSee('belum dicek ulang'));
            $this->assertSame(['Rp 210.000,00', '—', '—'], array_map(fn ($cell) => trim($cell->textContent), iterator_to_array($xpath->query('.//tfoot//td', $table))));
            $historyUrl = route('transaction-recap.show', ['jenis' => $type, 'source' => $source->id, 'hasil' => $check->id]);
            [$xpath, $table] = $this->combinedCostTable($this->get($historyUrl)->assertOk());
            $this->assertCount(0, $xpath->query('.//tbody//a', $table));
            $this->actingAs(User::findOrFail(2));
            [$xpath, $table] = $this->combinedCostTable($this->get($historyUrl)->assertOk());
            $savedPhoto = $check->details()->where('source_detail_id', $first->id)->firstOrFail();
            $this->assertSame(route('transaction-detail-photo.history', $savedPhoto), $xpath->query('.//tbody//a', $table)->item(0)->getAttribute('href'));
            $this->assertSame('Rp 200.000,00', trim($xpath->query('.//tfoot//td', $table)->item(0)->textContent));
        }
    }

    public function test_combined_cost_table_supports_event_photos_zero_approval_and_legacy_grouped_results(): void
    {
        foreach (['EDK', 'ELK'] as $type) {
            $source = $this->recapEvent($type, '200000');
            $source->update(['foto_event_1' => 'events/test.png']);
            $this->recapConfirm($source, $type);
            $this->recapDecision($source, $type, 'REJECTED', '0');
            $this->actingAs(User::findOrFail(1));
            [$xpath, $table] = $this->combinedCostTable($this->get(route('transaction-recap.show', [$type, $source->id]))->assertOk());
            $this->assertCount(1, $xpath->query('.//tbody/tr', $table));
            $this->assertSame(asset('storage/events/test.png'), $xpath->query('.//tbody/tr/td[3]//a', $table)->item(0)->getAttribute('href'));
            $this->assertSame(['Rp 200.000,00', 'Rp 0,00', 'Rp 200.000,00'], array_map(fn ($cell) => trim($cell->textContent), iterator_to_array($xpath->query('.//tfoot//td', $table))));
        }
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '100000');
        $source->details()->create(['cost_category_id' => 1, 'remark' => 'Cek Oli', 'nominal' => '100000']);
        $check = $this->recapDecision($source, 'OPR', 'APPROVED', '85000');
        $check->details()->delete();
        $check->details()->create(['cost_category_id' => 1, 'cost_category_name' => 'Transport', 'nominal_asal' => '200000', 'nominal_disetujui' => '170000']);
        [$xpath, $table] = $this->combinedCostTable($this->get(route('transaction-recap.show', ['OPR', $source->id]))->assertOk()->assertSee('gabungan per kategori'));
        $this->assertCount(1, $xpath->query('.//tbody/tr', $table));
        $this->assertSame(['Rp 200.000,00', 'Rp 170.000,00', 'Rp 30.000,00'], array_map(fn ($cell) => trim($cell->textContent), iterator_to_array($xpath->query('.//tfoot//td', $table))));
    }

    private function workspacePayload($response, string $action = 'draft'): array
    {
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $fields = [];
        foreach ((new \DOMXPath($document))->query('//form[@id="bs-check-form"]//*[@name]') as $input) {
            if ($input->tagName === 'button') continue;
            $value = $input->tagName === 'textarea' ? $input->textContent : $input->getAttribute('value');
            $fields[] = urlencode($input->getAttribute('name')).'='.urlencode($value);
        }
        parse_str(implode('&', $fields), $payload);

        return $payload + ['save_action' => $action];
    }

    public function test_transaction_workspace_keeps_draft_confirmation_and_recheck_in_one_place_for_all_modules(): void
    {
        $bs = $this->bs();
        foreach (['OPR', 'PD', 'EDK', 'ELK'] as $type) {
            if (in_array($type, ['OPR', 'PD'])) {
                $source = $this->expense($bs, status: 'VALIDATED', amount: '100000', travel: $type === 'PD');
                $source->details()->first()->update(['remark' => 'Bensin Mobil']);
                $source->details()->create(['cost_category_id' => 1, 'remark' => 'Cek Oli', 'nominal' => '100000']);
            } else {
                $source = $this->recapEvent($type, '200000');
                $this->recapConfirm($source, $type);
            }
            $this->actingAs(User::findOrFail(2));
            $params = ['jenis' => $type, 'source' => $source->id];
            $detailUrl = route('transaction-recap.show', $params);
            $reviewUrl = route('transaction-recap.show', $params + ['pemeriksaan' => 1]);
            $storeUrl = route('bs-usage-checks.store', $params);
            $this->get($detailUrl)->assertOk()->assertSee('Detail Transaksi')->assertSee('Rincian &amp; Hasil', false)
                ->assertSee($reviewUrl, false)->assertDontSee('id="bs-check-form"', false);
            $form = $this->get($reviewUrl)->assertOk()->assertSee('id="bs-check-form"', false);
            $payload = $this->workspacePayload($form);
            $this->assertSame('1', $payload['_workspace']);
            $keys = array_keys($payload['details']);
            $payload['details'][$keys[0]]['nominal_disetujui'] = count($keys) === 2 ? '100000' : '170000';
            if (count($keys) === 2) $payload['details'][$keys[1]]['nominal_disetujui'] = '70000';
            $payload['details'][$keys[0]]['catatan'] = 'Catatan internal pengujian';
            $payload['catatan_keputusan'] = 'Catatan untuk pemohon';
            $this->post($storeUrl, $payload)->assertSessionHasNoErrors()->assertRedirect($reviewUrl);
            $this->assertSame(0, $source->financeChecks()->count());
            $draftForm = $this->get($reviewUrl)->assertOk()->assertSee('Lanjutkan draft pemeriksaan');
            $confirmPayload = $this->workspacePayload($draftForm, 'confirm');
            $this->assertEquals(170000, array_sum(array_column($confirmPayload['details'], 'nominal_disetujui')));
            $this->post($storeUrl, $confirmPayload)->assertSessionHasNoErrors()->assertRedirect($detailUrl.'#hasil-finance');
            $first = $source->financeChecks()->latest('id')->firstOrFail();
            $this->assertEquals(170000, $first->details->sum('nominal_disetujui'));
            $this->assertSame(17000000, app(FinanceBsExpenseService::class)->approvedAmount($source->fresh()));
            $this->get($detailUrl)->assertOk()->assertSee('170.000,00')->assertSee('30.000,00')
                ->assertSee($first->document_no)->assertSee('Catatan internal pengujian')->assertSee('Cek Ulang');

            $recheck = $this->workspacePayload($this->get($reviewUrl)->assertOk(), 'confirm');
            $this->assertEquals(170000, array_sum(array_column($recheck['details'], 'nominal_disetujui')));
            $recheck['details'][$keys[0]]['nominal_disetujui'] = '90000';
            $this->post($storeUrl, $recheck)->assertSessionHasNoErrors()->assertRedirect($detailUrl.'#hasil-finance');
            $this->assertSame(2, $source->financeChecks()->count());
            $this->assertEquals(170000, $first->fresh()->details->sum('nominal_disetujui'));
            $this->assertDatabaseMissing('bs_usage_check_drafts', ['jenis_transaksi' => $type, 'transaksi_pemakaian_id' => $source->id]);
        }
    }

    public function test_transaction_workspace_protects_internal_notes_forms_and_other_transactions(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '200000');
        $check = $this->recapDecision($source, 'OPR', 'APPROVED', '170000', '<b>Catatan publik</b>');
        $params = ['jenis' => 'OPR', 'source' => $source->id];
        $reviewUrl = route('transaction-recap.show', $params + ['pemeriksaan' => 1]);
        $draft = $this->workspacePayload($this->get($reviewUrl)->assertOk());
        $draft['catatan_keputusan'] = 'Draft belum boleh terlihat';
        $this->post(route('bs-usage-checks.store', $params), $draft)->assertSessionHasNoErrors();

        $this->actingAs(User::findOrFail(1));
        $this->get(route('transaction-recap.show', $params))->assertOk()->assertSee('170.000,00')
            ->assertSee('&lt;b&gt;Catatan publik&lt;/b&gt;', false)->assertDontSee('<b>Catatan publik</b>', false)
            ->assertDontSee('Catatan internal Finance')->assertDontSee('Draft belum boleh terlihat')
            ->assertDontSee('name="review_token"', false)->assertDontSee('Pemeriksaan Finance</a>', false);
        $this->get($reviewUrl)->assertForbidden();
        $this->post(route('bs-usage-checks.store', $params), $draft)->assertForbidden();
        $this->get(route('transaction-recap.show', $params + ['hasil' => $check->id]))->assertOk()->assertDontSee('Catatan internal Finance');
        $this->actingAs(User::findOrFail(3));
        $this->get(route('transaction-recap.show', $params))->assertForbidden();
        $this->get($reviewUrl)->assertForbidden();
    }

    public function test_transaction_workspace_scopes_history_and_marks_old_results(): void
    {
        $bs = $this->bs();
        $source = $this->expense($bs, status: 'VALIDATED', amount: '200000');
        $first = $this->recapDecision($source, 'OPR', 'APPROVED', '170000');
        $last = $this->recapDecision($source, 'OPR', 'APPROVED', '160000');
        $other = $this->expense($bs, status: 'VALIDATED', amount: '100000');
        $foreign = $this->recapDecision($other, 'OPR', 'APPROVED', '90000');
        $params = ['jenis' => 'OPR', 'source' => $source->id];
        $this->get(route('transaction-recap.show', $params + ['hasil' => $first->id]))->assertOk()
            ->assertSee('Rincian biaya dan hasil pemeriksaan sebelumnya')->assertSee('Hasil terbaru')
            ->assertSee('170.000,00')->assertViewHas('selectedDecision', fn ($value) => $value->id === $first->id)
            ->assertViewHas('financeResult', fn ($value) => $value->check_id === $last->id);
        $this->get(route('transaction-recap.show', $params + ['hasil' => $foreign->id]))->assertNotFound();
        $source->details()->update(['nominal' => '210000']);
        $this->get(route('transaction-recap.show', $params))->assertOk()
            ->assertSee('hasil pada versi pengajuan saat diperiksa')
            ->assertViewHas('financeResult', fn ($value) => $value->finance_status === 'PENDING');
    }

    public function test_transaction_workspace_keeps_closed_or_unconfirmed_sources_read_only(): void
    {
        $source = $this->expense($this->bs(), amount: '100000');
        $this->actingAs(User::findOrFail(2));
        $params = ['jenis' => 'OPR', 'source' => $source->id];
        $reviewUrl = route('transaction-recap.show', $params + ['pemeriksaan' => 1]);
        foreach (['DRAFT', 'PENDING_OWNER', 'REJECTED_OWNER'] as $status) {
            $source->update(['status' => $status]);
            $this->get($reviewUrl)->assertOk()->assertSee('Menunggu konfirmasi pengajuan')
                ->assertDontSee('id="bs-check-form"', false)->assertViewHas('canReview', false);
        }
        $source->update(['status' => 'VALIDATED']);
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $this->get($reviewUrl)->assertOk()->assertSee('Periode transaksi sudah ditutup')
            ->assertDontSee('id="bs-check-form"', false)->assertViewHas('canReview', false);
        $this->get(route('transaction-recap.show', $params))->assertOk()->assertSee('100.000,00');
    }

    public function test_transaction_audit_groups_items_by_check_in_saved_order_and_preserves_archived_history(): void
    {
        $bs = $this->bs();
        $source = $this->expense($bs, status: 'VALIDATED', amount: '100000');
        $source->details()->first()->update(['remark' => 'Bensin Mobil']);
        $source->details()->create(['cost_category_id' => 1, 'remark' => 'Cek Oli', 'nominal' => '100000']);
        $first = $this->recapDecision($source, 'OPR', 'APPROVED', '85000', '<b>Catatan pemeriksaan awal</b>');
        $this->travel(2)->minutes();
        $second = $this->recapDecision($source, 'OPR', 'APPROVED', '90000', 'Catatan cek ulang');
        // A backdated business date must not move a later saved decision before the first check.
        $second->update(['tanggal_pengecekan' => '2026-09-16']);
        $otherModule = $this->expense($bs, status: 'VALIDATED', amount: '100000', travel: true);
        $foreign = $this->recapDecision($otherModule, 'PD', 'APPROVED', '80000', 'HANYA TRANSAKSI LAIN');
        $this->assertSame($source->id, $otherModule->id);

        foreach ([route('transaction-recap.show', ['OPR', $source->id]), route('bs-usage-checks.show', $second)] as $url) {
            $response = $this->get($url)->assertOk()->assertSee('Jejak Audit (Audit Trail)')
                ->assertSee('Cek Ulang Finance')->assertSee('Atasan / Finance')
                ->assertSee(\App\Support\AppTime::format($first->created_at))
                ->assertSee('&lt;b&gt;Catatan pemeriksaan awal&lt;/b&gt;', false)
                ->assertDontSee('<b>Catatan pemeriksaan awal</b>', false)->assertDontSee('HANYA TRANSAKSI LAIN');
            $document = new \DOMDocument();
            @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
            $xpath = new \DOMXPath($document);
            $entries = $xpath->query('//li[@data-audit-check]');
            $this->assertCount(2, $entries);
            $this->assertSame((string) $first->id, $entries->item(0)->getAttribute('data-audit-check'));
            $this->assertSame((string) $second->id, $entries->item(1)->getAttribute('data-audit-check'));
            foreach ($entries as $entry) {
                $this->assertStringContainsString('Bensin Mobil', $entry->textContent);
                $this->assertStringContainsString('Cek Oli', $entry->textContent);
                $this->assertCount(2, $xpath->query('.//details//tbody/tr', $entry));
            }
            $this->assertStringContainsString('Rp 170.000,00', $entries->item(0)->textContent);
            $this->assertStringContainsString('Rp 180.000,00', $entries->item(1)->textContent);
        }
        $this->actingAs(User::findOrFail(1));
        $this->get(route('transaction-recap.show', ['OPR', $source->id]))->assertOk()
            ->assertSee('Jejak Audit (Audit Trail)')->assertDontSee('Catatan internal Finance');

        $source->delete();
        $this->actingAs(User::findOrFail(2));
        $this->get(route('bs-usage-checks.show', $second))->assertOk()->assertSee('Transaksi sumber sudah tidak tersedia')
            ->assertSee('data-audit-check="'.$first->id.'"', false)->assertSee('data-audit-check="'.$second->id.'"', false)
            ->assertDontSee('data-audit-check="'.$foreign->id.'"', false);
    }

    public function test_transaction_audit_pagination_keeps_all_checks_in_chronological_order(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '100000');
        $checks = [];
        for ($i = 0; $i < 12; $i++) {
            $checks[] = $this->recapDecision($source, 'OPR', 'APPROVED', (string) (80000 + $i));
        }
        $params = ['jenis' => 'OPR', 'source' => $source->id, 'pemakaian_periode' => '', 'pemakaian_page' => 2];
        $this->get(route('transaction-recap.show', $params))->assertOk()
            ->assertViewHas('financeHistory', fn ($history) => $history->total() === 12 && $history->count() === 10 && $history->first()->id === $checks[0]->id);
        $secondPage = $this->get(route('transaction-recap.show', $params + ['riwayat_page' => 2]))->assertOk()
            ->assertViewHas('financeHistory', fn ($history) => $history->count() === 2 && $history->first()->id === $checks[10]->id && $history->last()->id === $checks[11]->id);
        $this->assertStringContainsString('pemakaian_page=2', $secondPage->viewData('financeHistory')->url(1));
        $this->assertStringEndsWith('#decision-history-title', $secondPage->viewData('financeHistory')->url(1));
    }

    public function test_transaction_workspace_preserves_filter_context_and_rejects_stale_review_tabs(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '200000');
        $this->actingAs(User::findOrFail(2));
        $context = ['pemakaian_periode' => '', 'pemakaian_status' => 'PENDING', 'pemakaian_referensi' => $source->transaksi_id, 'pemakaian_page' => 2, 'dari' => 'laporan'];
        $params = ['jenis' => 'OPR', 'source' => $source->id] + $context;
        $reviewUrl = route('transaction-recap.show', $params + ['pemeriksaan' => 1]);
        $storeUrl = route('bs-usage-checks.store', ['OPR', $source->id]);
        $form = $this->get($reviewUrl)->assertOk();
        $payload = $this->workspacePayload($form);
        $this->assertEquals($context, $payload['workspace_context']);
        $payload['details'][array_key_first($payload['details'])]['nominal_disetujui'] = '170000.50';
        $this->post($storeUrl, $payload)->assertSessionHasNoErrors()->assertRedirect($reviewUrl);
        $this->postJson($storeUrl, $payload)->assertUnprocessable()->assertJsonValidationErrors('review_token');
        $this->assertSame('170000.50', BsUsageCheckDraft::firstOrFail()->details[0]['nominal_disetujui']);
        $confirm = $this->workspacePayload($this->get($reviewUrl)->assertOk(), 'confirm');
        $this->post($storeUrl, $confirm)->assertSessionHasNoErrors()->assertRedirect(route('transaction-recap.show', $params).'#hasil-finance');
        $this->assertSame(17000050, Money::minor(BsUsageCheck::firstOrFail()->details->sum('nominal_disetujui')));
        foreach (['pemakaian_referensi' => ['invalid'], 'pemakaian_page' => 0, 'dari' => 'https://example.com'] as $key => $value) {
            $this->getJson(route('transaction-recap.show', array_replace($params, [$key => $value, 'pemeriksaan' => 1])))
                ->assertUnprocessable()->assertJsonValidationErrors($key);
        }
    }

    public function test_transaction_lists_have_one_detail_action_per_source(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '200000');
        $this->recapDecision($source, 'OPR', 'APPROVED', '170000');
        foreach ([route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_periode' => '']), route('bs-usage-checks.index')] as $url) {
            $response = $this->get($url)->assertOk();
            $document = new \DOMDocument();
            @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
            $links = (new \DOMXPath($document))->query('//div[@class="recap-actions"]/a');
            $this->assertCount(1, $links);
            $this->assertSame('Detail Transaksi', trim($links->item(0)->textContent));
            $this->assertStringStartsWith(route('transaction-recap.show', ['OPR', $source->id]), $links->item(0)->getAttribute('href'));
        }
    }
}
