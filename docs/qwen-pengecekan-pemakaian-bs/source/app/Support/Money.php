<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function minor(int|float|string|null $amount): int
    {
        $value = is_float($amount) ? number_format($amount, 2, '.', '') : (string) ($amount ?? '0');
        if (!preg_match('/^(-?)(\d{1,13})(?:\.(\d{1,2}))?$/D', $value, $parts)) {
            throw new InvalidArgumentException('Nominal harus berupa angka dengan maksimal dua angka desimal.');
        }
        $minor = ((int) $parts[2] * 100) + (int) str_pad($parts[3] ?? '', 2, '0');
        return ($parts[1] === '-' ? -1 : 1) * $minor;
    }

    public static function decimal(int $minor): string
    {
        return ($minor < 0 ? '-' : '').intdiv(abs($minor), 100).'.'.str_pad((string) (abs($minor) % 100), 2, '0', STR_PAD_LEFT);
    }
}
