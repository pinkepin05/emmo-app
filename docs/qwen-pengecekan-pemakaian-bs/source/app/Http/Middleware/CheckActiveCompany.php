<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveCompany
{
    /**
     * 🎯 L2 - Company Nonaktif Tidak Bisa Dipakai
     * Halaman form tetap terbuka; yang ditolak adalah proses SIMPAN transaksi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // ✅ Company aktif -> lanjut simpan
        if ($user && $user->hasActiveCompany()) {
            return $next($request);
        }

        // ❌ Company non-aktif -> tolak submit
        $message = 'Company tidak aktif. Transaksi tidak dapat dilakukan.';

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
            ->withErrors(['company' => $message])
            ->with('error', $message)
            ->withInput();
    }
}