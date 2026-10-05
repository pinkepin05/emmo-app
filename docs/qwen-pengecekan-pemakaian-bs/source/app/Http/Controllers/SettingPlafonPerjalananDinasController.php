<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingPlafonPerjalananDinas;
use App\Models\Wilayah;
use App\Models\Level;

class SettingPlafonPerjalananDinasController extends Controller
{
    public function create()
    {
        $data = SettingPlafonPerjalananDinas::with(['wilayahAwal', 'wilayahTujuan', 'level'])->paginate(20);
        $levels = Level::all();     
        
        return view('setting_plafon_perjalanan_dinas.create', compact('data', 'levels'));
    }

    /**
     * API: Ambil Provinsi berdasarkan Teritory
     */
    public function getProvinsiByTeritory(Request $request)
    {
        $teritory = $request->get('teritory');
        
        $provinsis = Wilayah::where('teritory', $teritory)
                       ->whereNull('parent_id') // Hanya ambil level Provinsi
                       ->select('id', 'nama')
                       ->orderBy('nama', 'asc')
                       ->get();
        
        return response()->json($provinsis);
    }

    public function store(Request $request)
    {
        $request->validate([
            'teritory_awal' => 'required|in:Jawa,Luar Jawa',
            'provinsi_awal_id' => 'required|exists:wilayah,id',
            'teritory_tujuan' => 'required|in:Jawa,Luar Jawa',
            'provinsi_tujuan_id' => 'required|exists:wilayah,id',
            'level_id' => 'required|exists:level,id',
            'description' => 'required|string|max:255',
            'default_value' => 'nullable|integer',
            'tanggal_berlaku' => 'nullable|date',
        ]);

        // 🎯 VALIDASI KEAMANAN: Pastikan provinsi sesuai dengan territory yang dipilih
        $provAwal = Wilayah::find($request->provinsi_awal_id);
        if ($provAwal && $provAwal->teritory !== $request->teritory_awal) {
            return redirect()->back()->withErrors(['provinsi_awal_id' => '❌ Provinsi Awal tidak sesuai dengan Territory yang dipilih!'])->withInput();
        }

        $provTujuan = Wilayah::find($request->provinsi_tujuan_id);
        if ($provTujuan && $provTujuan->teritory !== $request->teritory_tujuan) {
            return redirect()->back()->withErrors(['provinsi_tujuan_id' => '❌ Provinsi Tujuan tidak sesuai dengan Territory yang dipilih!'])->withInput();
        }

        // 🎯 SIMPAN DATA SECARA EKPLISIT (Lebih aman daripada $request->all())
        SettingPlafonPerjalananDinas::create([
            'provinsi_awal_id' => $request->provinsi_awal_id,
            'provinsi_tujuan_id' => $request->provinsi_tujuan_id,
            'level_id' => $request->level_id,
            'description' => $request->description,
            'default_value' => $request->default_value,
            'tanggal_berlaku' => $request->tanggal_berlaku,
        ]);

        return redirect()->route('setting_plafon_perjalanan_dinas.create')
                         ->with('success', 'Data Plafon Perjalanan Dinas berhasil ditambahkan!');
    }

    public function edit($id) 
    {
        $item = SettingPlafonPerjalananDinas::with(['wilayahAwal', 'wilayahTujuan'])->findOrFail($id);
        $levels = Level::all();
        
        return view('setting_plafon_perjalanan_dinas.edit', compact('item', 'levels'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'teritory_awal' => 'required|in:Jawa,Luar Jawa',
            'provinsi_awal_id' => 'required|exists:wilayah,id',
            'teritory_tujuan' => 'required|in:Jawa,Luar Jawa',
            'provinsi_tujuan_id' => 'required|exists:wilayah,id',
            'level_id' => 'required|exists:level,id',
            'description' => 'required|string|max:255',
            'default_value' => 'nullable|integer',
            'tanggal_berlaku' => 'nullable|date',
        ]);

        $provAwal = Wilayah::find($request->provinsi_awal_id);
        if ($provAwal && $provAwal->teritory !== $request->teritory_awal) {
            return redirect()->back()->withErrors(['provinsi_awal_id' => '❌ Provinsi Awal tidak sesuai dengan Territory yang dipilih!'])->withInput();
        }

        $provTujuan = Wilayah::find($request->provinsi_tujuan_id);
        if ($provTujuan && $provTujuan->teritory !== $request->teritory_tujuan) {
            return redirect()->back()->withErrors(['provinsi_tujuan_id' => '❌ Provinsi Tujuan tidak sesuai dengan Territory yang dipilih!'])->withInput();
        }

        $item = SettingPlafonPerjalananDinas::findOrFail($id);
        $item->update([
            'provinsi_awal_id' => $request->provinsi_awal_id,
            'provinsi_tujuan_id' => $request->provinsi_tujuan_id,
            'level_id' => $request->level_id,
            'description' => $request->description,
            'default_value' => $request->default_value,
            'tanggal_berlaku' => $request->tanggal_berlaku,
        ]);

        return redirect()->route('setting_plafon_perjalanan_dinas.create')
                         ->with('success', 'Data Plafon Perjalanan Dinas berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $item = SettingPlafonPerjalananDinas::findOrFail($id);
        $item->delete();

        return redirect()->route('setting_plafon_perjalanan_dinas.create')
                         ->with('success', 'Data Plafon Perjalanan Dinas berhasil dihapus!');
    }

    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}
