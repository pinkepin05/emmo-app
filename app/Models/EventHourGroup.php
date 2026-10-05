<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventHourGroup extends Model
{
    protected $table = 'event_hour_group';
    public $timestamps = false;
    protected $fillable = ['code', 'description', 'is_active'];
}