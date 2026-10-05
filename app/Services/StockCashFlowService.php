<?php

namespace App\Services;

use App\Models\Periode;
use App\Models\PerongkosanEventDalamKota;
use App\Models\PerongkosanEventLuarKota;
use App\Models\StockCashFlowSnapshot;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class StockCashFlowService
{
    private const EVENTS = [
        PerongkosanEventDalamKota::class => 'Event Dalam Kota',
        PerongkosanEventLuarKota::class => 'Event Luar Kota',
    ];

    public function __construct(private PeriodService $periods, private BsCashFlowService $bsCash) {}

    public function events(string $model, int $userId, string $periode)
    {
        $periode = $this->periods->normalize($periode);
        return $model::where('user_id', $userId)
            ->where('tanggal', '>=', $periode.'-01')
            ->where('tanggal', '<', $this->periods->next($periode).'-01');
    }

    public function recalculateSnapshot(int $userId, string $periode): StockCashFlowSnapshot
    {
        $periode = $this->periods->normalize($periode);
        return DB::transaction(function () use ($userId, $periode) {
            $period = Periode::where('periode', $periode)->lockForUpdate()->first();
            DB::table('user')->where('id', $userId)->lockForUpdate()->first();
            $existing = StockCashFlowSnapshot::where('user_id', $userId)->where('periode', $periode)->first();
            if ($period?->status === 'CLOSED' && $existing?->saldo_akhir !== null) return $existing;

            $bs = $this->bsCash->recalculateSnapshot($userId, $periode);
            $opening = Money::minor($bs->saldo_awal);
            $ready = $bs->opening_final;
            $previous = $this->periods->previous($periode);
            if (Periode::where('periode', $previous)->where('status', 'CLOSED')->exists()) {
                $prior = $this->recalculateSnapshot($userId, $previous);
                $opening = Money::minor($prior->saldo_akhir);
                $ready = $ready && $prior->opening_final;
            }

            $local = Money::minor($this->events(PerongkosanEventDalamKota::class, $userId, $periode)->sum('nominal'));
            $outOfTown = Money::minor($this->events(PerongkosanEventLuarKota::class, $userId, $periode)->sum('nominal'));
            return StockCashFlowSnapshot::updateOrCreate(
                ['user_id' => $userId, 'periode' => $periode],
                [
                    'saldo_awal' => Money::decimal($opening),
                    'total_masuk' => $bs->total_masuk,
                    // Retain obsolete columns for compatibility with existing databases.
                    'total_masuk_reimbursement' => '0.00',
                    'total_keluar_bs' => $bs->total_keluar,
                    'total_event_dalam' => Money::decimal($local),
                    'total_event_luar' => Money::decimal($outOfTown),
                    'total_keluar_pribadi' => '0.00',
                    'total_keluar' => Money::decimal(Money::minor($bs->total_keluar) + $local + $outOfTown),
                    'includes_personal_funds' => false,
                    'penyesuaian' => $bs->penyesuaian,
                    // Preserve the frozen BS closing balance when bootstrapping historical periods.
                    // Carry prior event cash forward once while keeping the BS ledger separate.
                    'saldo_akhir' => $period?->status === 'CLOSED'
                        ? Money::decimal(Money::minor($bs->saldo_akhir) + $opening - Money::minor($bs->saldo_awal) - $local - $outOfTown)
                        : null,
                    'opening_final' => $ready,
                    'last_updated' => now(),
                ]
            );
        }, 3);
    }

    public function owners(): array
    {
        $owners = collect($this->bsCash->owners())->merge(StockCashFlowSnapshot::pluck('user_id'));
        foreach (array_keys(self::EVENTS) as $model) $owners = $owners->merge($model::distinct()->pluck('user_id'));
        return $owners->unique()->sort()->values()->all();
    }

    public function ensureSnapshotForPeriod(string $periode): void
    {
        foreach ($this->owners() as $owner) $this->recalculateSnapshot((int) $owner, $periode);
    }

    public function details(int $userId, string $periode): array
    {
        $this->recalculateSnapshot($userId, $periode);
        $result = $this->bsCash->details($userId, $periode);
        foreach (self::EVENTS as $model => $label) {
            foreach ($this->events($model, $userId, $periode)->orderBy('tanggal')->orderBy('id')->get() as $event) {
                $result['keluar'][] = [
                    'type' => $label,
                    'date' => $event->tanggal,
                    'description' => ($event->transaksi_id ?? 'Event #'.$event->id).($event->description ? ' / '.$event->description : ''),
                    'nominal' => $event->nominal,
                    'url' => $event instanceof PerongkosanEventDalamKota
                        ? route('perongkosan_event_dalam_kota.show', $event->id)
                        : route('perongkosan_event_luar_kota.show', $event->id),
                ];
            }
        }
        $result['masuk'] = collect($result['masuk'])->sortBy('date')->values()->all();
        $result['keluar'] = collect($result['keluar'])->sortBy('date')->values()->all();
        return $result;
    }
}
