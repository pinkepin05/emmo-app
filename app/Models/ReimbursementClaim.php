<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementClaim extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2', 'items' => 'array', 'expense_date' => 'date',
        'submitted_at' => 'datetime', 'approved_at' => 'datetime',
        'rejected_at' => 'datetime', 'paid_at' => 'datetime', 'payment_date' => 'date',
        'payment_proofs' => 'array',
    ];

    public const LABELS = [
        'PENDING_FINANCE' => 'Menunggu Finance',
        'APPROVED' => 'Disetujui, belum dibayar',
        'REJECTED' => 'Ditolak Finance',
        'PAID' => 'Sudah dibayar',
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function payer() { return $this->belongsTo(User::class, 'paid_by'); }
    public function logs() { return $this->hasMany(ReimbursementLog::class)->orderBy('id'); }

}
