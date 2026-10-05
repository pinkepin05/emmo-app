<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOperasionalHeader extends Model
{
    use HasFactory, \App\Models\Concerns\UsesBsBalance;

    protected $table = 'transaksi_operasional_area_sales_header';
    public $timestamps = false;

    protected $casts = ['validated_at' => 'datetime'];

    // 🎯 PERBAIKAN 3: Tambahkan 'bs_id' agar bisa di-save via create() / update()
    protected $fillable = [
        'validated_at', 'validated_by', 'bs_owner_id',
        'transaksi_id',
        'tanggal',
        'user_id',
        'bs_id', 
        'periode',
        'description',
        'status', // 🎯 TAMBAHKAN INI
    ];

    public function details()
    {
        return $this->hasMany(TransaksiOperasionalDetail::class, 'transaksi_id', 'transaksi_id');
    }

    public function financeChecks()
    {
        return $this->hasMany(BsUsageCheck::class, 'transaksi_pemakaian_id')->where('jenis_transaksi', 'OPR');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bonSementara() {
        return $this->belongsTo(PengajuanBs::class, 'bs_id');
    }
}
