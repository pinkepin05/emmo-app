<?php

namespace App\Services;

use App\Models\ApprovalBsFinance;
use App\Models\BsCashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Models\StockCashFlowSnapshot;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class BsCashFlowService
{
    public function __construct(private PeriodService $periods) {}

    public function receipts(int $userId, string $periode)
    {
        $periode = $this->periods->normalize($periode);
        return ApprovalBsFinance::whereHas('pengajuanBs', fn ($q) => $q->where('user_id', $userId))
            ->where('tanggal_cair', '>=', $periode.'-01')
            ->where('tanggal_cair', '<', $this->periods->next($periode).'-01');
    }

    public function expenses(string $model, int $userId, string $periode)
    {
        return $model::forBsOwner($userId)
            ->where('periode', $this->periods->normalize($periode))
            ->where('status', 'VALIDATED');
    }

    /** Separate existing settlement adjustments; this service does not perform settlement. */
    public function adjustment(int $userId, string $periode): int
    {
        $query = PengajuanBs::where('user_id', $userId)->where('status', 'SETTLED')->where('settled_periode', $periode);
        return Money::minor((clone $query)->sum('nominal_reimburse')) - Money::minor($query->sum('nominal_pengembalian'));
    }

    public function calculate(int $userId, string $periode): array
    {
        $periode = $this->periods->normalize($periode);
        if (Periode::where('periode', $periode)->where('status', 'CLOSED')->exists()) {
            $frozen = BsCashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->first();
            if ($frozen?->saldo_akhir !== null) {
                return $frozen->only(['saldo_awal', 'total_masuk', 'total_keluar', 'penyesuaian', 'saldo_akhir', 'opening_final', 'finance_confirmed']);
            }
        }
        $previous = $this->periods->previous($periode);
        $previousRecord = Periode::where('periode', $previous)->first();
        $opening = 0;
        $ready = !Periode::where('periode', '<', $periode)->exists();

        if ($previousRecord?->status === 'CLOSED') {
            // Continue the published cash closing balance, including historical event costs.
            // Both current ledgers must inherit it once, even if the old BS-only balance differs.
            $prior = StockCashFlowSnapshot::where('user_id', $userId)->where('periode', $previous)
                ->whereNotNull('saldo_akhir')->first()
                ?? BsCashFlowSnapshot::where('user_id', $userId)->where('periode', $previous)->first();
            if ($prior && $prior->saldo_akhir !== null) {
                $opening = Money::minor($prior->saldo_akhir);
                $ready = $prior->opening_final;
            } else {
                // Bootstrap a missing prior summary without rewriting existing closed snapshots.
                $prior = $this->calculate($userId, $previous);
                $opening = Money::minor($prior['saldo_akhir']);
                $ready = $prior['opening_final'];
            }
        }

        $incoming = Money::minor($this->receipts($userId, $periode)->sum('nominal_approved'));
        $outgoing = app(FinanceBsExpenseService::class)->expenses($userId, $periode)
            ->sum(fn ($row) => Money::minor($row['nominal']));
        $adjustment = $this->adjustment($userId, $periode);

        return [
            'saldo_awal' => Money::decimal($opening),
            'total_masuk' => Money::decimal($incoming),
            'total_keluar' => Money::decimal($outgoing),
            'penyesuaian' => Money::decimal($adjustment),
            'saldo_akhir' => Periode::where('periode', $periode)->where('status', 'CLOSED')->exists()
                ? Money::decimal($opening + $incoming - $outgoing + $adjustment)
                : null,
            'opening_final' => (bool) $ready,
            'finance_confirmed' => true,
        ];
    }

    /** Funds available for transaction validation, separate from the closing balance. */
    public function availableBalance(array $balance): string
    {
        return Money::decimal(
            Money::minor($balance['saldo_awal']) + Money::minor($balance['total_masuk'])
            - Money::minor($balance['total_keluar']) + Money::minor($balance['penyesuaian'])
        );
    }

    public function recalculateSnapshot(int $userId, string $periode): BsCashFlowSnapshot
    {
        $periode = $this->periods->normalize($periode);
        return DB::transaction(function () use ($userId, $periode) {
            $period = Periode::where('periode', $periode)->lockForUpdate()->first();
            DB::table('user')->where('id', $userId)->lockForUpdate()->first();
            $existing = BsCashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->first();
            if ($period?->status === 'CLOSED' && $existing?->saldo_akhir !== null) return $existing;

            return BsCashFlowSnapshot::updateOrCreate(
                ['user_id' => $userId, 'periode' => $periode],
                $this->calculate($userId, $periode) + ['last_updated' => now()]
            );
        }, 3);
    }

    public function owners(): array
    {
        return PengajuanBs::pluck('user_id')->merge(BsCashFlowSnapshot::pluck('user_id'))
            ->merge(TransaksiOperasionalHeader::whereNotNull('bs_owner_id')->pluck('bs_owner_id'))
            ->merge(TransaksiPerjalananDinasHeader::whereNotNull('bs_owner_id')->pluck('bs_owner_id'))
            ->merge(\App\Models\PerongkosanEventDalamKota::pluck('user_id'))
            ->merge(\App\Models\PerongkosanEventLuarKota::pluck('user_id'))
            ->unique()->sort()->values()->all();
    }

    public function ensureSnapshotForPeriod(string $periode): void
    {
        foreach ($this->owners() as $owner) $this->recalculateSnapshot((int) $owner, $periode);
    }

    public function details(int $userId, string $periode): array
    {
        $periode = $this->periods->normalize($periode);
        $result = ['masuk' => [], 'keluar' => [], 'penyesuaian' => []];
        foreach ($this->receipts($userId, $periode)->with('pengajuanBs')->orderBy('tanggal_cair')->get() as $receipt) {
            $result['masuk'][] = ['type' => 'BS Dicairkan', 'date' => $receipt->tanggal_cair,
                'description' => $receipt->pengajuanBs?->document_no ?? '-', 'nominal' => $receipt->nominal_approved,
                'url' => route('pengajuan-bs.show', $receipt->pengajuan_bs_id)];
        }
        $snapshot = $this->recalculateSnapshot($userId, $periode);
        if ($snapshot->finance_confirmed) {
            $result['keluar'] = app(FinanceBsExpenseService::class)->expenses($userId, $periode)->all();
        } else foreach ([TransaksiOperasionalHeader::class => 'Operasional Area Sales', TransaksiPerjalananDinasHeader::class => 'Perjalanan Dinas'] as $model => $label) {
            foreach ($this->expenses($model, $userId, $periode)->with(['user', 'bonSementara'])->withSum('details', 'nominal')->orderBy('tanggal')->get() as $header) {
                $result['keluar'][] = ['type' => $label, 'date' => $header->tanggal,
                    'description' => $header->transaksi_id.' (diinput: '.($header->user?->nama ?? '-').')',
                    'nominal' => $header->details_sum_nominal,
                    'url' => $header instanceof TransaksiPerjalananDinasHeader
                        ? route('transaksi_perjalanan_dinas.show', $header->transaksi_id)
                        : route('transaksi_operasional.show', $header->id)];
            }
        }
        foreach (PengajuanBs::where('user_id', $userId)->where('status', 'SETTLED')->where('settled_periode', $periode)->get() as $bs) {
            $amount = Money::minor($bs->nominal_reimburse) - Money::minor($bs->nominal_pengembalian);
            if ($amount !== 0) $result['penyesuaian'][] = ['description' => $bs->document_no, 'nominal' => Money::decimal($amount), 'date' => $bs->settled_at?->toDateString(),
                'url' => route('pengajuan-bs.show', $bs->id)];
        }
        return $result;
    }
}
