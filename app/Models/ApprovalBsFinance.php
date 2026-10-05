<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalBsFinance extends Model
{
    use HasFactory;

    // 🎯 PERBAIKAN: Tambahkan baris ini agar Laravel mencari tabel yang benar
    protected $table = 'approval_bs_finance'; 
    
    protected $guarded = ['id'];

    public function pengajuanBs() { 
        return $this->belongsTo(PengajuanBs::class, 'pengajuan_bs_id'); 
    }
    
    public function financeUser() { 
        return $this->belongsTo(User::class, 'finance_user_id'); 
    }
}