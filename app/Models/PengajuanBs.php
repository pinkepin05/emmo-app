<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanBs extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_bs';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal'    => 'datetime',
        'settled_at' => 'datetime',
    ];

    /* ================= RELATIONS ================= */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'pengajuan_bs_members', 'pengajuan_bs_id', 'user_id');
    }

    public function financeApprovals()
    {
        return $this->hasMany(ApprovalBsFinance::class, 'pengajuan_bs_id');
    }

    public function operasionalSales()
    {
        return $this->hasMany(TransaksiOperasionalHeader::class, 'bs_id');
    }

    public function perjalananDinas()
    {
        return $this->hasMany(TransaksiPerjalananDinasHeader::class, 'bs_id');
    }

    public function logs()
    {
        return $this->hasMany(BsActivityLog::class, 'pengajuan_bs_id');
    }

    /* ================= ACCESSORS ================= */

    /** Label tampilan tipe pengajuan */
    public function getTipeLabelAttribute(): string
    {
        return $this->tipe_pengajuan === 'perjalanan_dinas'
            ? 'Perjalanan Dinas'
            : 'Operasional Sales';
    }

    /** Apakah BS ini tipe perjalanan dinas? */
    public function isPerjalananDinas(): bool
    {
        return $this->tipe_pengajuan === 'perjalanan_dinas';
    }
}