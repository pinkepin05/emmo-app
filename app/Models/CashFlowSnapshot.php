<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashFlowSnapshot extends Model
{
    protected $table = 'cash_flow_snapshots';
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'periode',
        'saldo_awal',
        'total_masuk',
        'total_keluar',
        'saldo_akhir',
        'last_updated',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'total_masuk' => 'decimal:2',
        'total_keluar' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}