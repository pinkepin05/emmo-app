<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BsUsageCheck extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['tanggal_pengecekan' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'user_checker_id');
    }

    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'jenis_transaksi', 'transaksi_pemakaian_id');
    }

    public function details()
    {
        return $this->hasMany(BsUsageCheckDetail::class);
    }

    public function revisionOf()
    {
        return $this->belongsTo(self::class, 'revision_of_id');
    }

    public function scopeLatestPerSource(Builder $query): Builder
    {
        // Choose the globally latest saved check BEFORE date/status filters.
        return $query->whereIn('bs_usage_checks.id', DB::table('bs_usage_checks')
            ->selectRaw('MAX(id)')->groupBy('jenis_transaksi', 'transaksi_pemakaian_id'));
    }

    public function scopeDifferent(Builder $query): Builder
    {
        return $query->whereHas('details', fn ($detail) => $detail->whereColumn('nominal_asal', '<>', 'nominal_disetujui'));
    }
}
