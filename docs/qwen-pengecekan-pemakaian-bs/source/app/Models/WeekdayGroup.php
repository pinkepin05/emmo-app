<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekdayGroup extends Model
{
    protected $table = 'weekday_group';
    public $timestamps = false;
    protected $fillable = ['code', 'description', 'is_active'];
}