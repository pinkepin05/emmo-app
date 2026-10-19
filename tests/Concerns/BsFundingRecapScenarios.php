<?php

namespace Tests\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\DB;

trait BsFundingRecapScenarios
{
    public function test_grouped_finance_link_shows_all_amounts_and_opens_all_statuses_in_selected_period(): void
    {
        $bs = $this->bs();
        $approved = $this->expense($bs, 'VALIDATED', '200000', true);
        $this->recapDecision($approved, 'PD', 'APPROVED', '170000');
        $pending = $this->expense($bs, 'VALIDATED', '250000', true);
        $rejected = $this->expense($bs, 'VALIDATED', '50000', true);
        $this->recapDecision($rejected, 'PD', 'REJECTED', '0');
        $draft = $this->expense($bs, 'DRAFT', '80000', true);
        $private = $this->expense($this->bs(['user_id' => 3]), 'VALIDATED', '90000', true);
        $old = $this->expense($this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-01']), 'VALIDATED', '10000', true);
        $this->actingAs(User::findOrFail(1));
        $page = $this->get(route('dashboard.transaksi', [
            'pemakaian_periode' => '2026-09', 'pemakaian_status' => 'REJECTED', 'pemakaian_referensi' => 'NO-MATCH',
        ]))->assertOk();
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$page->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertCount(8, $xpath->query('//*[@data-usage-recap]//a'));
        foreach ($xpath->query('//*[@data-usage-row]') as $row) {
            $this->assertCount(2, $xpath->query('.//a', $row));
            $this->assertCount(3, $xpath->query('.//*[@data-finance-status]', $row));
        }
        $link = $xpath->query('//a[@data-usage-finance and @data-usage-history="PD"]')->item(0);
        foreach (['APPROVED' => ['Rp 170.000,00', '1 transaksi'], 'PENDING' => ['Rp 250.000,00', '1 transaksi'], 'REJECTED' => ['Rp 80.000,00', '2 transaksi']] as $status => $values) {
            $cell = $xpath->query('.//*[@data-finance-status="'.$status.'"]', $link)->item(0);
            foreach ($values as $value) $this->assertStringContainsString($value, $cell->textContent);
        }
        parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
        $this->assertSame(['pemakaian_periode' => '2026-09'], $query);
        $history = $this->get($link->getAttribute('href'))->assertOk()
            ->assertSee('Daftar Pengecekan Finance — Perjalanan Dinas')
            ->assertDontSee($draft->transaksi_id)->assertDontSee($private->transaksi_id)->assertDontSee($old->transaksi_id);
        $this->assertEqualsCanonicalizing([$approved->id, $pending->id, $rejected->id], $history->viewData('rows')->pluck('source_id')->all());
        $this->assertEqualsCanonicalizing(['APPROVED', 'PENDING', 'REJECTED'], $history->viewData('rows')->pluck('finance_status')->all());
        $reported = $xpath->query('//a[@data-usage-reported and @data-usage-history="PD"]')->item(0);
        $this->assertSame('/transaksi-perjalanan-dinas', parse_url($reported->getAttribute('href'), PHP_URL_PATH));
        $this->get($reported->getAttribute('href'))->assertOk()->assertSee($draft->transaksi_id);
    }

    public function test_bs_dashboard_combines_usage_and_finance_with_real_values(): void
    {
        $bs = $this->bs(['nominal_pengajuan' => '250000', 'nominal_approved' => '250000']);
        $this->bs(['nominal_pengajuan' => '500000', 'status' => 'PENDING_ATASAN'], false);
        $this->bs(['nominal_pengajuan' => '999999', 'nominal_approved' => '999999', 'user_id' => 3]);
        $source = $this->expense($bs, status: 'VALIDATED', amount: '200000', travel: true);
        $this->recapDecision($source, 'PD', 'APPROVED', '170000');
        $this->actingAs(User::findOrFail(1));
        $page = $this->get(route('dashboard.transaksi'))->assertOk()
            ->assertSeeInOrder(['id="funding-recap-title"', 'id="usage-recap-title"'], false)
            ->assertSee('Rekap Pengajuan &amp; Pencairan BS', false)->assertSee('Rekap Pemakaian &amp; Konfirmasi BS', false)
            ->assertDontSee('id="finance-recap-title"', false)
            ->assertDontSee('name="pemakaian_referensi"', false)->assertDontSee('name="pemakaian_status"', false);
        $data = $page->viewData('bsUsageDashboard');
        $this->assertSame(['amount' => '750000.00', 'count' => 2], $data['funding']['diajukan']);
        $this->assertSame(['amount' => '250000.00', 'count' => 1], $data['funding']['dicairkan']);
        $this->assertEquals(200000, $data['modules']['PD']->nominal_user);
        $this->assertEquals(170000, $data['modules']['PD']->nominal_finance);
        $this->assertEquals(30000, $data['modules']['PD']->selisih);
        $this->assertEquals(30000, $data['summary']->nominal_not_approved);
        $this->assertEquals(1, $data['summary']->not_approved_count);
        // A bookmarked dashboard URL must not silently narrow the period totals.
        $legacy = $this->get(route('dashboard.transaksi', [
            'pemakaian_periode' => $data['filters']['pemakaian_periode'],
            'pemakaian_referensi' => 'NO-MATCH', 'pemakaian_status' => 'REJECTED', 'pemakaian_page' => 2,
        ]))->assertOk()->viewData('bsUsageDashboard');
        $this->assertSame($data['funding'], $legacy['funding']);
        $this->assertEquals($data['summary'], $legacy['summary']);
        $this->assertSame(['pemakaian_periode' => $data['filters']['pemakaian_periode']], $legacy['filters']);
        $dom = new \DOMDocument();
        @$dom->loadHTML($page->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertCount(4, $xpath->query('//*[@data-funding-stage]'));
        $this->assertCount(4, $xpath->query('//*[@data-usage-stage]'));
        $this->assertCount(0, $xpath->query('//*[@data-usage-stage="DIFFERENCE"]'));
        $this->assertCount(0, $xpath->query('//*[@data-module-card]'));
        foreach ($xpath->query('//*[@data-funding-stage] | //*[@data-usage-history]') as $link) {
            $this->get($link->getAttribute('href'))->assertOk();
        }
        $this->get(route('transaction-recap.funding', ['tahap' => 'diajukan']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 2);
        $back = route('dashboard.transaksi', ['pemakaian_periode' => $data['filters']['pemakaian_periode']]).'#pemakaian-bs';
        $this->get(route('transaction-recap.funding', ['tahap' => 'diajukan', 'pemakaian_referensi' => $bs->document_no]))->assertOk()
            ->assertSee('name="pemakaian_referensi"', false)->assertSee('href="'.$back.'"', false)
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $bs->id);
        $this->get(route('transaction-recap.module', ['jenis' => 'PD', 'pemakaian_referensi' => $source->transaksi_id]))->assertOk()
            ->assertSee('name="pemakaian_referensi"', false)->assertSee('href="'.$back.'"', false)
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
        $this->get(route('transaction-recap.module', ['jenis' => 'PD', 'pemakaian_referensi' => 'NO-MATCH']))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
        $this->get(route('transaction-recap.funding', ['tahap' => 'dicairkan']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
        $this->actingAs(User::findOrFail(2));
        $all = $this->get(route('dashboard.transaksi'))->assertOk()->viewData('bsUsageDashboard');
        $this->assertSame('1749999.00', $all['funding']['diajukan']['amount']);
        $this->assertSame('1249999.00', $all['funding']['dicairkan']['amount']);
    }

    public function test_bs_funding_recap_filters_payment_dates_and_keeps_cross_period_requests_in_history(): void
    {
        $old = $this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-31', 'nominal_pengajuan' => '125.75', 'nominal_approved' => '100.25']);
        DB::table('approval_bs_finance')->where('pengajuan_bs_id', $old->id)->update(['tanggal_cair' => '2026-09-01', 'periode' => '2026-09']);
        $current = $this->bs(['nominal_pengajuan' => '60.50', 'nominal_approved' => '40.25']);
        $this->bs(['periode' => '2026-10', 'tanggal' => '2026-10-01', 'nominal_pengajuan' => '900', 'nominal_approved' => '900']);
        $september = $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '202609']))->assertOk()->viewData('bsUsageDashboard');
        $this->assertSame('60.50', $september['funding']['diajukan']['amount']);
        $this->assertSame('140.50', $september['funding']['dicairkan']['amount']);
        $this->assertSame(2, $september['funding']['dicairkan']['count']);
        $all = $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '']))->assertOk()->viewData('bsUsageDashboard');
        $this->assertSame('1086.25', $all['funding']['diajukan']['amount']);
        $this->assertSame('1040.50', $all['funding']['dicairkan']['amount']);
        $history = $this->get(route('transaction-recap.funding', ['tahap' => 'dicairkan', 'pemakaian_periode' => '202609', 'pemakaian_referensi' => $old->document_no]))->assertOk()
            ->assertSee($old->document_no)->assertDontSee($current->document_no);
        $this->assertEquals('100.25', $history->viewData('total'));
        $this->assertSame(1, $history->viewData('rows')->total());
        $this->get(route('transaction-recap.funding', ['tahap' => 'dicairkan', 'pemakaian_periode' => '', 'pemakaian_referensi' => 'CAIR-'.$old->id]))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
    }

    public function test_bs_funding_recap_empty_states_privacy_pagination_and_invalid_filters(): void
    {
        $this->get(route('dashboard.transaksi'))->assertOk()->assertViewHas('bsUsageDashboard', fn ($data) => $data['funding']['diajukan']['amount'] === '0.00' && $data['funding']['dicairkan']['amount'] === '0.00');
        $private = $this->bs(['user_id' => 3, 'nominal_pengajuan' => '77777', 'nominal_approved' => '77777']);
        for ($i = 0; $i < 16; $i++) $this->bs(['nominal_pengajuan' => '10.25'], false);
        $page = $this->get(route('transaction-recap.funding', ['tahap' => 'diajukan', 'pemakaian_periode' => '2026-09', 'page' => 2, 'user_id' => 3]))->assertOk()->assertDontSee($private->document_no);
        $this->assertSame(16, $page->viewData('rows')->total());
        $this->assertSame(1, $page->viewData('rows')->count());
        $this->assertEquals('164.00', $page->viewData('total'));
        $this->get(route('transaction-recap.funding', ['tahap' => 'dicairkan', 'pemakaian_referensi' => $private->document_no]))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->isEmpty());
        $this->get(route('transaction-recap.funding', ['tahap' => '']))->assertOk();
        $this->getJson(route('transaction-recap.funding', ['tahap' => 'invalid']))->assertUnprocessable();
        $this->getJson(route('transaction-recap.funding', ['pemakaian_periode' => '2026-13']))->assertUnprocessable();
        auth()->logout();
        $this->get(route('transaction-recap.funding'))->assertRedirect(route('login'));
    }
}
