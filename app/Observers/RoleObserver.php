<?php

namespace App\Observers;

use App\Models\Role;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class RoleObserver
{
    /**
     * Role master baru dibuat -> otomatis muncul di Access Management.
     */
    public function created(Role $role): void
    {
        if ($role->code) {
            SpatieRole::firstOrCreate(['name' => $role->code, 'guard_name' => 'web']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * Code role master diubah -> Spatie role ikut diganti namanya.
     */
    public function updating(Role $role): void
    {
        if ($role->isDirty('code')) {
            $old = SpatieRole::where('name', $role->getOriginal('code'))->first();

            if ($old) {
                $old->update(['name' => $role->code]);
            } else {
                SpatieRole::firstOrCreate(['name' => $role->code, 'guard_name' => 'web']);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}