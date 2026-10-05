<?php

namespace App\Services;

use App\Models\Periode;

class BonSementaraService
{
        /**
     * 🎯 AUTO-ROLL: Periode aktif adalah periode OPEN terbaru.
     * Jika bulan di dunia nyata belum ada di database, sistem otomatis membuatnya.
     */
    public function getActivePeriode(): string
    {
        $currentMonth = \App\Support\AppTime::now()->format('Y-m');
        
        // 1. Pastikan bulan saat ini (dunia nyata) PASTI ada di database dan berstatus OPEN
        Periode::firstOrCreate(
            ['periode' => $currentMonth], 
            ['status' => 'OPEN']
        );

        // 2. Ambil periode OPEN paling baru (mendukung multiple OPEN untuk settlement periode lalu)
        $latestOpen = Periode::where('status', 'OPEN')->orderBy('periode', 'desc')->first();
        
        return $latestOpen ? $latestOpen->periode : $currentMonth;
    }

    public function getSaldoAwal($userId, $periode)
    {
        return app(BsCashFlowService::class)->calculate((int) $userId, $periode)['saldo_awal'];
    }

    public function getRealtimeSisaSaldo($userId, $periode)
    {
        $service = app(BsCashFlowService::class);
        return $service->availableBalance($service->calculate((int) $userId, $periode));
    }

}
