<?php

namespace Database\Seeders;

use App\Models\Role as MasterRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ==========================================
        // 1. DAFTAR RESMI PERMISSION (format dokumen rev4)
        // ==========================================
        $canonical = [
            'akses.pengajuan-bs',
           
            'akses.approval-finance',
            'akses.transaksi-operasional',
            'akses.transaksi-perjalanan-dinas',
            'akses.event-dalam-kota',
            'akses.event-luar-kota',
            'akses.laporan-bs',
             'akses.settle-bs',
            'akses.cash-flow',
            'akses.master-data',
            'akses.access-management',
        ];

        foreach ($canonical as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // ==========================================
        // 2. 🧹 CLEANUP: HAPUS PERMISSION LAMA DI LUAR DAFTAR RESMI
        //    (mis. sisa format lama: akses.transaksi.operasional,
        //     akses.approval.atasan, akses.master.data, dll)
        //    Pivot di role_has_permissions & model_has_permissions
        //    ikut terhapus otomatis karena ON DELETE CASCADE.
        // ==========================================
        Permission::whereNotIn('name', $canonical)->get()->each->delete();

        // ==========================================
        // 3. CERMINKAN MASTER ROLES (User Right Group) KE SPATIE ROLES
        // ==========================================
        $masterCodes = MasterRole::pluck('code')->filter()->values();

        foreach ($masterCodes as $code) {
            SpatieRole::firstOrCreate(['name' => $code, 'guard_name' => 'web']);
        }

        if ($masterCodes->isNotEmpty()) {
            SpatieRole::whereNotIn('name', $masterCodes)->get()->each->delete();
        }

        // ==========================================
        // 4. MATRIKS DEFAULT (hanya jika role masih kosong)
        // ==========================================
        $all = Permission::pluck('name')->toArray();

        $matrix = [
            'SALES' => [
                'akses.pengajuan-bs',
                'akses.transaksi-operasional',
                'akses.transaksi-perjalanan-dinas',
            ],
            'MARKETING' => [
                'akses.pengajuan-bs',
                'akses.event-dalam-kota',
                'akses.event-luar-kota',
            ],
            'OFFICE' => [
                'akses.pengajuan-bs',
                'akses.transaksi-operasional',
                'akses.laporan-bs',
            ],
            'FINANCE' => [
                'akses.approval-finance',
                'akses.laporan-bs',
                'akses.settle-bs', 
                'akses.cash-flow',
                
            ],
            'DIRECTOR' => [
                'akses.laporan-bs',
                'akses.settle-bs', 
                'akses.cash-flow',
            ],
            'DIR' => [
                'akses.laporan-bs',
                'akses.cash-flow',
            ],
            'HR' => [
                'akses.master-data',
            ],
            'ADMINISTRATOR' => $all,
            'ADMIN' => $all,
        ];

        foreach ($matrix as $code => $perms) {
            $role = SpatieRole::where('name', $code)->first();
            if ($role && $role->permissions()->count() === 0) {
                $role->syncPermissions($perms);
            }
        }

        // ==========================================
        // 5. SYNC SEMUA USER KE ROLE MASTER
        // ==========================================
        User::with('role')->get()->each(function (User $user) {
            $code = $user->role?->code;

            if (!$code) {
                $user->syncRoles([]);
                return;
            }

            $spatieRole = SpatieRole::firstOrCreate(['name' => $code, 'guard_name' => 'web']);
            $user->syncRoles([$spatieRole]);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}