<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerongkosanEventLuarKota extends Model
{
    use HasFactory;

    protected $table = 'perongkosan_event_luar_kota';
    public $timestamps = false; 

    protected $fillable = [
    'transaksi_id',
    'tanggal',
    'lokasi_event_id', 
    'jarak_km',       // ✅ Tetap ada (untuk backward compatibility)
    'durasi_jam',     // ✅ Tetap ada
    'is_libur',
    'jam_kerja_event_id',
    'user_id',
    'description',
    'nominal',
    'periode',        // ✅ Tetap ada
    'is_over_plafon', 
    'plafon_value', 
    'selisih_plafon',
    'foto_event_1',
    'foto_event_2',
    'foto_event_3'
];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'lokasi_event_id', 'id');
    }

    public function jamKerja()
    {
        return $this->belongsTo(EventHourGroup::class, 'jam_kerja_event_id', 'id');
    }
}