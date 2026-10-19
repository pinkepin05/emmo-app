<?php

namespace Tests\Concerns;

use App\Models\Periode;
use App\Models\User;
use App\Services\StockCashFlowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait DashboardPeriodScenarios
{
    public function test_reported_dashboard_links_only_list_confirmed_transactions_and_keep_filters(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $bs = $this->bs();
        $privateBs = $this->bs(['user_id' => 3]);
        foreach (['OPR' => 'transaksi_operasional', 'PD' => 'transaksi_perjalanan_dinas',
            'EDK' => 'perongkosan_event_dalam_kota', 'ELK' => 'perongkosan_event_luar_kota'] as $type => $route) {
            $event = in_array($type, ['EDK', 'ELK'], true);
            $make = function (string $status, string $amount, bool $private = false) use ($type, $event, $bs, $privateBs) {
                if (!$event) return $this->expense($private ? $privateBs : $bs, $status, $amount, $type === 'PD');
                $source = $this->recapEvent($type, $amount, $private ? 3 : 1);
                $source->forceFill(['status' => $status])->save();
                return $source;
            };
            $confirmed = $make('VALIDATED', '200000');
            $draft = $make('DRAFT', '20000');
            $private = $make('VALIDATED', '900000', true);
            $old = $make('VALIDATED', '50000');
            $old->forceFill(['periode' => '2026-08', 'tanggal' => '2026-08-01'])->save();
            if (!$event) {
                $make('PENDING_OWNER', '30000');
                $make('REJECTED_OWNER', '40000');
            }

            $dashboard = $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '2026-09']))->assertOk();
            $module = $dashboard->viewData('bsUsageDashboard')['modules'][$type];
            $this->assertEquals(1, $module->confirmed_count);
            $this->assertEquals(200000, $module->nominal_user);
            $document = new \DOMDocument();
            @$document->loadHTML($dashboard->getContent());
            $xpath = new \DOMXPath($document);
            $url = $xpath->query('//a[@data-usage-reported and @data-usage-history="'.$type.'"]')->item(0)->getAttribute('href');
            $dataKey = $event ? 'events' : 'transaksis';
            $list = $this->get($url)->assertOk()->assertSee($confirmed->transaksi_id)
                ->assertDontSee($draft->transaksi_id)->assertDontSee($private->transaksi_id)->assertDontSee($old->transaksi_id)
                ->assertSee('Rp 200.000,00')->assertSee('Sudah dikonfirmasi user')
                ->assertViewHas($dataKey, fn ($rows) => $rows->total() === 1 && $rows->first()->id === $confirmed->id);
            @$document->loadHTML($list->getContent());
            $xpath = new \DOMXPath($document);
            $this->assertCount(1, $xpath->query('//form[select[@name="periode"]]/input[@name="dilaporkan" and @value="1"]'));
            $this->get(route($route.'.index', ['periode' => '2026-09']))->assertOk()->assertSee($draft->transaksi_id);

            \Maatwebsite\Excel\Facades\Excel::fake();
            $this->get(route($route.'.export', ['periode' => '2026-09', 'dilaporkan' => 1]))->assertOk();
            $exportName = match ($type) {
                'OPR' => 'operasional-sales', 'PD' => 'perjalanan-dinas',
                'EDK' => 'event-dalam-kota', 'ELK' => 'event-luar-kota',
            };
            \Maatwebsite\Excel\Facades\Excel::assertDownloaded('laporan-'.$exportName.'-2026-09.xlsx', function ($export) use ($confirmed) {
                $this->assertSame([$confirmed->transaksi_id, 'TOTAL'], array_column($export->array(), 0));
                $nominalColumn = array_search('Nominal', $export->headings(), true);
                $this->assertEquals(200000, $export->array()[0][$nominalColumn]);
                return true;
            });

            for ($i = 0; $i < 10; $i++) $make('VALIDATED', '1000');
            $page = $this->get($url.'&page=2')->assertOk()->assertDontSee($draft->transaksi_id)
                ->assertViewHas($dataKey, fn ($rows) => $rows->total() === 11 && $rows->count() === 1
                    && $rows->every(fn ($row) => $row->status === 'VALIDATED'));
            parse_str(parse_url($page->viewData($dataKey)->url(1), PHP_URL_QUERY), $query);
            $this->assertSame('1', $query['dilaporkan']);
            $this->assertSame('2026-09', $query['periode']);
            $this->get(route($route.'.index', ['periode' => '2026-08', 'dilaporkan' => 1]))->assertOk()
                ->assertViewHas($dataKey, fn ($rows) => $rows->total() === 1 && $rows->first()->id === $old->id);
        }
    }

    public function test_native_transaction_lists_filter_period_and_keep_it_during_pagination(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $currentBs = $this->bs();
        $oldBs = $this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-01']);
        $privateBs = $this->bs(['user_id' => 3]);
        $routes = ['OPR' => 'transaksi_operasional.index', 'PD' => 'transaksi_perjalanan_dinas.index',
            'EDK' => 'perongkosan_event_dalam_kota.index', 'ELK' => 'perongkosan_event_luar_kota.index'];
        foreach ($routes as $type => $route) {
            $event = in_array($type, ['EDK', 'ELK'], true);
            for ($i = 0; $i < 11; $i++) {
                if ($event) $this->recapEvent($type);
                else $this->expense($currentBs, amount: '10', travel: $type === 'PD');
            }
            if ($event) {
                $old = $this->recapEvent($type);
                $old->forceFill(['periode' => '2026-08', 'tanggal' => '2026-08-01'])->save();
                $private = $this->recapEvent($type, owner: 3);
            } else {
                $old = $this->expense($oldBs, amount: '10', travel: $type === 'PD');
                $private = $this->expense($privateBs, amount: '10', travel: $type === 'PD');
            }
            $dataKey = $event ? 'events' : 'transaksis';
            $page = $this->get(route($route, ['periode' => '2026-09', 'page' => 2]))->assertOk()
                ->assertDontSee($old->transaksi_id)->assertDontSee($private->transaksi_id)
                ->assertViewHas($dataKey, fn ($rows) => $rows->total() === 11 && $rows->count() === 1
                    && $rows->every(fn ($row) => $row->periode === '2026-09' && $row->user_id == 1));
            $this->assertStringContainsString('periode=2026-09', $page->viewData($dataKey)->url(1));
            $this->get(route($route, ['periode' => '2026-08']))->assertOk()
                ->assertViewHas($dataKey, fn ($rows) => $rows->total() === 1 && $rows->first()->id === $old->id);
            $this->get(route($route, ['periode' => '']))->assertOk()
                ->assertViewHas($dataKey, fn ($rows) => $rows->total() === 12);
            if ($type !== 'OPR') $this->getJson(route($route, ['periode' => '2026-13']))->assertUnprocessable();
        }
    }

    public function test_global_dashboard_period_changes_cash_funding_usage_and_finance_together(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        foreach ([['2026-08', '500000', '200000', '170000'], ['2026-09', '300000', '100000', '80000']] as [$period, $funding, $usage, $approved]) {
            $bs = $this->bs(['periode' => $period, 'tanggal' => $period.'-01', 'nominal_pengajuan' => $funding, 'nominal_approved' => $funding]);
            $source = $this->expense($bs, 'VALIDATED', $usage, true);
            $this->recapDecision($source, 'PD', 'APPROVED', $approved);
        }
        $this->bs(['user_id' => 3, 'nominal_pengajuan' => '900000', 'nominal_approved' => '900000']);
        $this->actingAs(User::findOrFail(1));

        foreach ([['202608', '2026-08', '500000.00', 200000, 170000], ['2026-09', '2026-09', '300000.00', 100000, 80000], ['', '2026-09', '300000.00', 100000, 80000]] as [$query, $period, $funding, $usage, $approved]) {
            $page = $this->get(route('dashboard.transaksi', ['pemakaian_periode' => $query]))->assertOk();
            $this->assertSame($period, $page->viewData('selectedPeriod'));
            $data = $page->viewData('bsUsageDashboard');
            $this->assertSame($funding, $data['funding']['diajukan']['amount']);
            $this->assertSame($funding, $page->viewData('cashSnapshot')->total_masuk);
            $this->assertEquals($usage, $data['modules']['PD']->nominal_user);
            $this->assertEquals($approved, $data['modules']['PD']->nominal_finance);
            $this->assertEquals($approved, $page->viewData('cashSnapshot')->total_keluar);

            $dom = new \DOMDocument();
            @$dom->loadHTML($page->getContent());
            $xpath = new \DOMXPath($dom);
            $this->assertCount(1, $xpath->query('//input[@name="pemakaian_periode"]'));
            $this->assertCount(0, $xpath->query('//input[@name="pemakaian_referensi"]'));
            foreach ($xpath->query('//*[@data-funding-stage] | //*[@data-usage-history]') as $link) {
                parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY) ?? '', $params);
                $this->assertSame($period, $params['pemakaian_periode'] ?? $params['periode']);
            }
            $cashLink = $xpath->query('//a[contains(@class,"dashboard-saldo-section__link")]')->item(0)->getAttribute('href');
            $this->assertSame(route('cash-flow.index', ['semua_periode' => 1]), $cashLink);
            $cashFlow = $this->get($cashLink)->assertOk()->assertViewIs('cash-flow.index')
                ->assertViewHas('showAllPeriods', true)
                ->assertViewHas('snapshots', fn ($rows) => $rows->pluck('periode')->all() === ['2026-09', '2026-08']
                    && $rows->every(fn ($row) => $row->user_id == 1) && $rows->sum('total_masuk') == 800000)
                ->assertSee(route('cash-flow.export', ['semua_periode' => 1]), false);
            foreach (['202608', '202609'] as $cashPeriod) {
                $periodUrl = route('cash-flow.show', ['periode' => $cashPeriod, 'semua_periode' => 1]);
                $cashFlow->assertSee(htmlspecialchars($periodUrl, ENT_QUOTES, 'UTF-8'), false);
                $this->get($periodUrl)->assertOk()->assertViewIs('cash-flow.show')
                    ->assertSee('href="'.$cashLink.'"', false);
            }
        }
    }

    public function test_global_dashboard_all_periods_counts_opening_once_and_preserves_closed_snapshots(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $old = $this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-01', 'nominal_approved' => '500000.25']);
        $this->recapDecision($this->expense($old, 'VALIDATED', '100000.10'), 'OPR', 'APPROVED', '100000.10');
        Periode::where('periode', '2026-08')->update(['status' => 'CLOSED']);
        $closed = app(StockCashFlowService::class)->recalculateSnapshot(1, '2026-08');
        $before = $closed->fresh()->getAttributes();

        $current = $this->bs(['nominal_approved' => '200000.25', 'status' => 'SETTLED', 'settled_periode' => '2026-09', 'nominal_pengembalian' => '10000.05']);
        $this->recapDecision($this->expense($current, 'VALIDATED', '70000.10', true), 'PD', 'APPROVED', '70000.10');
        $this->actingAs(User::findOrFail(1));
        // A later source edit must not rewrite a published closing snapshot.
        DB::table('approval_bs_finance')->where('pengajuan_bs_id', $old->id)->update(['nominal_approved' => '999999']);

        $monthly = $this->get(route('dashboard.transaksi'))->assertOk()->viewData('cashSnapshot');
        $all = $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '']))->assertOk()->viewData('cashSnapshot');
        $this->assertSame('400000.15', $monthly->saldo_awal);
        $this->assertSame('0.00', $all->saldo_awal);
        $this->assertSame('700000.50', $all->total_masuk);
        $this->assertSame('170000.20', $all->total_keluar);
        $this->assertSame('-10000.05', $all->penyesuaian);
        $this->assertSame('520000.25', $all->saldo_berjalan);
        $this->assertSame($monthly->saldo_berjalan, $all->saldo_berjalan);
        $this->assertSame($before, $closed->fresh()->getAttributes());
    }

    public function test_global_dashboard_keeps_request_period_and_actual_cash_receipt_period_distinct(): void
    {
        $old = $this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-31', 'nominal_pengajuan' => '500000', 'nominal_approved' => '300000']);
        DB::table('approval_bs_finance')->where('pengajuan_bs_id', $old->id)->update(['tanggal_cair' => '2026-09-01', 'periode' => '2026-09']);
        $august = $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '2026-08']))->assertOk();
        $this->assertSame('300000.00', $august->viewData('bsUsageDashboard')['funding']['dicairkan']['amount']);
        $this->assertSame('0.00', $august->viewData('cashSnapshot')->total_masuk);
        $september = $this->get(route('dashboard.transaksi'))->assertOk();
        $this->assertSame('0.00', $september->viewData('bsUsageDashboard')['funding']['dicairkan']['amount']);
        $this->assertSame('300000.00', $september->viewData('cashSnapshot')->total_masuk);
    }

    public function test_global_dashboard_all_periods_keeps_initial_balance_after_empty_periods(): void
    {
        Periode::create(['periode' => '2026-06', 'status' => 'CLOSED']);
        Periode::create(['periode' => '2026-07', 'status' => 'CLOSED']);
        \App\Models\StockCashFlowSnapshot::create([
            'user_id' => 1, 'periode' => '2026-07', 'saldo_awal' => '1000.50',
            'total_masuk' => '0.00', 'total_keluar' => '200.00', 'penyesuaian' => '0.00',
            'saldo_akhir' => '800.50', 'opening_final' => true,
        ]);
        $this->bs(['nominal_approved' => '50000']);
        $all = $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '']))->assertOk()->viewData('cashSnapshot');
        $this->assertSame('1000.50', $all->saldo_awal);
        $this->assertSame('50800.50', $all->saldo_berjalan);
    }

    public function test_global_dashboard_default_follows_local_calendar_and_handles_empty_and_invalid_periods(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);
        Periode::create(['periode' => '2026-11', 'status' => 'OPEN']);
        $this->travelTo(Carbon::parse('2026-09-30 16:59:00', 'UTC'));
        $this->get(route('dashboard.transaksi'))->assertOk()->assertViewHas('selectedPeriod', '2026-09');
        $this->travelTo(Carbon::parse('2026-09-30 17:00:00', 'UTC'));
        $this->get(route('dashboard.transaksi'))->assertOk()->assertViewHas('selectedPeriod', '2026-10');
        $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '2026-08']))->assertOk()->assertViewHas('selectedPeriod', '2026-08');
        $all = $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '']))->assertOk()->assertViewHas('selectedPeriod', '');
        $this->assertSame('0.00', $all->viewData('cashSnapshot')->saldo_berjalan);
        $this->assertSame('0.00', $all->viewData('bsUsageDashboard')['funding']['diajukan']['amount']);
        $this->getJson(route('dashboard.transaksi', ['pemakaian_periode' => '2026-13']))->assertUnprocessable();
    }

    public function test_global_dashboard_all_periods_finds_uncached_receipts_and_keeps_cash_private(): void
    {
        $this->bs(['periode' => '2026-10', 'tanggal' => '2026-10-01', 'nominal_pengajuan' => '50000', 'nominal_approved' => '50000']);
        $this->bs(['user_id' => 3, 'nominal_pengajuan' => '900000', 'nominal_approved' => '900000']);
        $this->assertDatabaseMissing('periode', ['periode' => '2026-10']);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
        $this->get(route('dashboard.transaksi'))->assertOk()->assertViewHas('cashSnapshot', fn ($row) => $row->total_masuk === '0.00');
        $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '', 'user_id' => 3]))->assertOk()
            ->assertViewHas('cashSnapshot', fn ($row) => $row->total_masuk === '0.00');
        $this->get(route('cash-flow.index', ['semua_periode' => 1]))->assertOk()->assertDontSee('202610')
            ->assertViewHas('snapshots', fn ($rows) => $rows->pluck('periode')->all() === ['2026-09']);
        $this->assertDatabaseMissing('stock_cash_flow_snapshots', ['user_id' => 1, 'periode' => '2026-10']);
        $this->travelTo(Carbon::parse('2026-10-01 10:00:00', 'Asia/Jakarta'));
        $this->get(route('cash-flow.index', ['semua_periode' => 1]))->assertOk()->assertSee('202610')
            ->assertViewHas('snapshots', fn ($rows) => $rows->pluck('periode')->all() === ['2026-10', '2026-09']
                && $rows->every(fn ($row) => $row->user_id == 1) && $rows->sum('total_masuk') == 50000);
    }

    public function test_global_dashboard_without_cash_permission_reads_all_balances_without_creating_reports(): void
    {
        $this->bs(['nominal_approved' => '50000']);
        $user = User::findOrFail(1);
        $user->revokePermissionTo('akses.cash-flow');
        $this->actingAs($user);
        foreach ([[], ['pemakaian_periode' => '']] as $filter) {
            $this->get(route('dashboard.transaksi', $filter))->assertOk()->assertDontSee('class="dashboard-saldo-section__link"', false)
                ->assertViewHas('cashSnapshot', fn ($row) => $row->saldo_berjalan === '50000.00');
        }
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
    }
}
