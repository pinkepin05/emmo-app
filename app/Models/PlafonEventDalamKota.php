<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlafonEventDalamKota extends Model
{
    protected $table = 'setting_plafon_event_marketing_dalam_kota';
    public $timestamps = false;
    
    protected $fillable = [
        'tanggal_berlaku', 
        'level_id', 
        'kelompok_hari_kerja_id', 
        'hari_event', 
        'jam_kerja_event_id', 
        'description', 
        'default_value'
    ];

    // Relasi ke tabel Level
    public function level() { 
        return $this->belongsTo(Level::class, 'level_id'); 
    }

    // Relasi ke tabel Weekday Group
    public function weekdayGroup() { 
        return $this->belongsTo(WeekdayGroup::class, 'kelompok_hari_kerja_id'); 
    }

    // Relasi ke tabel Event Hour Group
    public function eventHourGroup() { 
        return $this->belongsTo(EventHourGroup::class, 'jam_kerja_event_id'); 
    }
}