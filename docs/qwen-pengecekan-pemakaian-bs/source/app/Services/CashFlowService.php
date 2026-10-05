<?php

namespace App\Services;

use App\Models\CashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\ApprovalBsFinance;
use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashFlowService
{
    public function recalculateSnapshot(int $userId, string $periode): void
    {
        $periode = app(PeriodService::class)->normalize($periode);
        $isClosed = Periode::where('periode', $periode)->where('status', 'CLOSED')->exists();
        if ($isClosed && CashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->whereNotNull('saldo_akhir')->exists()) {
            return;
        }

        $saldoAwal   = $this->getSaldoAwal($userId, $periode);
        $totalMasuk  = $this->calculateTotalMasuk($userId, $periode);
        $totalKeluar = $this->calculateTotalKeluar($userId, $periode);
        
        // Rumus: Saldo Awal + Masuk + Pengembalian + Reimburse - Keluar
        $pengembalian = $this->calculatePengembalian($userId, $periode);
        $reimburse    = $this->calculateReimburse($userId, $periode);
        
        $saldoAkhir = $isClosed ? $saldoAwal + $totalMasuk + $pengembalian + $reimburse - $totalKeluar : null;

        CashFlowSnapshot::updateOrCreate(
            ['user_id' => $userId, 'periode' => $periode],
            [
                'saldo_awal'   => $saldoAwal,
                'total_masuk'  => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'saldo_akhir'  => $saldoAkhir,
                'last_updated' => now(),
            ]
        );
    }

    protected function getSaldoAwal(int $userId, string $periode): float
    {
        $prevPeriode = app(PeriodService::class)->previous($periode);
        $prevRecord  = Periode::where('periode', $prevPeriode)->first();

        if ($prevRecord && $prevRecord->status === 'CLOSED') {
            $prevSnapshot = CashFlowSnapshot::where('user_id', $userId)
                ->where('periode', $prevPeriode)
                ->first();
            return $prevSnapshot ? (float) $prevSnapshot->saldo_akhir : 0;
        }
        return 0;
    }

    protected function calculateTotalMasuk(int $userId, string $periode): float
    {
        $bsIds = PengajuanBs::where('user_id', $userId)->pluck('id');
        return (float) ApprovalBsFinance::whereIn('pengajuan_bs_id', $bsIds)
            ->where('periode', $periode)
            ->sum('nominal_approved');
    }

    protected function calculateTotalKeluar(int $userId, string $periode): float
    {
        $cash = app(BsCashFlowService::class);
        $total = 0;
        foreach ([TransaksiOperasionalHeader::class, TransaksiPerjalananDinasHeader::class] as $model) {
            $total += $cash->expenses($model, $userId, $periode)->withSum('details', 'nominal')->get()->sum('details_sum_nominal');
        }
        return (float) ($total + $this->calculateEventKeluar($userId, $periode));
    }

    // 🎯 BARU: Hitung pengembalian sisa BS di periode ini
    protected function calculatePengembalian(int $userId, string $periode): float
    {
        return (float) PengajuanBs::where('user_id', $userId)
            ->where('settled_periode', $periode)
            ->sum('nominal_pengembalian');
    }

    // 🎯 BARU: Hitung reimburse di periode ini
    protected function calculateReimburse(int $userId, string $periode): float
    {
        return (float) PengajuanBs::where('user_id', $userId)
            ->where('settled_periode', $periode)
            ->sum('nominal_reimburse');
    }

    protected function calculateEventKeluar(int $userId, string $periode): float
    {
        $dalam = DB::table('perongkosan_event_dalam_kota')
            ->where('user_id', $userId)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])
            ->sum('nominal');

        $luar = DB::table('perongkosan_event_luar_kota')
            ->where('user_id', $userId)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])
            ->sum('nominal');

        return (float) ($dalam + $luar);
    }

    public function ensureSnapshotForPeriod(string $periode): void
    {
        foreach ($this->usersWithActivityIn($periode) as $userId) {
            $this->recalculateSnapshot($userId, $periode);
        }
    }

    protected function usersWithActivityIn(string $periode): array
    {
        $owners = PengajuanBs::where('periode', $periode)->pluck('user_id');

        $bsIdsUsed = TransaksiOperasionalHeader::where('periode', $periode)->whereNotNull('bs_id')->pluck('bs_id')
            ->merge(TransaksiPerjalananDinasHeader::where('periode', $periode)->whereNotNull('bs_id')->pluck('bs_id'));
        $ownersViaTrx = PengajuanBs::whereIn('id', $bsIdsUsed)->pluck('user_id');

        $eventUsers = DB::table('perongkosan_event_dalam_kota')->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])->pluck('user_id')
            ->merge(DB::table('perongkosan_event_luar_kota')->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode])->pluck('user_id'));

        $snapshotOwners = CashFlowSnapshot::whereIn('periode', [$periode, app(PeriodService::class)->previous($periode)])->pluck('user_id');
        return $owners->merge($ownersViaTrx)->merge(app(BsCashFlowService::class)->owners())->merge($eventUsers)->merge($snapshotOwners)->unique()->values()->all();
    }

    public function recalculateAllPeriods(int $userId): void
    {
        $periods = Periode::orderBy('periode', 'asc')->pluck('periode');
        foreach ($periods as $periode) {
            $this->recalculateSnapshot($userId, $periode);
        }
        $this->recalculateSnapshot($userId, \App\Support\AppTime::now()->format('Y-m'));
    }

    public function getSnapshot(int $userId, string $periode): ?CashFlowSnapshot
    {
        return CashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->first();
    }
}
