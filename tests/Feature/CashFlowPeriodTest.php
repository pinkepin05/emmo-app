<?php

namespace Tests\Feature;

use App\Exports\CashFlowExport;
use App\Http\Controllers\CashFlowController;
use App\Models\Periode;
use App\Models\StockCashFlowSnapshot;
use App\Services\StockCashFlowService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CashFlowPeriodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['app.display_timezone' => 'Asia/Jakarta']);
        $this->travelTo(Carbon::parse('2026-10-09 03:00:00', 'UTC'));

        Schema::create('periode', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->unique();
            $table->string('status');
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_18_110000_create_stock_cash_flow_snapshots_table.php'))->up();
        (require database_path('migrations/2026_09_23_110000_add_personal_funds_to_stock_cash_flow.php'))->up();
        (require database_path('migrations/2026_10_09_025352_add_operasional_and_perjalanan_dinas_columns_to_stock_cash_flow_snapshots_table.php'))->up();
        Schema::table('stock_cash_flow_snapshots', fn (Blueprint $table) => $table->boolean('finance_confirmed')->default(false));

        foreach (['2026-09', '2026-10', '2026-11'] as $period) {
            Periode::create(['periode' => $period, 'status' => 'OPEN']);
        }
        // December also has a restored snapshot without a corresponding period row.
        foreach (['2026-09', '2026-10', '2026-11', '2026-12'] as $period) {
            StockCashFlowSnapshot::create(['user_id' => 7, 'periode' => $period]);
        }

        // Call the controller directly; authentication is covered by the access suite.
        Auth::shouldReceive('id')->andReturn(7);
        Auth::shouldReceive('guard')->andReturnSelf();
        Auth::shouldReceive('check')->andReturnFalse();
    }

    public static function cashFlowScopes(): array
    {
        return ['default' => [false], 'all periods from dashboard' => [true]];
    }

    private function expectCalculations(array $periods, bool $showAllPeriods): void
    {
        request()->query->set('semua_periode', $showAllPeriods ? '1' : '0');
        $service = $this->mock(StockCashFlowService::class);
        if ($showAllPeriods) {
            $service->shouldReceive('dashboardPeriods')->once()->with(7)
                ->andReturn(collect(['2026-09', '2026-10', '2026-11', '2026-12']));
        }
        foreach ($periods as $period) {
            $service->shouldReceive('recalculateSnapshot')->once()->with(7, $period)
                ->andReturn(StockCashFlowSnapshot::where('user_id', 7)->where('periode', $period)->firstOrFail());
        }
    }

    #[DataProvider('cashFlowScopes')]
    public function test_index_excludes_future_periods_without_recalculating_or_deleting_them(bool $showAllPeriods): void
    {
        $before = StockCashFlowSnapshot::where('periode', '>', '2026-10')->get()->toArray();
        $this->expectCalculations(['2026-09', '2026-10'], $showAllPeriods);

        $view = app(CashFlowController::class)->index();

        $this->assertSame('2026-10', $view->getData()['currentPeriod']);
        $this->assertSame('2026-10', $view->getData()['currentSnapshot']->periode);
        $this->assertSame(['2026-10', '2026-09'], $view->getData()['snapshots']->pluck('periode')->all());
        $html = $view->render();
        $this->assertStringContainsString('Oktober 2026', $html);
        $this->assertStringContainsString('202609', $html);
        $this->assertStringNotContainsString('202611', $html);
        $this->assertStringNotContainsString('202612', $html);
        $this->assertSame($before, StockCashFlowSnapshot::where('periode', '>', '2026-10')->get()->toArray());
    }

    #[DataProvider('cashFlowScopes')]
    public function test_export_includes_only_the_current_month_and_past_periods(bool $showAllPeriods): void
    {
        $this->expectCalculations(['2026-09', '2026-10'], $showAllPeriods);
        Excel::fake();

        app(CashFlowController::class)->export();

        Excel::assertDownloaded('stok-kas-semua-periode.xlsx', function (CashFlowExport $export) {
            $this->assertSame(['202610', '202609'], array_column($export->sheets()[0]->array(), 0));
            return true;
        });
    }

    #[DataProvider('cashFlowScopes')]
    public function test_november_becomes_current_only_when_local_time_reaches_november(bool $showAllPeriods): void
    {
        $this->travelTo(Carbon::parse('2026-10-31 17:00:00', 'UTC'));
        $this->expectCalculations(['2026-09', '2026-10', '2026-11'], $showAllPeriods);

        $view = app(CashFlowController::class)->index();

        $this->assertSame('2026-11', $view->getData()['currentPeriod']);
        $this->assertSame('2026-11', $view->getData()['currentSnapshot']->periode);
        $this->assertSame(['2026-11', '2026-10', '2026-09'], $view->getData()['snapshots']->pluck('periode')->all());
        $this->assertStringContainsString('November 2026', $view->render());
        $this->assertStringNotContainsString('202612', $view->render());
    }

    #[DataProvider('cashFlowScopes')]
    public function test_history_never_renders_future_snapshots_even_if_provided(bool $showAllPeriods): void
    {
        $snapshots = StockCashFlowSnapshot::where('user_id', 7)->orderByDesc('periode')->get();
        $html = view('cash-flow.index', [
            'showAllPeriods' => $showAllPeriods,
            'snapshots' => $snapshots,
            'currentPeriod' => '2026-10',
            'currentSnapshot' => $snapshots->firstWhere('periode', '2026-10'),
        ])->render();

        $this->assertStringContainsString('202609', $html);
        $this->assertStringNotContainsString('202611', $html);
        $this->assertStringNotContainsString('202612', $html);
    }
}
