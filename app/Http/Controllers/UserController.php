<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Level;
use App\Models\Position;
use App\Models\Wilayah;

class UserController extends Controller
{
    public function create()
    {
        $data = User::with(['company', 'level', 'position', 'homebase'])
                    ->orderBy('id', 'desc')
                    ->take(50)
                    ->get();
        
        $companies = Company::all();
        $roles = Role::all();
        $levels = Level::all();
        $positions = Position::all();
        
        // HAPUS query $wilayahs di sini karena membuat beban memori penuh
        
        $atasans = User::where('is_active', 1)
                       ->orderBy('nama')
                       ->take(100)
                       ->get();
        
        // Hapus 'wilayahs' dari fungsi compact()
        return view('users.create', compact('data', 'companies', 'roles', 'levels', 'positions', 'atasans'));
    }
public function store(Request $request)
{
    // 1. Validasi
    $validatedData = $request->validate([
        'NIK' => 'nullable|string|max:20|unique:user,NIK',
        'nama' => 'required|string|max:255',
        'company_id' => 'required|exists:company,id',
        'roles_id' => 'required|exists:roles,id',
        'level_id' => 'required|exists:level,id',
        'position_id' => 'required|exists:position,id',
        'homebase_id' => 'required|exists:wilayah,id',
        'kost' => 'nullable|integer',
        'bbm' => 'nullable|integer',
        'uang_makan' => 'nullable|integer',
        'hari_kerja_user' => 'nullable|in:5 HK,6 HK',
        'atasan_user_id' => 'nullable|exists:user,id',
        'username' => 'required|string|max:20',
        'password' => 'required|string|min:6', // Hapus max:20, ganti min:6 agar user aman
    ]);

    // 2. Enkripsi Password sebelum disimpan
    $validatedData['password'] = bcrypt($validatedData['password']);

    // 3. Simpan data yang sudah divalidasi dan dienkripsi
    User::create($validatedData);

    return redirect()->route('users.create')->with('success', 'Data User berhasil ditambahkan!');
}

    public function edit(string $id)
    {
        $item = User::findOrFail($id);
        $companies = Company::all();
        
        // PERBAIKAN: Ubah Roles::all() menjadi Role::all()
        $roles = Role::all(); 
        
        $levels = Level::all();
        $positions = Position::all();
        
        // HAPUS query $wilayahs di sini
        
        $atasans = User::where('id', '!=', $id)->get(); 
        
        // Hapus 'wilayahs' dari fungsi compact()
        return view('users.edit', compact('item', 'companies', 'roles', 'levels', 'positions', 'atasans'));
    }

    public function update(Request $request, string $id)
    {
         // (Isi fungsi update biarkan sama persis seperti kode Anda sebelumnya)
         $request->validate([
            'NIK' => 'nullable|string|max:20|unique:user,NIK,' . $id . ',id',
            'nama' => 'required|string|max:255',
            'company_id' => 'required|exists:company,id',
            'roles_id' => 'required|exists:roles,id',
            'level_id' => 'required|exists:level,id',
            'position_id' => 'required|exists:position,id',
            'homebase_id' => 'required|exists:wilayah,id',
            'kost' => 'nullable|integer',
            'bbm' => 'nullable|integer',
            'uang_makan' => 'nullable|integer',
            'hari_kerja_user' => 'nullable|in:5 HK,6 HK',
            'atasan_user_id' => 'nullable|exists:user,id',
            'username' => 'required|string|max:20',
            'password' => 'nullable|string|max:20', 
        ]);

        $item = User::findOrFail($id);
        $data = $request->all();
        
        if (!empty($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    } else {
        // Jika password kosong, jangan ubah password di database
        unset($data['password']);
    }
    
        $item->update($data);

        return redirect()->route('users.create')->with('success', 'Data User berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $item = User::findOrFail($id);
        $item->delete();

        return redirect()->route('users.create')->with('success', 'Data User berhasil dihapus!');
    }

    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}
