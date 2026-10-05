<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';
    
    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'reason',
        'old_value',
    ];

    protected $casts = [
        'old_value' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
