<?php

namespace Tests\Feature;

use App\Services\PeriodService;
use App\Support\AppTime;
use Carbon\Carbon;
use Tests\TestCase;

class ApplicationTimeTest extends TestCase
{
    public function test_utc_timestamps_display_in_wib_without_mutating_the_original(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);
        $original = Carbon::parse('2026-09-17 01:39:30', 'UTC');

        $this->assertSame('17 Sep 2026, 08:39:30 WIB', AppTime::format($original));
        $this->assertSame('01:39:30', $original->format('H:i:s'));
        $this->assertSame('17 Sep 2026, 08:45:38 WIB', AppTime::format('2026-09-17 01:45:38'));
        $this->assertSame('-', AppTime::format(null));
        $this->assertSame('-', AppTime::format(''));
    }

    public function test_business_calendar_rolls_over_at_local_midnight_including_new_year(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);
        $this->travelTo(Carbon::parse('2026-12-31 17:05:06', 'UTC'));

        $this->assertSame('2027-01-01', AppTime::now()->toDateString());
        $this->assertSame('2027-01', app(PeriodService::class)->current());
        $this->assertSame('01 Jan 2027, 00:05:06 WIB', AppTime::format(now()));
        $this->assertSame('2026-12-31 17:05:06', now()->format('Y-m-d H:i:s'));
    }

    public function test_configured_timezone_and_explicit_offsets_are_respected(): void
    {
        config(['app.display_timezone' => 'Asia/Makassar']);
        $this->assertSame('17 Sep 2026, 09:39:30 WITA', AppTime::format('2026-09-17T08:39:30+07:00'));
        config(['app.display_timezone' => 'Asia/Jayapura']);
        $this->assertSame('17 Sep 2026, 10:39:30 WIT', AppTime::format('2026-09-17 01:39:30'));
    }

    public function test_live_header_clock_starts_from_application_time_without_a_server_time_label(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);
        $this->travelTo(Carbon::parse('2026-09-21 01:23:15', 'UTC'));

        $html = view('partials.application-time', ['mode' => 'inline'])->render();

        $this->assertStringContainsString('September - 21 - 2026, 8:23:15 am', $html);
        $this->assertStringContainsString('data-application-live-time', $html);
        $this->assertStringContainsString('data-time-zone="Asia/Jakarta"', $html);
        $this->assertStringNotContainsString('Server Time', $html);
    }
}
