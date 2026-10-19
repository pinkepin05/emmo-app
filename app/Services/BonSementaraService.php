<?php

namespace App\Services;

use App\Models\Periode;

class BonSementaraService
{
    /**
     * Periode berjalan mengikuti bulan kalender aplikasi.
     * Buat bulan yang belum tersedia tanpa membuka kembali periode CLOSED.
     */
    public function getActivePeriode(): string
    {
        $currentMonth = app(PeriodService::class)->current();

        Periode::firstOrCreate(
            ['periode' => $currentMonth],
            ['status' => 'OPEN']
        );

        return $currentMonth;
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
