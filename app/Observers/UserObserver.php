<?php

namespace App\Observers;

use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class UserObserver
{
    public function created(User $user): void
    {
        $this->syncSpatieRole($user);
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('roles_id')) {
            $this->syncSpatieRole($user);
        }
    }

    /**
     * SESUAI DOKUMEN rev4:
     * User.User Right Group ID -> roles.code -> Spatie role bernama sama.
     */
    protected function syncSpatieRole(User $user): void
    {
        $code = $user->role?->code;

        if (!$code) {
            $user->syncRoles([]);
            return;
        }

        $spatieRole = SpatieRole::firstOrCreate(['name' => $code, 'guard_name' => 'web']);
        $user->syncRoles([$spatieRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}