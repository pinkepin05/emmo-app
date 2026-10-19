<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Level;
use App\Models\Position;
use App\Models\Wilayah;
use App\Services\UserPasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
public function store(Request $request, UserPasswordService $passwords)
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
        'password' => 'required|string|min:6',
    ]);

    $password = $validatedData['password'];
    unset($validatedData['password']);
    $user = new User($validatedData);
    $passwords->assign($user, $password);
    $user->save();

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

    public function update(Request $request, string $id, UserPasswordService $passwords)
    {
         $data = $request->validate([
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
            'password' => 'nullable|string|min:6',
            'is_active' => 'sometimes|boolean',
        ]);

        $item = User::findOrFail($id);
        $password = $data['password'] ?? null;
        unset($data['password']);
        $item->fill($data);
        if ($password !== null && $password !== '') {
            $passwords->assign($item, $password);
        }
        $item->save();

        return redirect()->route('users.create')->with('success', 'Data User berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $item = User::findOrFail($id);
        $item->delete();

        return redirect()->route('users.create')->with('success', 'Data User berhasil dihapus!');
    }

    public function revealPassword(Request $request, User $user, UserPasswordService $passwords): JsonResponse
    {
        $password = $passwords->reveal($user);
        $headers = [
            'Cache-Control' => 'no-store, private, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($password === null) {
            return response()->json([
                'message' => 'Password belum tersedia untuk ditampilkan. Isi password baru lalu simpan terlebih dahulu.',
            ], 409, $headers);
        }

        Log::notice('Master User password viewed', [
            'actor_id' => $request->user()->id,
            'user_id' => $user->id,
        ]);

        return response()->json(['password' => $password], 200, $headers);
    }

    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}
