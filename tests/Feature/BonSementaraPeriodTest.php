<?php

namespace Tests\Feature;

use App\Models\Periode;
use App\Services\BonSementaraService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BonSementaraPeriodTest extends TestCase
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
    }

    public function test_future_open_period_does_not_replace_the_current_calendar_month(): void
    {
        foreach (['2026-09', '2026-10', '2026-11'] as $period) {
            Periode::create(['periode' => $period, 'status' => 'OPEN']);
        }
        $before = Periode::orderBy('periode')->get()->toArray();

        $this->assertSame('2026-10', app(BonSementaraService::class)->getActivePeriode());
        $this->assertSame($before, Periode::orderBy('periode')->get()->toArray());
    }

    public function test_missing_current_month_is_created_once_despite_a_future_open_period(): void
    {
        Periode::create(['periode' => '2026-11', 'status' => 'OPEN']);

        $service = app(BonSementaraService::class);
        $this->assertSame('2026-10', $service->getActivePeriode());
        $this->assertSame('2026-10', $service->getActivePeriode());
        $this->assertDatabaseHas('periode', ['periode' => '2026-10', 'status' => 'OPEN']);
        $this->assertDatabaseHas('periode', ['periode' => '2026-11', 'status' => 'OPEN']);
        $this->assertDatabaseCount('periode', 2);
    }

    public function test_closed_current_month_is_selected_without_reopening_it(): void
    {
        Periode::create(['periode' => '2026-10', 'status' => 'CLOSED']);
        Periode::create(['periode' => '2026-11', 'status' => 'OPEN']);
        $before = Periode::orderBy('periode')->get()->toArray();

        $this->assertSame('2026-10', app(BonSementaraService::class)->getActivePeriode());
        $this->assertSame($before, Periode::orderBy('periode')->get()->toArray());
    }

    public function test_period_rolls_over_at_local_midnight_including_new_year(): void
    {
        $this->travelTo(Carbon::parse('2026-12-31 17:05:06', 'UTC'));
        Periode::create(['periode' => '2027-02', 'status' => 'OPEN']);

        $this->assertSame('2027-01', app(BonSementaraService::class)->getActivePeriode());
        $this->assertDatabaseHas('periode', ['periode' => '2027-01', 'status' => 'OPEN']);
        $this->assertDatabaseMissing('periode', ['periode' => '2026-12']);
    }
}
