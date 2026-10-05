<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;

class PositionController extends Controller
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
        $positions = Position::all();

        return view('positions.create', compact('positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'code' => 'required|unique:position,code',
            'nama' => 'required',
        ]);

        Position::create([
            'code' => $request->code,
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('positions.create')->with('success', 'Master Positions berhasil ditambahkan!');
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
        $positions = Position::findOrFail($id);
        return view('positions.edit', compact('positions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'nama' => 'required',
            // 'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        $positions = Position::findOrFail($id);
        $positions->update([
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('positions.create')->with('success', 'Master Position berhasil diperbarui!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $positions = Position::findOrFail($id);

        $positions->delete();

        return redirect()->route('positions.create')->with('success', 'Master Position berhasil dihapus!');
    }
}
