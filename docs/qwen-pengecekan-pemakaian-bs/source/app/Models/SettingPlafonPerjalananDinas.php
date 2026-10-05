<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingPlafonPerjalananDinas extends Model
{
   protected $table = 'setting_plafon_perjalanan_dinas';
public $timestamps = false;
    
    // Kolom yang boleh diisi massal (memudahkan kita di Controller)
    protected $fillable = [
        'provinsi_awal_id', 
        'provinsi_tujuan_id', 
        'level_id', 
        'description', 
        'default_value', 
        'tanggal_berlaku'
    ];

    // Relasi ke tabel Wilayah (sebagai provinsi awal)
    public function wilayahAwal()
    {
        return $this->belongsTo(Wilayah::class, 'provinsi_awal_id');
    }

    // Relasi ke tabel Wilayah (sebagai provinsi tujuan)
    public function wilayahTujuan()
    {
        return $this->belongsTo(Wilayah::class, 'provinsi_tujuan_id');
    }

    // Relasi ke tabel Level
    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }
}