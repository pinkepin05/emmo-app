<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlafonEventDalamKota;
use App\Models\Level;
use App\Models\WeekdayGroup;
use App\Models\EventHourGroup;

class PlafonEventDalamKotaController extends Controller
{
    public function index()
    {
        return $this->create();
    }

    public function create()
    {
        $plafons = PlafonEventDalamKota::with(['level', 'weekdayGroup', 'eventHourGroup'])->get();
        $levels = Level::all();
        $weekdayGroups = WeekdayGroup::all();
        $eventHourGroups = EventHourGroup::all();

        return view('plafon_event_dalam_kota.create', compact('plafons', 'levels', 'weekdayGroups', 'eventHourGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_berlaku' => 'required|date',
            'level_id' => 'required',
            'kelompok_hari_kerja_id' => 'required',
            'hari_event' => 'nullable|in:Reguler,Sabtu,Libur',
            'jam_kerja_event_id' => 'required',
            'description' => 'required|string|max:255',
            'default_value' => 'required|numeric'
        ]);

         // ✅ GUARD: cegah kombinasi dimensi + tanggal yang sama persis
    $duplikat = PlafonEventDalamKota::where('level_id', $request->level_id)
        ->where('kelompok_hari_kerja_id', $request->kelompok_hari_kerja_id)
        ->where('hari_event', $request->hari_event)
        ->where('jam_kerja_event_id', $request->jam_kerja_event_id)
        ->whereDate('tanggal_berlaku', $request->tanggal_berlaku)
        ->exists();

    if ($duplikat) {
        return back()->withErrors([
            'default_value' => '❌ Kombinasi Level + Kelompok Hari + Hari Event + Jam Kerja + Tanggal Berlaku yang sama sudah ada. '
                             . 'Edit baris lama, atau gunakan Tanggal Berlaku yang berbeda (effective dating).'
        ])->withInput();
    }

        PlafonEventDalamKota::create($request->all());

        return redirect()->route('plafon-event-dalam-kota.create')->with('success', 'Setting Plafon Dalam Kota berhasil ditambahkan!');
    }

    // 🎯 METHOD BARU: Form Edit
    public function edit(string $id)
    {
        $plafon = PlafonEventDalamKota::findOrFail($id);
        $levels = Level::all();
        $weekdayGroups = WeekdayGroup::all();
        $eventHourGroups = EventHourGroup::all();

        return view('plafon_event_dalam_kota.edit', compact('plafon', 'levels', 'weekdayGroups', 'eventHourGroups'));
    }

    // 🎯 METHOD BARU: Proses Update
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tanggal_berlaku' => 'required|date',
            'level_id' => 'required',
            'kelompok_hari_kerja_id' => 'required',
            'hari_event' => 'nullable|in:Reguler,Sabtu,Libur',
            'jam_kerja_event_id' => 'required',
            'description' => 'required|string|max:255',
            'default_value' => 'required|numeric'
        ]);

        $plafon = PlafonEventDalamKota::findOrFail($id);
        $plafon->update($request->all());

        return redirect()->route('plafon-event-dalam-kota.create')
                         ->with('success', 'Setting Plafon Dalam Kota berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $plafon = PlafonEventDalamKota::findOrFail($id);
        $plafon->delete();

        return redirect()->route('plafon-event-dalam-kota.create')->with('success', 'Setting Plafon Dalam Kota berhasil dihapus!');
    }
}
