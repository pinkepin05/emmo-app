<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
