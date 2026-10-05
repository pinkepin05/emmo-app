<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $guarded = ['id'];

    protected $casts = [
        'closed_at'   => 'datetime',
        'reopened_at' => 'datetime',
    ];
}