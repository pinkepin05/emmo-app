<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BsCashFlowSnapshot extends Model
{
    protected $table = 'bs_cash_flow_snapshots';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'saldo_awal' => 'decimal:2', 'total_masuk' => 'decimal:2',
        'total_keluar' => 'decimal:2', 'saldo_akhir' => 'decimal:2',
        'penyesuaian' => 'decimal:2', 'opening_final' => 'boolean',
        'last_updated' => 'datetime', 'finance_confirmed' => 'boolean',
    ];
}
