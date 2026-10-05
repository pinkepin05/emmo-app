<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level;

class LevelController extends Controller
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

        $levels = Level::all();

        return view('levels.create', compact('levels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'code' => 'required|unique:level,code',
            'nama' => 'required',
        ]);

        Level::create([
            'code' => $request->code,
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('levels.create')->with('success', 'Master Level berhasil ditambahkan!');

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
        $level = Level::findOrfail($id);
        return view('levels.edit', compact('level'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $request->validate([
            'nama' => 'required',
        ]);

        $levels = Level::findOrFail($id);
        $levels->update([
            'nama' => $request->nama,
            'is_active' => $request->has('is_active') ? 1 : 0
        ]);

        return redirect()->route('levels.create')->with('success', 'Master Level berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $levels = Level::findOrFail($id);
        $levels->delete();

        return redirect()->route('levels.create')->with('success', 'Master Level berhasil dihapus!');
    }


}
