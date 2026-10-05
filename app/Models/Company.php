<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //

    use HasFactory;

    //1. Memberitahu Laravel nama tabelnya adalah 'company'

    protected $table = 'company';

    //2. Mematikan fitur pengisian waktu otomatis (timestamps)
    public $timestamps = false;

    //3. Daftarkan kolom yang diizinkan untuk diisi data (Mass Assignment)

    protected $fillable = [
        'code',
        'nama',
        'is_active'
    ];


    
    // ==========================================
    // 🎯 TAMBAHKAN METHOD INI (Untuk L2 Test Case)
    // ==========================================
    public function isActive(): bool
    {
        $value = $this->is_active;

        // Mendukung format 'Y'/'N', '1'/'0', 1/0, true/false
        if (is_string($value)) {
            return in_array(strtoupper(trim($value)), ['Y', 'YES', '1', 'TRUE', 'AKTIF'], true);
        }

        return (bool) $value;
    }
    }
