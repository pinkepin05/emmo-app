<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCategory extends Model
{
    //

    protected $table = 'cost_category';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'description',
        'is_active'
    ];
}
