<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PerongkosanEventDalamKota;
use App\Models\PerongkosanEventLuarKota;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Models\CostCategory;
use App\Models\EventHourGroup;
class FinanceMonitoringService
{
    private const TYPES = [
        'OPR' => TransaksiOperasionalHeader::class,
        'PD'  => TransaksiPerjalananDinasHeader::class,
        'EDK' => PerongkosanEventDalamKota::class,
        'ELK' => PerongkosanEventLuarKota::class,
    ];

    private const LABELS = [
        'OPR' => 'Operasional Sales',
        'PD'  => 'Perjalanan Dinas',
        'EDK' => 'Event Dalam Kota',
        'ELK' => 'Event Luar Kota',
    ];

    private const MODULES = [
    'OPR' => [
        'model' => TransaksiOperasionalHeader::class,
        'index_view' => 'transaksi_operasional.index',
        'show_view' => 'transaksi_operasional.show',
        'list_var' => 'transaksis',
    ],
    'PD' => [
        'model' => TransaksiPerjalananDinasHeader::class,
        'index_view' => 'transaksi_perjalanan_dinas.index',
        'show_view' => 'transaksi_perjalanan_dinas.show',
        'list_var' => 'transaksis',
    ],
    'EDK' => [
        'model' => PerongkosanEventDalamKota::class,
        'index_view' => 'perongkosan_event_dalam_kota.index',
        'show_view' => 'perongkosan_event_dalam_kota.show',
        'list_var' => 'events',
    ],
    'ELK' => [
        'model' => PerongkosanEventLuarKota::class,
        'index_view' => 'perongkosan_event_luar_kota.index',
        'show_view' => 'perongkosan_event_luar_kota.show',
        'list_var' => 'events',
    ],
];

/**
 * Daftar transaksi modul asli untuk user tertentu, semua status, dengan filter periode.
 */
public function getModuleIndex(Company $company, User $user, string $jenis, string $periode): array
{
    $model = $this->context->sourceModel($jenis);
    $query = $model::query()->with('user')->where('user_id', $user->id);

    if (in_array($jenis, ['OPR', 'PD'], true)) {
        $query->withExists('financeChecks')->withSum('details as total_nominal', 'nominal');
        if ($periode !== '') {
            $query->where('periode', $periode);
        }
    } else {
        // Event tidak punya periode konsisten; filter by tanggal sesuai periode Stok Kas.
        if ($periode !== '') {
            $query->where('tanggal', '>=', $periode . '-01')
                  ->where('tanggal', '<', $this->periods->next($periode) . '-01');
        }
    }

    if ($jenis === 'PD') {
        $query->with('tujuanDinas');
    }
    if ($jenis === 'ELK') {
        $query->with(['wilayah', 'jamKerja']);
    }

    $items = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();

    // Bangun URL detail yang mengarah ke monitoring module.show, bukan modul asli.
    $items->getCollection()->transform(function ($item) use ($company, $user, $jenis, $periode) {
        $item->monitoring_detail_url = route('monitoring-finance.module.show', [
            'company' => $company->getKey(),
            'user' => $user->getKey(),
            'jenis' => $jenis,
            'source' => $jenis === 'PD' ? $item->transaksi_id : $item->getKey(),
            'periode' => $periode,
        ]);
        return $item;
    });

    $view = match ($jenis) {
        'OPR' => 'transaksi_operasional.index',
        'PD' => 'transaksi_perjalanan_dinas.index',
        'EDK' => 'perongkosan_event_dalam_kota.index',
        'ELK' => 'perongkosan_event_luar_kota.index',
    };

    $listVar = in_array($jenis, ['OPR', 'PD'], true) ? 'transaksis' : 'events';

    return [
        'view' => $view,
        $listVar => $items,
        'periode' => $periode,
        'jenis' => $jenis,
        'moduleLabel' => $this->context->listLabel($jenis),
    ];
}

/**
 * Detail transaksi modul asli, read-only.
 */
public function getModuleShow(Company $company, User $user, string $jenis, string $source): array
{
    $model = $this->context->sourceModel($jenis);

    if ($jenis === 'PD') {
        $transaksi = $model::query()
            ->with(['user', 'details', 'tujuanDinas.parent', 'bonSementara', 'bsOwner'])
            ->withExists('financeChecks')
            ->where('transaksi_id', $source)
            ->firstOrFail();
    } else {
        $relations = ['user'];
        if ($jenis === 'OPR') {
            $relations[] = 'details';
            $relations[] = 'bonSementara';
            $relations[] = 'bsOwner';
        }
        if ($jenis === 'EDK') {
            $relations[] = 'jamKerja';
        }
        if ($jenis === 'ELK') {
            $relations[] = 'wilayah';
            $relations[] = 'jamKerja';
        }
        $transaksi = $model::query()->with($relations)->findOrFail((int) $source);
    }

    // Validasi kepemilikan SEBELUM render.
    $this->context->assertTransactionOwnership($jenis, $transaksi, $user, $company);

    $view = match ($jenis) {
        'OPR' => 'transaksi_operasional.show',
        'PD' => 'transaksi_perjalanan_dinas.show',
        'EDK' => 'perongkosan_event_dalam_kota.show',
        'ELK' => 'perongkosan_event_luar_kota.show',
    };

    $data = [
        'view' => $view,
        'jenis' => $jenis,
        'moduleLabel' => $this->context->listLabel($jenis),
    ];

    if ($jenis === 'OPR') {
        $data['transaksi'] = $transaksi;
        $data['cost_categories'] = CostCategory::active()->get();
    } elseif ($jenis === 'PD') {
        $data['transaksi'] = $transaksi;
        $data['cost_categories'] = CostCategory::active()->get();
        $data['provinces'] = collect();
        $data['cities'] = collect();
        $data['currentTerritory'] = $transaksi->tujuanDinas->teritory ?? 'Jawa';
        $data['currentProvinceId'] = $transaksi->tujuanDinas->parent_id ?? null;
    } elseif ($jenis === 'EDK') {
        $data['event'] = $transaksi;
        $data['jam_kerja'] = \App\Models\EventHourGroup::all();
    } elseif ($jenis === 'ELK') {
        $data['event'] = $transaksi;
    }

    return $data;
}
public function __construct(
    private StockCashFlowService $stockCash,
    private PeriodService $periods,
    private FinanceBsExpenseService $financeExpenses,
    private FinanceMonitoringContextService $context,
) {}

    public function getAllCompanies(): Collection
    {
        return Company::orderBy('nama')->get();
    }

    public function getCompanyData(Company $company, string $periode, int $page = 1, int $perPage = 20): array
    {
        $periode = $this->periods->normalize($periode);
        $users = $company->users()->with('role')->orderBy('nama')->get();

        $totalMasukMinor = 0;
        $totalKeluarMinor = 0;
        $saldoBerjalanMinor = 0;
        $userSummaries = [];

        foreach ($users as $user) {
            $snapshot = $this->stockCash->recalculateSnapshot((int) $user->id, $periode);

            $masukMinor = Money::minor($snapshot->total_masuk);
            $keluarMinor = Money::minor($snapshot->total_keluar);
            $saldoMinor = Money::minor($snapshot->saldo_berjalan);

            $totalMasukMinor += $masukMinor;
            $totalKeluarMinor += $keluarMinor;
            $saldoBerjalanMinor += $saldoMinor;

            $userSummaries[] = [
                'user' => $user,
                'snapshot' => $snapshot,
                'totals' => [
                    'total_masuk' => Money::decimal($masukMinor),
                    'total_keluar' => Money::decimal($keluarMinor),
                    'saldo_berjalan' => Money::decimal($saldoMinor),
                ],
            ];
        }

        $offset = ($page - 1) * $perPage;
        $pagedUsers = collect($userSummaries)->slice($offset, $perPage)->values();

        $userPaginator = new LengthAwarePaginator(
            $pagedUsers, count($userSummaries), $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $userPaginator->appends(request()->query());

        return [
            'company' => $company,
            'periode' => $periode,
            'users' => $userPaginator,
            'totals' => [
                'total_masuk' => Money::decimal($totalMasukMinor),
                'total_keluar' => Money::decimal($totalKeluarMinor),
                'saldo_berjalan' => Money::decimal($saldoBerjalanMinor),
            ],
        ];
    }

    public function getUserData(User $user, string $periode, ?string $jenisFilter = null, int $page = 1, int $perPage = 20): array
    {
        $periode = $this->periods->normalize($periode);
        $paginator = $this->paginateSources($user, $periode, $jenisFilter, $page, $perPage);
        $rows = $this->hydrateRows($paginator->getCollection(), $user, $periode, $jenisFilter, $page);
        $paginator->setCollection($rows);
        $snapshot = $this->stockCash->recalculateSnapshot((int) $user->id, $periode);

        return [
            'user' => $user,
            'periode' => $periode,
            'transactions' => $paginator,
            'cash_summary' => $snapshot,
        ];
    }

    private function paginateSources(User $user, string $periode, ?string $jenisFilter, int $page, int $perPage): LengthAwarePaginator
    {
        $parts = [];
        foreach (self::TYPES as $type => $model) {
            if ($jenisFilter !== null && $jenisFilter !== $type) continue;
            $parts[] = $this->sourcePart($type, $model, (int) $user->id, $periode);
        }

        if ($parts === []) return $this->emptyPaginator($perPage, $page);

        $union = array_shift($parts);
        foreach ($parts as $part) $union = $union->unionAll($part);

        $query = DB::query()->fromSub($union, 'monitoring_sources')
            ->select('jenis', 'source_id', 'tanggal')
            ->orderBy('tanggal', 'desc')->orderBy('jenis', 'asc')->orderBy('source_id', 'desc');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $paginator->appends(request()->query());
        return $paginator;
    }

    private function sourcePart(string $type, string $model, int $userId, string $periode): Builder
    {
        $table = (new $model)->getTable();
        $query = DB::table($table)
            ->selectRaw('? as jenis, ' . $table . '.id as source_id, ' . $table . '.tanggal', [$type])
            ->where($table . '.user_id', $userId);

        if (in_array($type, ['OPR', 'PD'], true)) {
            $query->where($table . '.periode', $periode);
        } else {
            $query->where($table . '.tanggal', '>=', $periode . '-01')
                  ->where($table . '.tanggal', '<', $this->periods->next($periode) . '-01');
        }
        return $query;
    }

    private function hydrateRows(Collection $items, User $user, string $periode, ?string $jenisFilter, int $page): Collection
    {
        if ($items->isEmpty()) return collect();

        $grouped = $items->groupBy('jenis');
        $loaded = [];

        foreach ($grouped as $type => $group) {
            $model = self::TYPES[$type];
            $ids = $group->pluck('source_id')->unique()->values()->all();
            $relations = in_array($type, ['OPR', 'PD'], true)
                ? ['details.costCategory', 'bonSementara.user', 'financeChecks' => fn ($q) => $q->latestPerSource()->with('details')]
                : ['financeChecks' => fn ($q) => $q->latestPerSource()->with('details')];

            $loaded[$type] = $model::query()->whereIn('id', $ids)->with($relations)->get()->keyBy('id');
        }

        $rows = $items->map(function ($item) use ($loaded, $user, $periode, $jenisFilter, $page) {
            $source = $loaded[$item->jenis]->get($item->source_id);
            if (!$source) return null;

            $isBsModule = in_array($item->jenis, ['OPR', 'PD'], true);
            $ownerId = $isBsModule ? $source->bsCashOwnerId() : (int) $source->user_id;

            $currentCheck = $this->financeExpenses->currentCheck($source);
            $latestCheck = $source->relationLoaded('financeChecks') ? $source->financeChecks->first() : null;

            if ($currentCheck) {
                $financeStatus = $currentCheck->keputusan;
            } elseif ($latestCheck) {
                $financeStatus = 'PERLU_RECHECK';
            } else {
                $financeStatus = 'BELUM_DICEK';
            }

            $nominalUser = $isBsModule ? (string) $source->details->sum('nominal') : (string) ($source->nominal ?? '0');
            $sourceStatus = $source->status ?? ($isBsModule ? 'DRAFT' : 'VALIDATED');

            return [
                'type' => $item->jenis,
                'label' => self::LABELS[$item->jenis],
                'id' => $source->id,
                'reference' => $source->transaksi_id ?? ($item->jenis . ' #' . $source->id),
                'date' => $source->tanggal,
                'source_status' => $sourceStatus,
                'finance_status' => $financeStatus,
                'nominal_user' => $nominalUser,
                'bs_owner_id' => $ownerId,
                'bs_owner_differs' => $ownerId !== null && $ownerId !== (int) $user->id,
                'url' => route('transaction-recap.show', [
                    'jenis' => $item->jenis, 'source' => $source->id,
                    'pemakaian_periode' => $periode, 'dari' => 'monitoring',
                    'company' => (int) $user->company_id, 'user' => (int) $user->id,
                    'page' => $page, 'jenis_filter' => $jenisFilter,
                ]),
            ];
        })->filter()->values();

        $ownerIds = $rows->pluck('bs_owner_id')->filter()->unique()->values()->all();
        $ownerNames = $ownerIds === [] ? [] : User::whereIn('id', $ownerIds)->pluck('nama', 'id')->all();

        return $rows->map(function (array $row) use ($ownerNames) {
            $row['bs_owner_name'] = $ownerNames[$row['bs_owner_id']] ?? '-';
            return $row;
        });
    }

    private function emptyPaginator(int $perPage, int $page): LengthAwarePaginator
    {
        return new LengthAwarePaginator(collect(), 0, $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);
    }
}
