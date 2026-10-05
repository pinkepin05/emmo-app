<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wilayah;

class WilayahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->create($request);
    }

    /**
     * Show the form for creating a new resource.
     */
   public function create(Request $request)
{
    // Ambil keyword dari URL (?search=xxx) dan bersihkan spasi di ujung
    $search = trim((string) $request->get('search', ''));

    $query = Wilayah::with('parent'); // Eager load parent untuk performa

    // 🎯 PENCARIAN HANYA BERDASARKAN NAMA WILAYAH
    // (TIDAK mencari di code maupun teritory)
    if ($search !== '' && $search !== null) {
        $query->where('nama', 'LIKE', "%{$search}%");
    }

    $wilayahs = $query->orderBy('code', 'asc')
                      ->paginate(20)
                      ->withQueryString(); // ✅ Pertahankan ?search= di pagination

    return view('wilayah.create', compact('wilayahs', 'search'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            //Validasi code boleh kosong (nullable) di SQL), tapi jika diisi harus unique
            'code' => 'nullable|unique:wilayah,code|max:50',
            'nama' => 'required',
            'teritory' => 'nullable|in:Jawa,Luar Jawa'
        ]);

        Wilayah::create([
            'code' => $request->code,
            'nama' => $request->nama,
            'parent_id' => $request->parent_id,
            'teritory' => $request->teritory,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('wilayah.create')->with('success', 'Master Wilayah berhasil ditambahkan!');
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
        // 1. Ambil data wilayah yang mau diedit berdasarkan ID
        $wilayah = Wilayah::findOrFail($id);

        // 2. Ambil semua data wilayah untuk pilihan di dropdown parent
        // Agar tidak membingungkan, kita saring agar wilayah ini tidak bisa memilih dirinya sendiri sebagai parent
        $allWilayah = Wilayah::where('id', '!=', $id)->get();

        return view('wilayah.edit', compact('wilayah', 'allWilayah'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi inputan (ingat kolomnya 'nama', bukan 'description')
        // Aturan unique:wilayah,code,$id artinya abaikan pengecekan unik jika kodenya milik dia sendiri
        $request->validate([
            'code' => 'nullable|max:50|unique:wilayah,code,' . $id,
            'nama' => 'required',
            'teritory' => 'nullable|in:Jawa,Luar Jawa'
        ]);

        $wilayah = Wilayah::findOrFail($id);

        // 2. Logika Pengamanan: Cegah jika user mencoba mengakali sistem 
        // dengan memilih dirinya sendiri sebagai parent lewat inspect element
        if ($request->parent_id == $id) {
            return redirect()->back()->withErrors(['parent_id' => 'Wilayah tidak boleh menjadi induk dari dirinya sendiri!'])->withInput();
        }

        // 3. Eksekusi Update
        $wilayah->update([
            'code' => $request->code,
            'nama' => $request->nama,
            'parent_id' => $request->parent_id,
            'teritory' => $request->teritory,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('wilayah.create')->with('success', 'Master Wilayah berhasil diperbarui!');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $wilayah = Wilayah::findOrFail($id);

        // Logika Pengamanan Tambahan: Jika wilayah ini punya "anak" (menjadi parent bagi wilayah lain),
        // ubah parent_id anak-anaknya menjadi NULL terlebih dahulu agar database tidak error (Foreign Key Constraint)
        Wilayah::where('parent_id', $id)->update(['parent_id' => null]);

        // Eksekusi Hapus
        $wilayah->delete();

        return redirect()->route('wilayah.create')->with('success', 'Master Wilayah berhasil dihapus!');
    }

   // GANTI kode search() Anda menjadi ini:
public function search(Request $request)
    {
        $term = trim($request->get('q', ''));
        
        // Jika kosong, kembalikan array kosong
        if (empty($term)) {
            return response()->json([]);
        }

        $wilayahs = Wilayah::where('nama', 'LIKE', "%{$term}%")
                          ->select('id', 'nama')
                          ->orderByRaw('LENGTH(nama) ASC') // Prioritaskan nama yang lebih pendek/tepat
                          ->limit(50) // Batasi 50 hasil agar response cepat (cukup untuk kebutuhan user)
                          ->get()
                          ->map(function($w) {
                              // Format ini WAJIB sesuai standar Select2: 'id' dan 'text'
                              return [
                                  'id' => $w->id, 
                                  'text' => $w->nama
                              ];
                          });
                          
        return response()->json($wilayahs);
    }
}
