<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventHourGroup;

class EventHourGroupController extends Controller
{
    public function create()
    {
        $eventHourGroups = EventHourGroup::all();
        return view('event_hour_groups.create', compact('eventHourGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Hapus aturan unique-nya
            'code' => 'nullable|in:8 Jam,12 Jam,16 Jam', 
            'description' => 'nullable|string|max:255',
        ]);

        EventHourGroup::create([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('event_hour_groups.create')->with('success', 'Master Event Hour Group berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $eventHourGroup = EventHourGroup::findOrFail($id);
        return view('event_hour_groups.edit', compact('eventHourGroup'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'code' => 'nullable|in:8 Jam,12 Jam,16 Jam|unique:event_hour_group,code,' . $id,
            'description' => 'nullable|string|max:255',
        ]);

        $eventHourGroup = EventHourGroup::findOrFail($id);
        $eventHourGroup->update([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('event_hour_groups.create')->with('success', 'Master Event Hour Group berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $eventHourGroup = EventHourGroup::findOrFail($id);
        $eventHourGroup->delete();

        return redirect()->route('event_hour_groups.create')->with('success', 'Master Event Hour Group berhasil dihapus!');
    }

    // Biarkan index dan show kosong
    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}
