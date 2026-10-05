<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

final class AppTime
{
    public static function timezone(): string
    {
        return config('app.display_timezone', 'Asia/Jakarta');
    }

    /** Local calendar time for business dates, periods, and document numbers. */
    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::timezone());
    }

    /** Stored timestamps use the application's UTC clock, including legacy rows. */
    public static function format(DateTimeInterface|string|null $value, string $format = 'd M Y, H:i:s'): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $local = CarbonImmutable::parse($value, config('app.timezone'))->setTimezone(self::timezone());
        $label = match (self::timezone()) {
            'Asia/Jakarta', 'Asia/Pontianak' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => $local->format('T'),
        };

        return $local->format($format).' '.$label;
    }
}
