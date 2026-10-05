<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // 1. Definisikan nama tabel asli di database
    protected $table = 'roles';

    // 2. Tentukan primary key tabel
    protected $primaryKey = 'id';

    // 3. Matikan timestamps karena tidak ada kolom created_at & updated_at
    public $timestamps = false;

    // 4. Daftarkan kolom yang boleh diisi secara massal (Mass Assignment)
    protected $fillable = [
        'code',
        'nama',
        'is_active',
    ];

    /**
     * Relasi ke tabel User (One-to-Many)
     * Satu Role bisa dimiliki oleh banyak User
     */
    public function users()
    {
        // Hubungkan kembali ke model User melalui foreign key 'roles_id'
        return $this->hasMany(User::class, 'roles_id', 'id');
    }

//     public function role()
// {
//     // User terhubung ke Role melalui kolom 'roles_id'
//     return $this->belongsTo(Role::class, 'roles_id', 'id');
// }
}