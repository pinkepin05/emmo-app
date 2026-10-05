<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Memanggil fitur "Satpam" bawaan Laravel 

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        //1. Catat apa yang diketik tamu
        $kredensial = [
            'username' => $request->username,
            'password' => $request->password
        ];

        // 2. Suruh "Satpan" (Auth::attempt) mengecek ke Buku Daftar Tamu (Database)
        if (Auth::attempt($kredensial)) {
            // JIKA COCOK: Beri gelang VIP dan masukkan ke halaman dashboard
            $request->session()->regenerate();
            $request->session()->forget('url.intended');
            return redirect()->route($request->user()->dashboardRouteName());
        }

        // JIKA GAGAL: Kembalikan ke pintu depan dan beri pesan error
        return back()->with('error', 'Username atau Password salah, silahkan coba lagi!');
    }

    public function logout(Request $request){

    // 1. Bersihkan gelang VIP (Sesssion)
    Auth::logout();

    //2. Hancurkan sesi yang tersisa agar tidak bisa dipakai ulang
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login')->with('success', 'Anda telah berhasil logout.');

    }
}
