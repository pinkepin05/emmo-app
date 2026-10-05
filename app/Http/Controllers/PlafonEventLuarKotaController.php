<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlafonEventLuarKota;
use App\Models\Level;
use App\Models\WeekdayGroup;
use App\Models\EventHourGroup;

class PlafonEventLuarKotaController extends Controller
{
    public function index()
    {
        return $this->create();
    }

    public function create()
    {
        $plafons = PlafonEventLuarKota::with(['level', 'weekdayGroup', 'eventHourGroup'])
            ->orderBy('tanggal_berlaku', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $levels = Level::all();
        $weekdayGroups = WeekdayGroup::all();
        $eventHourGroups = EventHourGroup::all();

        return view('plafon_event_luar_kota.create', compact(
            'plafons',
            'levels',
            'weekdayGroups',
            'eventHourGroups'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_berlaku'          => 'required|date',
            'level_id'                 => 'required|exists:level,id',
            'kelompok_hari_kerja_id'   => 'required|exists:weekday_group,id',
            'hari_event'               => 'required|in:Reguler,Sabtu,Libur',
            'jam_kerja_event_id'       => 'required|exists:event_hour_group,id',
            'description'              => 'required|string|max:255',
            'default_value'            => 'required|numeric|min:0',
        ]);

        // Cegah duplikasi kombinasi plafon yang sama persis
        $duplikat = PlafonEventLuarKota::where('tanggal_berlaku', $request->tanggal_berlaku)
            ->where('level_id', $request->level_id)
            ->where('kelompok_hari_kerja_id', $request->kelompok_hari_kerja_id)
            ->where('hari_event', $request->hari_event)
            ->where('jam_kerja_event_id', $request->jam_kerja_event_id)
            ->exists();

        if ($duplikat) {
            return back()
                ->withErrors([
                    'default_value' => 'Kombinasi Tanggal Berlaku, Level, Kelompok Hari Kerja, Hari Event, dan Jam Kerja Event sudah ada. Gunakan Edit atau buat Tanggal Berlaku yang berbeda.'
                ])
                ->withInput();
        }

        PlafonEventLuarKota::create($request->only([
            'tanggal_berlaku',
            'level_id',
            'kelompok_hari_kerja_id',
            'hari_event',
            'jam_kerja_event_id',
            'description',
            'default_value',
        ]));

        return redirect()
            ->route('plafon-event-luar-kota.create')
            ->with('success', 'Setting Plafon Luar Kota berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $plafon = PlafonEventLuarKota::findOrFail($id);
        $levels = Level::all();
        $weekdayGroups = WeekdayGroup::all();
        $eventHourGroups = EventHourGroup::all();

        return view('plafon_event_luar_kota.edit', compact(
            'plafon',
            'levels',
            'weekdayGroups',
            'eventHourGroups'
        ));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'tanggal_berlaku'          => 'required|date',
            'level_id'                 => 'required|exists:level,id',
            'kelompok_hari_kerja_id'   => 'required|exists:weekday_group,id',
            'hari_event'               => 'required|in:Reguler,Sabtu,Libur',
            'jam_kerja_event_id'       => 'required|exists:event_hour_group,id',
            'description'              => 'required|string|max:255',
            'default_value'            => 'required|numeric|min:0',
        ]);

        $plafon = PlafonEventLuarKota::findOrFail($id);

        // Cegah duplikasi selain record yang sedang diedit
        $duplikat = PlafonEventLuarKota::where('tanggal_berlaku', $request->tanggal_berlaku)
            ->where('level_id', $request->level_id)
            ->where('kelompok_hari_kerja_id', $request->kelompok_hari_kerja_id)
            ->where('hari_event', $request->hari_event)
            ->where('jam_kerja_event_id', $request->jam_kerja_event_id)
            ->where('id', '!=', $plafon->id)
            ->exists();

        if ($duplikat) {
            return back()
                ->withErrors([
                    'default_value' => 'Kombinasi Tanggal Berlaku, Level, Kelompok Hari Kerja, Hari Event, dan Jam Kerja Event sudah ada pada data lain.'
                ])
                ->withInput();
        }

        $plafon->update($request->only([
            'tanggal_berlaku',
            'level_id',
            'kelompok_hari_kerja_id',
            'hari_event',
            'jam_kerja_event_id',
            'description',
            'default_value',
        ]));

        return redirect()
            ->route('plafon-event-luar-kota.create')
            ->with('success', 'Setting Plafon Luar Kota berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $plafon = PlafonEventLuarKota::findOrFail($id);
        $plafon->delete();

        return redirect()
            ->route('plafon-event-luar-kota.create')
            ->with('success', 'Setting Plafon Luar Kota berhasil dihapus!');
    }
}
