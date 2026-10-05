<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOperasionalDetail extends Model
{
    //

    use HasFactory;

    protected $table = 'transaksi_operasional_area_sales_detail';

    public $timestamps = false;

    protected $fillable = [
        'transaksi_id',
        'cost_category_id',
        'remark',
        'nominal'
    ];


    // Relasi ke Header

    public function header()
    {
        return $this->belongsTo(TransaksiOperasionalHeader::class, 'transaksi_id', 'transaksi_id');
    }

    public function costCategory()
    {
        return $this->belongsTo(CostCategory::class, 'cost_category_id', 'id');
    }


}
