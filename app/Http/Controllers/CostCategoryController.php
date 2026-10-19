<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CostCategory;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class CostCategoryController extends Controller
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
        //
        $costCategories = CostCategory::all();
        return view('cost_categories.create', compact('costCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Field code pada form disimpan di kolom nama.
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::unique('cost_category', 'nama')],
            'description' => ['required', 'string'],
        ]);

        CostCategory::create([
            'nama' => $validated['code'],
            'description' => $validated['description'],
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);
        return redirect()->route('cost_categories.create')->with('success', 'Master Cost Category berhasil ditambahkan!');


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $costCategories = CostCategory::findOrFail($id);
        return view('cost_categories.edit', compact('costCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $costCategory = CostCategory::findOrFail($id);
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::unique('cost_category', 'nama')->ignore($costCategory)],
            'description' => ['required', 'string'],
        ]);

        $costCategory->update([
            'nama' => $validated['code'],
            'description' => $validated['description'],
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('cost_categories.create')->with('success', 'Master Cost Category berhasil diupdate!');
    }
    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
    {
        try {
            $costCategory = CostCategory::findOrFail($id);
            $costCategory->delete();

            return redirect()->back()->with('success', 'Cost Category berhasil dihapus!');

        } catch (QueryException $e) {
            // Cek apakah errornya adalah kode 23000 (Integrity Constraint Violation / Foreign Key)
            if ($e->getCode() == "23000") {
                return redirect()->back()->withErrors(['error' => 'GAGAL MENGHAPUS: Kategori Biaya ini tidak bisa dihapus karena sudah digunakan pada riwayat Transaksi.']);
            }
            
            // Tangkap error database lainnya
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan database: ' . $e->getMessage()]);
        }
    }
}
