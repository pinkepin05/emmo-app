<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BsActivityLog extends Model
{
    protected $table = 'bs_activity_logs';
    protected $guarded = ['id'];

    // 🎯 HELPER STATIK: Memudahkan pencatatan log dari controller manapun
    public static function record($pengajuan_bs_id, $code, $name, $description = null, $nominal = null)
    {
        self::create([
            'pengajuan_bs_id' => $pengajuan_bs_id,
            'activity_code'   => $code,
            'activity_name'   => $name,
            'actor_user_id'   => auth()->id(), // Otomatis mencatat siapa yang sedang login
            'description'     => $description,
            'nominal'         => $nominal,
        ]);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}