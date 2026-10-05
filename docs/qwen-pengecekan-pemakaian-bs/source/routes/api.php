<?php

use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/wilayah-search', function (Request $request) {
    $search = $request->query('q', '');
    
    // Kita ambil id dan nama, lalu kita ubah namanya menjadi 'text' langsung di query
    $results = Wilayah::where('nama', 'LIKE', '%' . $search . '%')
        ->limit(20)
        ->get(['id', 'nama as text']); // Kolom 'nama' diubah jadi 'text'

    return response()->json($results);
});