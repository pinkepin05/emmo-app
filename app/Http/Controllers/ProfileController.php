<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        // Ambil data user yang sedang login beserta relasinya
        $user = Auth::user()->load(['role', 'company', 'level', 'position', 'homebase', 'atasan.position']);
        $bawahan = $user->bawahan()
            ->select(['id', 'atasan_user_id', 'nama', 'NIK', 'position_id', 'homebase_id', 'is_active'])
            ->with(['position', 'homebase'])
            ->orderBy('nama')
            ->orderBy('id')
            ->get();

        return view('profile.index', compact('user', 'bawahan'));
    }
}
