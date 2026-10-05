<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BsUsageApprovalLog extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'SUBMITTED' => 'Diajukan kepada pemohon',
            'APPROVED' => 'Disetujui dan dikonfirmasi',
            'REJECTED' => 'Ditolak pemohon',
            'WITHDRAWN' => 'Ditarik kembali oleh anggota',
            'REVISED' => 'Dibuka untuk perbaikan',
            default => $this->action,
        };
    }
}
