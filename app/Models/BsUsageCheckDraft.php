<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BsUsageCheckDraft extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['tanggal_pengecekan' => 'date', 'details' => 'array', 'version' => 'integer'];

    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'jenis_transaksi', 'transaksi_pemakaian_id');
    }

    public function revisionOf()
    {
        return $this->belongsTo(BsUsageCheck::class, 'revision_of_id');
    }
}
