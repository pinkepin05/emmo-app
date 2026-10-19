<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PeriodClosingLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['excluded_transactions' => 'array', 'created_at' => 'datetime'];

    /** Historical closures remain in the audit trail after a period is reopened. */
    public static function activeExclusions(string $period): Collection
    {
        if (!Periode::where('periode', $period)->where('status', 'CLOSED')->exists()) return collect();

        return collect(static::where('periode', $period)->latest('id')->first()?->excluded_transactions ?? []);
    }
}
