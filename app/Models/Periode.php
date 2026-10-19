<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $fillable = ['periode', 'status', 'closed_at', 'closed_by', 'reopened_at', 'reopen_reason'];

    protected $casts = [
        'closed_at'   => 'datetime',
        'reopened_at' => 'datetime',
    ];
}
