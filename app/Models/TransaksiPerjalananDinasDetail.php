<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 🎯 TAMBAHKAN

class TransaksiPerjalananDinasDetail extends Model
{
    use HasFactory, SoftDeletes; // 🎯 TAMBAHKAN SoftDeletes

    protected $table = 'transaksi_perjalanan_dinas_detail';
    public $timestamps = false;
    
    // 🎯 PENTING: Beritahu Laravel hanya pakai deleted_at, jangan cari updated_at
    const DELETED_AT = 'deleted_at'; 

    protected $fillable = [
        'transaksi_id',
        'cost_category_id',
        'remark',
        'nominal'
    ];

    public function header()
    {
        return $this->belongsTo(TransaksiPerjalananDinasHeader::class, 'transaksi_id', 'transaksi_id');
    }

    public function costCategory()
    {
        return $this->belongsTo(CostCategory::class, 'cost_category_id', 'id');
    }
}