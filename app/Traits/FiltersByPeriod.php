<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByPeriod
{
    /**
     * Filter query berdasarkan periode (YYYY-MM) dari kolom tanggal.
     * Jika tabel Anda sudah punya kolom `periode` (sesuai dok rev4),
     * ganti isi whereRaw dengan: $query->where('periode', $periode);
     */
    protected function filterPeriod(Builder $query, ?string $periode, string $column = 'tanggal'): Builder
    {
        if ($periode && preg_match('/^\d{4}-\d{2}$/', $periode)) {
            $query->whereRaw("DATE_FORMAT({$column}, '%Y-%m') = ?", [$periode]);
        }

        return $query;
    }
}