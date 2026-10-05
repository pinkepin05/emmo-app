<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    //

    // 1. Nama Tabel
    protected $table = 'wilayah';

    // 2. Matikan Timestamps karena tidak ada
    public $timestamps = false;

    //3. Kolom yang boleh diisi (Mass Assignment)

    protected $fillable = [
        'code',
        'nama',
        'parent_id',
        'teritory',
        'is_active'
    ];

    //4. Logika Baru: Mengambil data parent (Orang Tua)
    // Ini membantu kita menampilkan nama parent di table nanti

    public function parent()
{
    return $this->belongsTo(Wilayah::class, 'parent_id', 'id');
}
}
