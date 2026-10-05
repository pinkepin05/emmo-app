<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return $this->create();
    }

    // 1. Tampilkan Halaman Form Tambah Data
    public function create()
    {
        $roles = Role::all();
        return view('roles.create', compact('roles'));
    }

    // 2. Proses Simpan Data dari Form ke MySQL
    public function store(Request $request)
    {
        // Validasi input data dari pengguna
        $request->validate([
            'code' => 'required|unique:roles,code|max:50',
            'nama' => 'nullable|string|max:255',
        ]);

        // Simpan ke database menggunakan Eloquent Model
        Role::create([
            'code' => $request->code,
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        // Kembalikan ke halaman form dengan pesan sukses
        return redirect()->route('roles.create')->with('success', 'Master Role berhasil ditambahkan!');
    }

    public function edit($id){

        $role = Role::findOrFail($id);

        return view('roles.edit',compact('role'));

    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
        ]);
        
        $role = Role::findOrFail($id);

        $role->update([
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('roles.create')->with('success', 'Role berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $role->delete();

        return redirect()->route('roles.create')->with('success', "Role berhasil dihapus secara permanen!");
    }
}
