<?php

namespace Tests\Concerns;

use App\Models\BsUsageCheck;
use App\Models\User;
use App\Services\BsUsageCheckService;
use App\Services\PlafonService;
use Illuminate\Support\Facades\DB;

trait TransactionRecapScenarios
{
    public function test_recap_separates_unchecked_submission_amount_from_approval_difference(): void
    {
        $bs = $this->bs();
        $approved = $this->expense($bs, status: 'VALIDATED', amount: '250000');
        $waiting = [];
        foreach (['200000', '220000', '250000'] as $amount) {
            $waiting[] = $this->expense($bs, status: 'VALIDATED', amount: $amount);
        }
        $this->expense($bs, status: 'DRAFT', amount: '777777');
        $this->expense($bs, status: 'VALIDATED', amount: '999999', attributes: ['user_id' => 3]);
        $this->recapDecision($approved, 'OPR', 'APPROVED', '200000');
        $this->actingAs(User::findOrFail(1));
        $context = ['pemakaian_periode' => ''];
        $dashboard = $this->get(route('dashboard.transaksi', $context))->assertOk()
            ->assertDontSee('Revisi/Pending')->assertDontSee('Revisi / Pending')->assertDontSee('Rp 720.000,00');
        $summary = $dashboard->viewData('bsUsageDashboard')['modules']['OPR'];
        $this->assertEquals(920000, $summary->nominal_user);
        $this->assertEquals(200000, $summary->nominal_finance);
        $this->assertEquals(670000, $summary->nominal_unchecked);
        $this->assertEquals(50000, $summary->selisih);
        $this->assertEquals(3, $summary->unchecked_count);
        $this->assertEquals(1, $summary->different_count);
        $this->assertEquals(0, $summary->nominal_revision);
        $this->assertEquals(0, $summary->nominal_recheck);
        $this->assertEquals($summary->nominal_user, $summary->nominal_finance + $summary->nominal_unchecked + $summary->selisih);
        $dom = new \DOMDocument();
        @$dom->loadHTML($dashboard->getContent());
        $xpath = new \DOMXPath($dom);
        foreach (['PENDING' => ['Rp 670.000,00', 3], 'DIFFERENCE' => ['Rp 50.000,00', 1]] as $status => [$amount, $count]) {
            $link = $xpath->query('(//*[@data-module-card])[1]//*[@data-amount-status="'.$status.'"]')->item(0);
            $this->assertStringContainsString($amount, $link->textContent);
            $this->assertStringContainsString($count.' transaksi', $link->textContent);
            $response = $this->get($link->getAttribute('href'))->assertOk()->assertDontSee('Revisi / Pending');
            $this->assertEquals($count, $response->viewData('rows')->total());
            $this->assertTrue($response->viewData('rows')->every(fn ($row) => $row->finance_status === ($status === 'PENDING' ? 'PENDING' : 'APPROVED')));
            $this->assertEquals($status === 'PENDING' ? 670000 : 0, $response->viewData('summary')->nominal_unchecked);
            $this->assertEquals($status === 'DIFFERENCE' ? 50000 : 0, $response->viewData('summary')->selisih);
        }
        $this->get(route('transaction-recap.show', ['OPR', $approved->id]))->assertOk()
            ->assertSee('Selisih Persetujuan Finance')->assertSee('Rp 50.000,00')->assertDontSee('Revisi / Pending');
        // Revisions and changed submissions must leave the unchecked bucket.
        $this->recapDecision($waiting[0], 'OPR', 'REVISION', '190000');
        $this->recapDecision($waiting[1], 'OPR', 'APPROVED', '210000');
        $waiting[1]->details()->first()->update(['nominal' => 225000]);
        $this->actingAs(User::findOrFail(1));
        $summary = $this->get(route('transaction-recap.module', ['jenis' => 'OPR'] + $context))->assertOk()->viewData('summary');
        $this->assertEquals(925000, $summary->nominal_user);
        $this->assertEquals(250000, $summary->nominal_unchecked);
        $this->assertEquals(200000, $summary->nominal_revision);
        $this->assertEquals(225000, $summary->nominal_recheck);
        $this->assertEquals(200000, $summary->nominal_finance);
        $this->assertEquals(50000, $summary->selisih);
        $this->assertEquals($summary->nominal_user, $summary->nominal_finance + $summary->nominal_rejected
            + $summary->nominal_unchecked + $summary->nominal_revision + $summary->nominal_recheck + $summary->selisih);
    }

    public function test_dashboard_module_cards_keep_scope_filters_and_detail_return_context(): void
    {
        $bs = $this->bs();
        for ($i = 0; $i < 12; $i++) $this->expense($bs, status: 'VALIDATED', amount: '10');
        $private = $this->expense($bs, status: 'VALIDATED', amount: '200', attributes: ['user_id' => 3]);
        $pd = $this->expense($bs, status: 'VALIDATED', amount: '50', travel: true);
        $context = ['pemakaian_periode' => '', 'pemakaian_status' => 'PENDING'];
        $dom = new \DOMDocument();
        $response = $this->get(route('dashboard.transaksi', $context))->assertOk()
            ->assertDontSee($private->transaksi_id)->assertDontSee('data-recap-tab', false)
            ->assertDontSee('transaction-recap-tabs.js')->assertDontSee('class="usage-table"', false);
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertCount(4, $xpath->query('//*[@data-module-card]'));
        $this->assertCount(0, $xpath->query('//table[contains(@class,"usage-table")]'));
        foreach ($xpath->query('//*[@data-module-card]//a') as $link) {
            $this->assertMatchesRegularExpression('~/laporan-transaksi/modul/(OPR|PD|EDK|ELK)$~', parse_url($link->getAttribute('href'), PHP_URL_PATH));
            parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
            $this->assertSame('', $query['pemakaian_periode']);
        }
        $listContext = $context + ['jenis' => 'OPR', 'pemakaian_page' => 2];
        $response = $this->get(route('transaction-recap.module', $listContext + ['pemakaian_jenis' => 'PD', 'user_id' => 3]))->assertOk()
            ->assertSee('Daftar Operasional Sales')->assertDontSee($pd->transaksi_id)->assertDontSee($private->transaksi_id)
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 12 && $rows->count() === 2 && $rows->every(fn ($row) => $row->jenis === 'OPR' && $row->user_id == 1))
            ->assertViewHas('summary', fn ($s) => $s->nominal_user == 120);
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertCount(0, $xpath->query('//*[@data-recap-tab]'));
        $this->assertCount(7, $xpath->query('//table[contains(@class,"usage-table")]/thead/tr/th'));
        $this->assertCount(2, $xpath->query('//td[@data-label="Disetujui Finance"]'));
        $this->assertSame('/laporan-transaksi/modul/OPR', parse_url($xpath->query('//form[contains(@class,"recap-filter")]')->item(0)->getAttribute('action'), PHP_URL_PATH));
        foreach ($xpath->query('//nav[@aria-label="Halaman daftar transaksi"]//a') as $link) {
            $this->assertSame('/laporan-transaksi/modul/OPR', parse_url($link->getAttribute('href'), PHP_URL_PATH));
            parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
            $this->assertSame('', $query['pemakaian_periode']);
            $this->assertSame('PENDING', $query['pemakaian_status']);
            $this->assertArrayNotHasKey('pemakaian_jenis', $query);
        }
        $hub = $xpath->query('//a[contains(@class,"recap-back")]')->item(0)->getAttribute('href');
        $this->assertSame('/dashboard/transaksi', parse_url($hub, PHP_URL_PATH));
        $this->get($hub)->assertOk()->assertViewHas('bsUsageDashboard', fn ($d) => $d['summary']->confirmed_count == 13);
        $detail = $xpath->query('//a[contains(@class,"recap-reference")]')->item(0)->getAttribute('href');
        @$dom->loadHTML($this->get($detail)->assertOk()->getContent());
        $back = (new \DOMXPath($dom))->query('//a[contains(@class,"recap-back")]')->item(0)->getAttribute('href');
        $this->assertSame('/laporan-transaksi/modul/OPR', parse_url($back, PHP_URL_PATH));
        parse_str(parse_url($back, PHP_URL_QUERY), $backQuery);
        $this->assertEquals($context + ['pemakaian_page' => 2], $backQuery);
        $this->get($back)->assertOk()->assertViewHas('rows', fn ($rows) => $rows->currentPage() === 2 && $rows->count() === 2);
        $this->get(route('transaction-recap.module', ['jenis' => 'PD', 'pemakaian_periode' => '', 'pemakaian_referensi' => $pd->transaksi_id]))->assertOk()
            ->assertSee($pd->transaksi_id)->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->jenis === 'PD');
        foreach (['OPR', 'PD', 'EDK', 'ELK'] as $type) {
            $response = $this->get(route('transaction-recap.module', ['jenis' => $type, 'pemakaian_periode' => '2099-01']))->assertOk()
                ->assertViewHas('rows', fn ($rows) => $rows->isEmpty());
            @$dom->loadHTML($response->getContent());
            foreach ((new \DOMXPath($dom))->query('//div[@class="empty-actions"]//a') as $link) {
                $this->assertSame('/laporan-transaksi/modul/'.$type, parse_url($link->getAttribute('href'), PHP_URL_PATH));
            }
        }
        $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '2099-01']))->assertOk()->assertSee('Belum ada pengajuan pada periode ini');
        $this->get('/laporan-transaksi/modul/INVALID')->assertNotFound();
    }

    public function test_dashboard_legacy_links_redirect_to_module_or_cards(): void
    {
        foreach (['ringkasan', 'daftar', 'pengajuan', 'finance'] as $mode) {
            $filters = ['pemakaian_periode' => '', 'pemakaian_status' => 'PENDING', 'pemakaian_page' => 2, 'pemakaian_referensi' => 'TEST'];
            foreach (['dashboard.transaksi', 'transaction-recap.index'] as $route) {
                $location = $this->get(route($route, $filters + ['pemakaian_jenis' => 'OPR', 'tampilan' => $mode]))->assertRedirect()->headers->get('Location');
                $this->assertSame('/laporan-transaksi/modul/OPR', parse_url($location, PHP_URL_PATH));
                parse_str(parse_url($location, PHP_URL_QUERY), $query);
                $this->assertEquals($filters, $query);
            }
            $this->get(route('transaction-recap.index', ['tampilan' => $mode, 'pemakaian_periode' => '']))
                ->assertRedirect(route('dashboard.transaksi', ['pemakaian_periode' => '']).'#pemakaian-bs');
            $this->get(route('dashboard.transaksi', ['tampilan' => $mode]))->assertOk()->assertDontSee('data-recap-tab', false);
        }
        $this->getJson(route('dashboard.transaksi', ['tampilan' => 'invalid']))->assertUnprocessable();
    }

    public function test_recap_combined_detail_shows_latest_public_result_without_private_notes(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '100');
        foreach ([
            ['APPROVED', '80', 20],
            ['REVISION', '90', 100],
            ['REJECTED', '0', 0],
        ] as [$decision, $amount, $pending]) {
            $latest = $this->recapDecision($source, 'OPR', $decision, $amount, '<b>Catatan publik</b>');
            $this->actingAs(User::findOrFail(1));
            $this->get(route('transaction-recap.show', ['OPR', $source->id]))->assertOk()
                ->assertSee('Hasil Finance terakhir')->assertSee($latest->document_no)
                ->assertSee('&lt;b&gt;Catatan publik&lt;/b&gt;', false)->assertDontSee('<b>Catatan publik</b>', false)
                ->assertDontSee('Catatan internal Finance')->assertDontSee('Riwayat Pemeriksaan')
                ->assertViewHas('financeResult', fn ($row) => $row->check_id == $latest->id
                    && $row->finance_status === $decision && $row->nominal_pending == $pending);
        }
        $this->actingAs(User::findOrFail(2));
        $this->get(route('transaction-recap.show', ['OPR', $source->id]))->assertOk()->assertSee('Riwayat Pemeriksaan');
    }

    private function recapEvent(string $type, string $amount = '300', int $owner = 1)
    {
        $model = BsUsageCheckService::TYPES[$type];
        return $model::create(['transaksi_id' => $type.'-TEST-'.($model::count() + 1),
            'tanggal' => '2026-09-16', 'periode' => '2026-09', 'user_id' => $owner,
            'nominal' => $amount, 'description' => 'Kegiatan uji', 'jam_kerja_event_id' => 1, 'is_libur' => 0]);
    }

    private function recapToken($source, string $type): string
    {
        $service = app(BsUsageCheckService::class);
        return $service->token($type, $source->id, $service->snapshot($source->fresh())['fingerprint']);
    }

    private function recapConfirm($source, string $type): void
    {
        $this->actingAs(User::findOrFail($source->user_id));
        $this->post(route('transaction-recap.confirm-event', [$type, $source->id]), ['source_token' => $this->recapToken($source, $type)])
            ->assertSessionHasNoErrors()->assertRedirect();
        $source->refresh();
    }

    private function recapDecision($source, string $type, string $decision, string $amount, ?string $note = 'Catatan untuk user')
    {
        $this->actingAs(User::findOrFail(2));
        $items = app(BsUsageCheckService::class)->snapshot($source->fresh())['items'];
        if ($decision === 'REVISION') {
            return $this->legacyFinanceRevision($source, $type, $amount, $note);
        }
        $this->post(route('bs-usage-checks.store', [$type, $source->id]), [
            'tanggal_pengecekan' => '2026-10-01', 'source_token' => $this->recapToken($source, $type),
            'keputusan' => $decision, 'catatan_keputusan' => $note,
            'details' => $items->map(fn ($item) => ['cost_category_id' => (int) $item['cost_category_id'],
                'nominal_disetujui' => $amount, 'catatan' => 'Catatan internal Finance'])->all(),
        ])->assertSessionHasNoErrors()->assertRedirect();
        return BsUsageCheck::latest('id')->firstOrFail();
    }

    public function test_recap_four_modules_reconcile_submission_approval_rejection_pending_and_difference(): void
    {
        $bs = $this->bs();
        $opr = $this->expense($bs, status: 'VALIDATED', amount: '100');
        $pd = $this->expense($bs, status: 'VALIDATED', amount: '200', travel: true);
        $local = $this->recapEvent('EDK', '300');
        $outside = $this->recapEvent('ELK', '400');
        $draft = $this->recapEvent('EDK', '999');
        $this->recapConfirm($local, 'EDK');
        $this->recapConfirm($outside, 'ELK');
        $this->recapDecision($opr, 'OPR', 'APPROVED', '80');
        $rejection = $this->recapDecision($pd, 'PD', 'REJECTED', '123');
        $revision = $this->recapDecision($local, 'EDK', 'REVISION', '250');
        $this->assertSame('0.00', $rejection->details->first()->nominal_disetujui);
        $this->assertNull($revision->details->first()->cost_category_id);
        $this->assertSame($local::class, $revision->source::class);
        $response = $this->get(route('dashboard.transaksi'))->assertOk()->assertDontSee($draft->transaksi_id);
        $data = $response->viewData('bsUsageDashboard');
        $summary = $data['summary'];
        $this->assertEquals(4, $summary->confirmed_count);
        $this->assertEquals(1000, $summary->nominal_user);
        $this->assertEquals(80, $summary->nominal_finance);
        $this->assertEquals(200, $summary->nominal_rejected);
        $this->assertEquals(720, $summary->nominal_pending);
        $this->assertEquals(700, $summary->nominal_waiting);
        $this->assertEquals(400, $summary->nominal_unchecked);
        $this->assertEquals(300, $summary->nominal_revision);
        $this->assertEquals(0, $summary->nominal_recheck);
        $this->assertEquals(3, $summary->pending_count);
        $this->assertEquals(20, $summary->selisih);
        $this->assertEquals($summary->nominal_user, $summary->nominal_finance + $summary->nominal_rejected + $summary->nominal_pending);
        foreach (['OPR', 'PD', 'EDK', 'ELK'] as $type) $this->assertEquals(1, $data['modules'][$type]->confirmed_count);
        foreach (['OPR' => 20, 'PD' => 0, 'EDK' => 300, 'ELK' => 400] as $type => $amount) {
            $this->assertEquals($amount, $data['modules'][$type]->nominal_pending);
            $this->assertEquals($amount, $data['rows']->firstWhere('jenis', $type)->nominal_pending);
        }
        $this->assertEquals('REVISION', $data['rows']->firstWhere('jenis', 'EDK')->finance_status);
        $this->assertNull($data['rows']->firstWhere('jenis', 'EDK')->selisih);
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'tampilan' => 'finance']))->assertOk()->assertSee('Daftar Operasional Sales')
            ->assertSee('Catatan untuk user')->assertDontSee('Catatan internal Finance');
        foreach (['OPR' => $opr, 'PD' => $pd, 'EDK' => $local, 'ELK' => $outside] as $type => $source) {
            $this->get(route('transaction-recap.show', [$type, $source->id]))->assertOk()->assertSee($source->transaksi_id);
        }
        $this->get(route('dashboard.transaksi', ['pemakaian_status' => 'FOLLOW_UP']))->assertOk()
            ->assertViewHas('bsUsageDashboard', fn ($d) => $d['summary']->nominal_pending == 720 && $d['summary']->nominal_rejected == 0 && $d['summary']->pending_count == 3);
        foreach (['OPR' => $opr, 'PD' => $pd, 'EDK' => $local, 'ELK' => $outside] as $type => $source) {
            $this->get(route('transaction-recap.module', ['jenis' => $type]))->assertOk()->assertSee($source->transaksi_id)
                ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->every(fn ($row) => $row->jenis === $type));
            $this->get(route('transaction-recap.module', ['jenis' => $type, 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()
                ->assertViewHas('rows', fn ($rows) => $rows->total() === ($type === 'PD' ? 0 : 1));
        }
        foreach (['PENDING' => 'ELK', 'REVISION' => 'EDK', 'DIFFERENCE' => 'OPR'] as $filter => $type) {
            $this->get(route('transaction-recap.module', ['jenis' => $type, 'pemakaian_status' => $filter]))->assertOk()
                ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->jenis === $type);
        }
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()
            ->assertViewHas('summary', fn ($s) => $s->nominal_pending == 20 && $s->pending_count == 1);
    }

    public function test_recap_latest_decisions_do_not_resurrect_older_approvals_and_zero_is_not_rejection(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '100');
        $this->recapDecision($source, 'OPR', 'REJECTED', '0');
        $this->recapDecision($source, 'OPR', 'APPROVED', '0');
        $data = $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'tampilan' => 'finance']))->assertOk()->viewData('summary');
        $this->assertEquals(1, $data->checked_count);
        $this->assertEquals(0, $data->rejected_count);
        $this->assertEquals(100, $data->selisih);
        $this->assertEquals(100, $data->nominal_pending);
        $this->assertEquals(1, $data->pending_count);
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_status' => 'REJECTED']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
        $last = $this->recapDecision($source, 'OPR', 'REVISION', '90');
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_status' => 'APPROVED']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_status' => 'REVISION']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->check_id == $last->id);
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()
            ->assertViewHas('summary', fn ($s) => $s->pending_count == 1 && $s->nominal_pending == 100 && $s->selisih == 0);
        $this->assertDatabaseCount('bs_usage_checks', 3);
        $this->assertSame('VALIDATED', $source->fresh()->status);
    }

    public function test_recap_event_confirmation_is_owner_only_stale_safe_and_idempotent(): void
    {
        foreach (['EDK', 'ELK'] as $type) {
            $source = $this->recapEvent($type);
            $this->get(route('transaction-recap.show', [$type, $source->id]))->assertOk()->assertSee('Konfirmasi Pengajuan');
            $token = $this->recapToken($source, $type);
            $this->actingAs(User::findOrFail(2));
            $this->post(route('transaction-recap.confirm-event', [$type, $source->id]), ['source_token' => $token])->assertForbidden();
            $this->actingAs(User::findOrFail(1));
            $source->update(['description' => 'Rincian berubah']);
            $this->post(route('transaction-recap.confirm-event', [$type, $source->id]), ['source_token' => $token])->assertSessionHasErrors('source_token');
            $this->assertSame('DRAFT', $source->fresh()->status);
            $this->recapConfirm($source, $type);
            $time = $source->validated_at;
            $this->post(route('transaction-recap.confirm-event', [$type, $source->id]), ['source_token' => $token])->assertRedirect();
            $this->assertSame('VALIDATED', $source->fresh()->status);
            $this->assertEquals(1, $source->fresh()->submission_version);
            $this->assertEquals(1, $source->fresh()->validated_by);
            $this->assertEquals($time, $source->fresh()->validated_at);
        }
    }

    public function test_recap_event_edit_requires_reconfirmation_and_recheck_even_when_total_is_unchanged(): void
    {
        $this->mock(PlafonService::class, fn ($mock) => $mock->shouldReceive('checkEvent')->andReturn(['is_over' => false, 'plafon_value' => 1000, 'selisih' => 0]));
        foreach (['EDK' => 'perongkosan_event_dalam_kota', 'ELK' => 'perongkosan_event_luar_kota'] as $type => $route) {
            $source = $this->recapEvent($type);
            $this->recapConfirm($source, $type);
            $check = $this->recapDecision($source, $type, 'APPROVED', '280');
            $this->actingAs(User::findOrFail(1));
            $this->put(route($route.'.update', $source->id), [
                'tanggal' => '2026-09-16', 'is_libur' => 0, 'jam_kerja_event_id' => 1, 'description' => 'Bukti diperbaiki',
                'nominal' => 300, 'lokasi_event_id' => 1, 'teritory' => 'Jawa', 'jarak_km' => 1, 'durasi_jam' => 1,
                'status' => 'VALIDATED', 'validated_by' => 2, 'submission_version' => 999,
            ])->assertSessionHasNoErrors()->assertRedirect();
            $source->refresh();
            $this->assertSame('DRAFT', $source->status);
            $this->assertNull($source->validated_at);
            $this->get(route('transaction-recap.module', ['jenis' => $type]))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
            $this->recapConfirm($source, $type);
            $this->assertEquals(3, $source->submission_version);
            $this->get(route('transaction-recap.module', ['jenis' => $type]))->assertOk()->assertSee('Perlu Cek Ulang')
                ->assertViewHas('summary', fn ($s) => $s->checked_count == 0 && $s->nominal_pending == 300 && $s->recheck_count == 1 && $s->revision_count == 0
                    && $s->nominal_recheck == 300 && $s->nominal_unchecked == 0 && $s->nominal_revision == 0);
            $this->get(route('transaction-recap.module', ['jenis' => $type, 'pemakaian_status' => 'REVISION']))->assertOk()
                ->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
            foreach (['RECHECK', 'FOLLOW_UP'] as $filter) {
                $this->get(route('transaction-recap.module', ['jenis' => $type, 'pemakaian_status' => $filter]))->assertOk()
                    ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->nominal_pending == 300);
            }
            $this->get(route('transaction-recap.show', [$type, $source->id]))->assertOk()
                ->assertSee('Dokumen sebelumnya')
                ->assertViewHas('financeResult', fn ($row) => $row->finance_status === 'RECHECK' && $row->nominal_pending == 300 && $row->selisih === null);
            $this->assertSame('280.00', $check->fresh()->details->first()->nominal_disetujui);
            $this->recapDecision($source, $type, 'APPROVED', '290');
            $this->get(route('transaction-recap.module', ['jenis' => $type]))->assertOk()
                ->assertViewHas('summary', fn ($s) => $s->checked_count == 1 && $s->nominal_finance == 290 && $s->selisih == 10 && $s->nominal_pending == 10);
        }
    }

    public function test_recap_reports_and_details_keep_owner_scope_and_escape_public_decision_notes(): void
    {
        $mine = $this->recapEvent('EDK');
        $other = $this->recapEvent('ELK', '900', 3);
        User::findOrFail(3)->givePermissionTo('akses.event-luar-kota');
        $this->recapConfirm($mine, 'EDK');
        $this->recapConfirm($other, 'ELK');
        $check = $this->recapDecision($mine, 'EDK', 'REVISION', '250', '<script>alert(1)</script>');
        $this->actingAs(User::findOrFail(1));
        foreach (['daftar', 'pengajuan', 'finance'] as $mode) {
            $this->get(route('transaction-recap.module', ['jenis' => 'EDK', 'tampilan' => $mode, 'user_id' => 3, 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()
                ->assertSee($mine->transaksi_id)->assertDontSee($other->transaksi_id)
                ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)
                ->assertDontSee('Catatan internal Finance')->assertDontSee('href="'.route('bs-usage-checks.show', $check).'"', false);
        }
        $this->get(route('transaction-recap.show', ['ELK', $other->id]))->assertForbidden();
        $this->post(route('bs-usage-checks.store', ['EDK', $mine->id]), [])->assertForbidden();
        $this->actingAs(User::findOrFail(2));
        $this->get(route('transaction-recap.show', ['ELK', $other->id]))->assertOk();
        $this->get(route('dashboard.transaksi'))->assertOk()->assertViewHas('bsUsageDashboard', fn ($d) => $d['summary']->confirmed_count == 2);
        auth()->logout();
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR']))->assertRedirect(route('login'));
    }

    public function test_recap_finance_rejects_unconfirmed_events_invalid_decisions_and_missing_reasons(): void
    {
        $source = $this->recapEvent('EDK');
        $this->actingAs(User::findOrFail(2));
        $payload = ['tanggal_pengecekan' => '2026-09-16', 'source_token' => $this->recapToken($source, 'EDK'),
            'keputusan' => 'APPROVED', 'details' => [['cost_category_id' => 0, 'nominal_disetujui' => '200']]];
        $url = route('bs-usage-checks.store', ['EDK', $source->id]);
        $this->post($url, $payload)->assertSessionHasErrors('source');
        $this->recapConfirm($source, 'EDK');
        $this->actingAs(User::findOrFail(2));
        $payload['source_token'] = $this->recapToken($source, 'EDK');
        foreach (['REJECTED'] as $decision) {
            $this->post($url, array_replace($payload, ['keputusan' => $decision]))->assertSessionHasErrors('catatan_keputusan');
        }
        $this->post($url, array_replace($payload, ['keputusan' => 'REVISION']))->assertSessionHasErrors('keputusan');
        $payload['details'][0]['cost_category_id'] = 1;
        $this->post($url, $payload)->assertSessionHasErrors('details');
        $this->assertDatabaseCount('bs_usage_checks', 0);
    }

    public function test_recap_event_decisions_leave_financial_tables_and_source_data_unchanged(): void
    {
        $event = $this->recapEvent('ELK');
        $this->recapConfirm($event, 'ELK');
        $before = [];
        foreach (['perongkosan_event_luar_kota', 'pengajuan_bs', 'approval_bs_finance', 'bs_cash_flow_snapshots', 'stock_cash_flow_snapshots', 'bs_usage_logs', 'reimbursement_claims'] as $table) $before[$table] = DB::table($table)->get()->toJson();
        foreach (['REVISION', 'REJECTED', 'APPROVED'] as $decision) $this->recapDecision($event, 'ELK', $decision, '250');
        foreach ($before as $table => $rows) $this->assertSame($rows, DB::table($table)->get()->toJson(), $table);
        $this->assertDatabaseCount('bs_usage_checks', 3);
    }

    public function test_recap_event_amounts_are_per_id_even_when_legacy_references_are_empty(): void
    {
        $one = $this->recapEvent('EDK', '100');
        $two = $this->recapEvent('EDK', '200');
        foreach ([$one, $two] as $event) {
            $event->update(['transaksi_id' => null]);
            $this->recapConfirm($event, 'EDK');
        }
        $this->recapDecision($one, 'EDK', 'APPROVED', '90');
        $this->get(route('transaction-recap.module', ['jenis' => 'EDK']))->assertOk()->assertViewHas('summary', fn ($s) => $s->confirmed_count == 2
            && $s->nominal_user == 300 && $s->nominal_finance == 90 && $s->nominal_pending == 210 && $s->selisih == 10);
    }

    public function test_recap_pending_tracks_partial_approval_until_latest_check_resolves_difference(): void
    {
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '1000000');
        $this->recapDecision($source, 'OPR', 'APPROVED', '800000');
        foreach (['daftar', 'pengajuan', 'finance'] as $mode) {
            $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'tampilan' => $mode, 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()
                ->assertSee('Rp 200.000,00')->assertSee('Disetujui Finance')
                ->assertViewHas('summary', fn ($s) => $s->confirmed_count == 1 && $s->checked_count == 1 && $s->pending_count == 1
                    && $s->nominal_user == 1000000 && $s->nominal_finance == 800000 && $s->nominal_pending == 200000);
        }
        $last = $this->recapDecision($source, 'OPR', 'APPROVED', '1000000');
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 0)
            ->assertViewHas('summary', fn ($s) => $s->pending_count == 0 && $s->nominal_pending == 0);
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR']))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->first()->check_id == $last->id && $rows->first()->nominal_pending == 0);
        $this->assertDatabaseCount('bs_usage_checks', 2);
    }

    public function test_recap_pending_keeps_signed_decimal_differences_and_excludes_rejected_amounts(): void
    {
        $bs = $this->bs();
        $over = $this->expense($bs, status: 'VALIDATED', amount: '100.25');
        $rejected = $this->expense($bs, status: 'VALIDATED', amount: '200', travel: true);
        $waiting = $this->recapEvent('EDK', '50.10');
        $this->recapConfirm($waiting, 'EDK');
        $this->recapDecision($over, 'OPR', 'APPROVED', '120.50');
        $this->recapDecision($rejected, 'PD', 'REJECTED', '0');
        $response = $this->get(route('dashboard.transaksi'))->assertOk();
        $summary = $response->viewData('bsUsageDashboard')['summary'];
        $this->assertEqualsWithDelta(29.85, $summary->nominal_pending, 0.00001);
        $this->assertEqualsWithDelta(50.10, $summary->nominal_waiting, 0.00001);
        $this->assertEqualsWithDelta(-20.25, $summary->selisih, 0.00001);
        $this->assertEqualsWithDelta(50.10, $summary->nominal_unchecked, 0.00001);
        $this->assertEquals(0, $summary->nominal_revision);
        $this->assertEquals(0, $summary->nominal_recheck);
        $this->assertEqualsWithDelta($summary->nominal_user, $summary->nominal_finance + $summary->nominal_rejected + $summary->nominal_pending, 0.00001);
        $this->assertEquals(2, $summary->pending_count);
        $this->assertEquals(0, $response->viewData('bsUsageDashboard')['rows']->firstWhere('jenis', 'PD')->nominal_pending);
        $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()
            ->assertDontSee($rejected->transaksi_id)
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->firstWhere('jenis', 'OPR')->nominal_pending < 0);
    }
}
