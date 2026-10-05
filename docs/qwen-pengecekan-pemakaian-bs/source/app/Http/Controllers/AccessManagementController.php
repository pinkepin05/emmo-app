<?php

namespace App\Http\Controllers;

use App\Models\Role as MasterRole;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class AccessManagementController extends Controller
{
    public function index()
    {
        foreach (MasterRole::all() as $master) {
            if ($master->code) {
                SpatieRole::firstOrCreate(['name' => $master->code, 'guard_name' => 'web']);
            }
        }

        $roles = SpatieRole::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        $masterNames  = MasterRole::pluck('nama', 'code');
        $masterActive = MasterRole::pluck('is_active', 'code');

        // 🎯 BARIS KUNCI: hitung jumlah user per role master
        $userCounts = User::selectRaw('roles.code as code, COUNT(user.id) as total')
            ->join('roles', 'user.roles_id', '=', 'roles.id')
            ->groupBy('roles.code')
            ->pluck('total', 'code')
            ->toArray();

        return view('admin.access-management', compact(
            'roles',
            'permissions',
            'masterNames',
            'masterActive',
            'userCounts'
        ));
    }

    public function update(Request $request, $roleId)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:spatie_permissions,name',
        ]);

        $role = SpatieRole::findById($roleId);
        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', "Hak akses untuk role <strong>{$role->name}</strong> berhasil diperbarui!");
    }
}