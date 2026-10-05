<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->create();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //Minta asisten gudang ambil semua data dari tabel company
        $companies = Company::all();

        return view('companies.create', compact('companies'));

    }

    /**
     * Menyimpan data baru dari form ke Database
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'code' => 'required|unique:company,code',
            'nama' => 'required',
        ]);

        //Minta assiten gedung untuk membuat data baru di database
        Company::create([
            'code' => $request->code,
            'nama' => $request->nama,
            // Jika checkbox dicentang maka 1, jika tidak maka 0
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        //Jika berhasil, kembalika user ke halaman form dengan pesan sukses
        return redirect()->route('companies.create')->with('success', 'Master Company berhasil ditambahkan!');
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Menampilkan Form Edit yang sudah terisi data lama
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('companies.edit', compact('company'));
    }
    /**
 * Menyimpan Data Hasil Editan ke Database
 */
public function update(Request $request, $id)
{
    // Validasi: code dan nama wajib diisi. 
    // unique:company,code,$id artinya "unik di tabel company, kolom code, abaikan baris dengan id ini"
    $request->validate([
        'code' => 'required|unique:company,code,' . $id,
        'nama' => 'required',
    ]);

    $company = Company::findOrFail($id);
    
    // Update semua field yang diizinkan, termasuk code
    $company->update([
        'code' => $request->code,
        'nama' => $request->nama,
        'is_active' => $request->has('is_active') ? 1 : 0 
    ]);

    return redirect()->route('companies.create')->with('success', 'Master Company berhasil diperbarui!');
}

    /**
     * Menghapus data dari database 
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return redirect()->route('companies.create')->with('success', 'Master Company berhasil dihapus!');
    }
}
