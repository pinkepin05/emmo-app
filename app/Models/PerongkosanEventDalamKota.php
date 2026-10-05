<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerongkosanEventDalamKota extends Model
{
    use HasFactory;

    protected $table = 'perongkosan_event_dalam_kota';
    public $timestamps = false; 

    public function financeChecks()
    {
        return $this->hasMany(BsUsageCheck::class, 'transaksi_pemakaian_id')->where('jenis_transaksi', 'EDK');
    }

    protected $fillable = [
        'transaksi_id',
        'tanggal',
        'is_libur',
        'jam_kerja_event_id',
        'user_id',
        'description',
        'nominal',
        'periode',
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

    public function jamKerja()
    {
        return $this->belongsTo(EventHourGroup::class, 'jam_kerja_event_id', 'id');
    }

    // public function index() {
    // $events = PerongkosanEventDalamKota::with(['user', 'jamKerja'])->orderBy('tanggal', 'desc')->paginate(10);
    // return view('perongkosan_event_dalam_kota.index', compact('events'));

}
