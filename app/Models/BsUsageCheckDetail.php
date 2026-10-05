<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;

class BsUsageCheckDetail extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['nominal_asal' => 'decimal:2', 'nominal_disetujui' => 'decimal:2'];

    public function check()
    {
        return $this->belongsTo(BsUsageCheck::class, 'bs_usage_check_id');
    }

    public function costCategory()
    {
        return $this->belongsTo(CostCategory::class);
    }

    public function getSelisihAttribute(): string
    {
        return Money::decimal(Money::minor($this->nominal_asal) - Money::minor($this->nominal_disetujui));
    }
}
