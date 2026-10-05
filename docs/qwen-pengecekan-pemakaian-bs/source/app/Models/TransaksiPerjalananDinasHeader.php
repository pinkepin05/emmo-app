<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiPerjalananDinasHeader extends Model
{
    use HasFactory, \App\Models\Concerns\UsesBsBalance;

    protected $table = 'transaksi_perjalanan_dinas_header';
    public $timestamps = false;
    protected $casts = ['validated_at' => 'datetime', 'submitted_at' => 'datetime', 'owner_reviewed_at' => 'datetime', 'approval_version' => 'integer'];
    protected $attributes = ['status' => 'DRAFT'];

    protected $fillable = [
        'status', 'validated_at', 'validated_by', 'bs_owner_id',
        'approval_version', 'submitted_at', 'submitted_by', 'owner_reviewed_at', 'owner_reviewed_by', 'owner_rejection_reason',
        'transaksi_id',
        'document_no',
        'tanggal',
        'no_referensi_bs',
        'bs_id',
        'user_id',
        'tujuan_dinas_id',   // ✅ diperbaiki dari 'tujuan_dinas'
        'jarak_km',          // ✅ ditambah
        'durasi_jam',        // ✅ ditambah
        'description',
        'periode',
        'is_over_plafon',
        'plafon_value',      // ✅ ditambah (dipakai di $header->update())
        'selisih_plafon',    // ✅ ditambah
    ];

    public function details()
    {
        return $this->hasMany(TransaksiPerjalananDinasDetail::class, 'transaksi_id', 'transaksi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bonSementara()
    {
        return $this->belongsTo(PengajuanBs::class, 'bs_id', 'id');
    }

    public function tujuanDinas()
    {
        return $this->belongsTo(Wilayah::class, 'tujuan_dinas_id', 'id');
    }

    public function approvalLogs()
    {
        return $this->hasMany(BsUsageApprovalLog::class, 'transaction_id')->orderBy('id');
    }

    public function needsOwnerApproval(): bool
    {
        return $this->bsCashOwnerId() !== null && $this->bsCashOwnerId() !== (int) $this->user_id;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'Draft',
            'PENDING_OWNER' => 'Menunggu Persetujuan Pemohon',
            'REJECTED_OWNER' => 'Ditolak Pemohon',
            'VALIDATED' => 'Dikonfirmasi',
            default => $this->status,
        };
    }
}
