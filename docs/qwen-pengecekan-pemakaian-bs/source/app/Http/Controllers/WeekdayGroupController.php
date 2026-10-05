<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeekdayGroup;

class WeekdayGroupController extends Controller
{
    public function create()
    {
        $weekdayGroups = WeekdayGroup::all();
        return view('weekday_groups.create', compact('weekdayGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|unique:weekday_group,code|in:5 HK,6 HK',
            'description' => 'nullable|string|max:255',
        ]);

        WeekdayGroup::create([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('weekday_groups.create')->with('success', 'Master Weekday Group berhasil ditambahkan!');
    }

    public function edit(string $id)
    {
        $weekdayGroup = WeekdayGroup::findOrFail($id);
        return view('weekday_groups.edit', compact('weekdayGroup'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'code' => 'nullable|in:5 HK,6 HK|unique:weekday_group,code,' . $id,
            'description' => 'nullable|string|max:255',
        ]);

        $weekdayGroup = WeekdayGroup::findOrFail($id);
        $weekdayGroup->update([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('weekday_groups.create')->with('success', 'Master Weekday Group berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $weekdayGroup = WeekdayGroup::findOrFail($id);
        $weekdayGroup->delete();

        return redirect()->route('weekday_groups.create')->with('success', 'Master Weekday Group berhasil dihapus!');
    }

    public function index()
    {
        return $this->create();
    }

    public function show(string $id) {}
}
