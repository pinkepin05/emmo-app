<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveAtasan
{
    /**
     * 🎯 L3 - Atasan Nonaktif
     * Menolak submit transaksi jika atasan user sedang nonaktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // ✅ Atasan aktif (atau user tidak punya atasan) -> lanjut
        if ($user && $user->hasActiveAtasan()) {
            return $next($request);
        }

        // ❌ Atasan non-aktif -> tolak submit
        $message = 'Atasan user tidak aktif. Hubungi Administrator untuk perbaikan data atasan.';

        // Request AJAX -> JSON 403
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        // Request form POST -> kembalikan ke form + tampilkan error + pertahankan isian
        return redirect()
            ->back()
            ->withErrors(['atasan' => $message])
            ->with('error', $message)
            ->withInput();
    }
}