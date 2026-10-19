<?php

namespace App\Services;

use App\Models\ApprovalBsFinance;
use App\Models\BsCashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Models\PerongkosanEventDalamKota;
use App\Models\PerongkosanEventLuarKota;
use App\Models\StockCashFlowSnapshot;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockCashFlowService
{
    private const EVENTS = [
        PerongkosanEventDalamKota::class => 'Event Dalam Kota',
        PerongkosanEventLuarKota::class => 'Event Luar Kota',
    ];

    public function __construct(private PeriodService $periods, private BsCashFlowService $bsCash) {}

    /** Include actual posting periods even when a period or snapshot has not been created yet. */
    public function dashboardPeriods(int $userId): Collection
    {
        $periods = Periode::pluck('periode')
            ->merge(StockCashFlowSnapshot::where('user_id', $userId)->pluck('periode'))
            ->merge(BsCashFlowSnapshot::where('user_id', $userId)->pluck('periode'))
            ->merge(PengajuanBs::where('user_id', $userId)->pluck('settled_periode'));

        $receiptDates = ApprovalBsFinance::whereHas('pengajuanBs', fn ($query) => $query->where('user_id', $userId))
            ->distinct()->pluck('tanggal_cair');
        $periods = $periods->merge($receiptDates->map(fn ($date) => substr((string) $date, 0, 7)));

        foreach ([TransaksiOperasionalHeader::class, TransaksiPerjalananDinasHeader::class] as $model) {
            $sources = $model::where(fn ($query) => $query->where('user_id', $userId)
                ->orWhere(fn ($owned) => $owned->forBsOwner($userId)));
            $periods = $periods->merge((clone $sources)->distinct()->pluck('periode'))
                ->merge($sources->distinct()->pluck('tanggal')->map(fn ($date) => substr((string) $date, 0, 7)));
        }
        foreach (array_keys(self::EVENTS) as $model) {
            $dates = $model::where('user_id', $userId)->distinct()->pluck('tanggal');
            $periods = $periods->merge($dates->map(fn ($date) => substr((string) $date, 0, 7)));
        }

        return $periods->push($this->periods->current())->filter()
            ->map(fn ($period) => $this->periods->normalize($period))
            ->unique()->sort()->values();
    }

    /** Empty period means all history: count the opening once, never sum carried balances. */
    public function dashboardSummary(int $userId, string $period, bool $refreshSnapshots = true): object
    {
        $read = function (string $month) use ($userId, $refreshSnapshots): object {
            if ($refreshSnapshots) return $this->recalculateSnapshot($userId, $month);
            // Users without Stok Kas access can read their balance without creating cash reports.
            $balance = $this->bsCash->calculate($userId, $month);
            return (object) ($balance + ['saldo_berjalan' => $this->bsCash->availableBalance($balance)]);
        };
        if ($period !== '') return $read($period);

        $snapshots = $this->dashboardPeriods($userId)->map($read);
        // Empty periods before this user's ledger starts must not hide its initial balance.
        $first = $snapshots->first(fn ($row) => Money::minor($row->saldo_awal) !== 0
            || Money::minor($row->total_masuk) !== 0 || Money::minor($row->total_keluar) !== 0
            || Money::minor($row->penyesuaian) !== 0) ?? $snapshots->first();
        $opening = Money::minor($first->saldo_awal);
        $incoming = $snapshots->sum(fn ($row) => Money::minor($row->total_masuk));
        $outgoing = $snapshots->sum(fn ($row) => Money::minor($row->total_keluar));
        $adjustment = $snapshots->sum(fn ($row) => Money::minor($row->penyesuaian));

        return (object) [
            'saldo_awal' => Money::decimal($opening),
            'total_masuk' => Money::decimal($incoming),
            'total_keluar' => Money::decimal($outgoing),
            'penyesuaian' => Money::decimal($adjustment),
            'saldo_berjalan' => Money::decimal($opening + $incoming - $outgoing + $adjustment),
            'opening_final' => (bool) $first->opening_final,
        ];
    }

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
            if ($period?->status === 'CLOSED' && $existing?->saldo_akhir !== null) {
                return $this->withArchivedBreakdown($existing);
            }

            $bs = $this->bsCash->recalculateSnapshot($userId, $periode);
            $opening = Money::minor($bs->saldo_awal);
            $ready = $bs->opening_final;
            $totals = $this->expenseTotals($userId, $periode, (bool) $bs->finance_confirmed);
            $local = $totals['EDK'];
            $outOfTown = $totals['ELK'];

            if (!$bs->finance_confirmed) {
                $previous = $this->periods->previous($periode);
                if (Periode::where('periode', $previous)->where('status', 'CLOSED')->exists()) {
                    $prior = $this->recalculateSnapshot($userId, $previous);
                    $opening = Money::minor($prior->saldo_akhir);
                    $ready = $ready && $prior->opening_final;
                }
            }

            return StockCashFlowSnapshot::updateOrCreate(
                ['user_id' => $userId, 'periode' => $periode],
                [
                    'saldo_awal' => Money::decimal($opening),
                    'total_masuk' => $bs->total_masuk,
                    'total_masuk_reimbursement' => '0.00',
                    'total_keluar_bs' => Money::decimal(Money::minor($bs->total_keluar) - ($bs->finance_confirmed ? $local + $outOfTown : 0)),
                    'total_keluar_operasional' => Money::decimal($totals['OPR']),
                    'total_keluar_perjalanan_dinas' => Money::decimal($totals['PD']),
                    'total_event_dalam' => Money::decimal($local),
                    'total_event_luar' => Money::decimal($outOfTown),
                    'total_keluar_pribadi' => '0.00',
                    'total_keluar' => Money::decimal(Money::minor($bs->total_keluar) + ($bs->finance_confirmed ? 0 : $local + $outOfTown)),
                    'includes_personal_funds' => false,
                    'penyesuaian' => $bs->penyesuaian,
                    'saldo_akhir' => $period?->status === 'CLOSED'
                        ? ($bs->finance_confirmed ? $bs->saldo_akhir : Money::decimal(Money::minor($bs->saldo_akhir) + $opening - Money::minor($bs->saldo_awal) - $local - $outOfTown))
                        : null,
                    'finance_confirmed' => (bool) $bs->finance_confirmed,
                    'opening_final' => $ready,
                    'last_updated' => now(),
                ]
            );
        }, 3);
    }

    /** Amounts in minor units, using the same Finance decisions as Total Keluar. */
    private function expenseTotals(int $userId, string $periode, bool $financeConfirmed): array
    {
        if ($financeConfirmed) {
            $expenses = app(FinanceBsExpenseService::class)->expenses($userId, $periode);
            return collect(['OPR', 'PD', 'EDK', 'ELK'])->mapWithKeys(fn ($module) => [
                $module => $expenses->where('module', $module)->sum(fn ($row) => Money::minor($row['nominal'])),
            ])->all();
        }

        $totals = [];
        foreach (['OPR' => TransaksiOperasionalHeader::class, 'PD' => TransaksiPerjalananDinasHeader::class] as $module => $model) {
            $totals[$module] = $this->bsCash->expenses($model, $userId, $periode)->withSum('details', 'nominal')->get()
                ->sum(fn ($row) => Money::minor($row->details_sum_nominal));
        }
        $totals['EDK'] = Money::minor($this->events(PerongkosanEventDalamKota::class, $userId, $periode)->sum('nominal'));
        $totals['ELK'] = Money::minor($this->events(PerongkosanEventLuarKota::class, $userId, $periode)->sum('nominal'));
        return $totals;
    }

    /** Derive missing labels for display only; never rewrite published closing balances. */
    private function withArchivedBreakdown(StockCashFlowSnapshot $snapshot): StockCashFlowSnapshot
    {
        if ($snapshot->total_keluar_operasional !== null && $snapshot->total_keluar_perjalanan_dinas !== null) return $snapshot;
        $totals = $this->expenseTotals((int) $snapshot->user_id, $snapshot->periode, (bool) $snapshot->finance_confirmed);
        if ($totals['OPR'] + $totals['PD'] !== Money::minor($snapshot->total_keluar_bs)) return $snapshot;
        foreach (['OPR' => 'total_keluar_operasional', 'PD' => 'total_keluar_perjalanan_dinas'] as $module => $column) {
            if ($snapshot->$column !== null && Money::minor($snapshot->$column) !== $totals[$module]) return $snapshot;
        }
        $snapshot->total_keluar_operasional = Money::decimal($totals['OPR']);
        $snapshot->total_keluar_perjalanan_dinas = Money::decimal($totals['PD']);
        return $snapshot;
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

    /** Informational usage shared by its inputter and BS owner; never added to cash totals. */
    public function sharedUsage(int $userId, string $periode): Collection
    {
        $periode = $this->periods->normalize($periode);
        $finance = app(FinanceBsExpenseService::class);
        $rows = collect();
        foreach (['OPR', 'PD'] as $type) {
            $model = BsUsageCheckService::TYPES[$type];
            $sources = $model::with([
                'user', 'bsOwner', 'bonSementara.user', 'details',
                'financeChecks' => fn ($checks) => $checks->latestPerSource()->with('details'),
            ])->where('tanggal', '>=', $periode.'-01')
                ->where('tanggal', '<', $this->periods->next($periode).'-01')
                ->where('status', 'VALIDATED')
                ->where(fn ($query) => $query->where('user_id', $userId)
                    ->orWhere(fn ($owned) => $owned->forBsOwner($userId)))->get();
            foreach ($sources as $source) {
                $owner = $source->bsCashOwnerId();
                if ($owner === null || $owner === (int) $source->user_id) continue;
                $check = $finance->currentCheck($source);
                $rows->push([
                    'type' => BsUsageCheckService::LABELS[$type],
                    'reference' => $source->transaksi_id,
                    'date' => $source->tanggal,
                    'user_id' => (int) $source->user_id,
                    'user_name' => $source->user?->nama ?? '-',
                    'owner_id' => $owner,
                    'owner_name' => $source->bsOwner?->nama ?? $source->bonSementara?->user?->nama ?? '-',
                    'reported' => Money::decimal($source->details->sum(fn ($detail) => Money::minor($detail->nominal))),
                    'approved' => $check ? Money::decimal($finance->approvedAmount($source)) : null,
                    'finance_status' => match ($check?->keputusan) {
                        'APPROVED' => 'Dikonfirmasi Finance',
                        'REJECTED' => 'Ditolak Finance',
                        'REVISION' => 'Perlu Revisi',
                        default => 'Belum Dicek Finance',
                    },
                    'url' => $type === 'PD'
                        ? route('transaksi_perjalanan_dinas.show', $source->transaksi_id)
                        : route('transaksi_operasional.show', $source->id),
                ]);
            }
        }
        return $rows->sortByDesc('date')->values();
    }

    public function details(int $userId, string $periode): array
    {
        $snapshot = $this->recalculateSnapshot($userId, $periode);
        $result = $this->bsCash->details($userId, $periode);
        if ($snapshot->finance_confirmed) return $result;
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
