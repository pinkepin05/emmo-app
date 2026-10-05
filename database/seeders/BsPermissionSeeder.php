<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'bs.create',
            'bs.approve.atasan',
            'bs.approve.finance',
            'bs.report'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Sesuaikan nama Role dengan yang ada di database Anda
        // Role::findByName('Sales')->givePermissionTo('bs.create');
        // Role::findByName('Manager')->givePermissionTo('bs.approve.atasan');
        // Role::findByName('Finance')->givePermissionTo('bs.approve.finance');
    }
}