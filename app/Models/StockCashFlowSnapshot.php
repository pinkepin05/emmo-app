<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCashFlowSnapshot extends Model
{
    protected $table = 'stock_cash_flow_snapshots';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'saldo_awal' => 'decimal:2', 'total_masuk' => 'decimal:2',
        'total_keluar_bs' => 'decimal:2', 'total_event_dalam' => 'decimal:2',
        'total_event_luar' => 'decimal:2', 'total_keluar' => 'decimal:2',
        'penyesuaian' => 'decimal:2', 'saldo_akhir' => 'decimal:2',
        'total_keluar_pribadi' => 'decimal:2', 'total_masuk_reimbursement' => 'decimal:2',
        'includes_personal_funds' => 'boolean',
        'opening_final' => 'boolean', 'last_updated' => 'datetime',
    ];
}
