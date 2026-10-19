<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Models\Company;
use App\Models\Role;
use App\Models\Level;
use App\Models\Position;
use App\Models\Wilayah;

class User extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $table = 'user';
    public $timestamps = false;

    protected $hidden = ['password', 'password_encrypted', 'remember_token'];

    protected $fillable = [
        'NIK', 'nama', 'company_id', 'roles_id', 'level_id', 'position_id',
        'homebase_id', 'kost', 'bbm', 'uang_makan', 'hari_kerja_user',
        'atasan_user_id', 'username', 'password', 'is_active',
    ];

    public function company() { return $this->belongsTo(Company::class, 'company_id'); }
    public function role() { return $this->belongsTo(Role::class, 'roles_id'); }
    public function level() { return $this->belongsTo(Level::class, 'level_id', 'id'); }
    public function position() { return $this->belongsTo(Position::class, 'position_id'); }
    public function homebase() { return $this->belongsTo(Wilayah::class, 'homebase_id', 'id'); }
    public function atasan() { return $this->belongsTo(User::class, 'atasan_user_id'); }
    public function bawahan() { return $this->hasMany(User::class, 'atasan_user_id'); }
    public function pengajuanBs() { return $this->hasMany(PengajuanBs::class, 'user_id'); }
    public function bsMembers() { return $this->belongsToMany(PengajuanBs::class, 'pengajuan_bs_members', 'user_id', 'pengajuan_bs_id'); }

    public function canAccessPortal(): bool
    {
        return in_array($this->role?->code, ['ADMIN', 'ADMINISTRATOR'], true);
    }

    public function dashboardRouteName(): string
    {
        return $this->canAccessPortal() ? 'dashboard' : 'dashboard.transaksi';
    }

    public function canSeeAllTransactions(): bool
    {
         return in_array($this->role->code ?? '', ['ADMIN', 'ADMINISTRATOR', 'DIR', 'FINANCE']);
    }

    public function canAccessTransaction($transaksi): bool
    {
        return $this->canSeeAllTransactions() || $transaksi->user_id === $this->id;
    }

    public function canDeleteTransaction($transaksi): bool
    {
        return in_array($this->role?->code, ['ADMIN', 'ADMINISTRATOR', 'FINANCE'], true)
            && $this->canAccessTransaction($transaksi);
    }

    public function canViewBsTransaction($transaksi): bool
    {
        return $this->canAccessTransaction($transaksi)
            || $transaksi->bsCashOwnerId() === (int) $this->id;
    }

    public function hasActiveCompany(): bool
    {
        if (!$this->relationLoaded('company')) {
            $this->load('company');
        }

        return $this->company !== null && $this->company->isActive();
    }

    public function isActive(): bool
    {
        $value = $this->is_active;

        if (is_string($value)) {
            return in_array(strtoupper(trim($value)), ['Y', 'YES', '1', 'TRUE', 'AKTIF'], true);
        }

        return (bool) $value;
    }

    public function hasActiveAtasan(): bool
    {
        if (!$this->relationLoaded('atasan')) {
            $this->load('atasan');
        }

        if (!$this->atasan) {
            return true;
        }

        return $this->atasan->isActive();
    }
}
