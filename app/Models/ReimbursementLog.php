<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementLog extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['created_at' => 'datetime', 'amount' => 'decimal:2'];

    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
}
