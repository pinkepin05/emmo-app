<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WilayahDistance extends Model
{
    use HasFactory;

    protected $table = 'wilayah_distances';

    protected $fillable = [
        'from_wilayah_id',
        'to_wilayah_id',
        'distance_km',
    ];

    public function fromWilayah()
    {
        return $this->belongsTo(Wilayah::class, 'from_wilayah_id');
    }

    public function toWilayah()
    {
        return $this->belongsTo(Wilayah::class, 'to_wilayah_id');
    }
}