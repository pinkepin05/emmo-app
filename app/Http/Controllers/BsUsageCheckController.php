<?php

namespace App\Http\Controllers;

use App\Models\BsUsageCheck;
use App\Models\BsUsageCheckDetail;
use App\Models\BsUsageCheckDraft;
use App\Models\User;
use App\Services\BsUsageCheckService;
use App\Services\BsUsageDashboardService;
use App\Services\PeriodService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BsUsageCheckController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $data = match ($filters['tampilan']) {
            'rincian' => $this->reportData($filters),
            'draft' => $this->draftData($filters),
            default => $this->sourceData($request, $filters),
        };

        return view('bs-usage-checks.index', $data + ['filters' => $filters, 'users' => $this->users()]);
    }

    public function create(string $jenis, int $source, BsUsageCheckService $service)
    {
        $transaction = $service->source($jenis, $source);
        $transaction->load('user');
        $snapshot = $service->snapshot($transaction);
        $draft = BsUsageCheckDraft::where('jenis_transaksi', $jenis)->where('transaksi_pemakaian_id', $source)->first();
        $revisionOf = $transaction->financeChecks()->latest('id')->with('details')->first();
        $previous = $draft ? collect($draft->details) : ($revisionOf?->details->map(fn ($detail) => $detail->getAttributes()) ?? collect());
        if (!$draft && $revisionOf?->keputusan === 'REJECTED') $previous = collect();
        $previous = $previous->keyBy(fn ($item) => (int) $item['cost_category_id']);
        $items = $snapshot['items']->map(function ($item) use ($previous) {
            $saved = $previous->get((int) $item['cost_category_id']);
            $same = $saved && \App\Support\Money::minor($saved['nominal_asal']) === \App\Support\Money::minor($item['nominal_asal']);
            return $item + [
                'nominal_disetujui' => $same ? $saved['nominal_disetujui'] : $item['nominal_asal'],
                'catatan' => $same ? ($saved['catatan'] ?? '') : '',
            ];
        });
        $previousFingerprint = $draft?->source_fingerprint ?? $revisionOf?->source_fingerprint;
        $sourceChanged = $previousFingerprint && $previousFingerprint !== $snapshot['fingerprint'];
        $defaults = [
            'tanggal_pengecekan' => $draft?->tanggal_pengecekan->toDateString() ?? \App\Support\AppTime::now()->toDateString(),
            'keputusan' => $draft && !isset(BsUsageCheckService::DECISIONS[$draft->keputusan]) ? '' : ($draft?->keputusan ?? 'APPROVED'),
            'catatan_keputusan' => $draft?->catatan_keputusan ?? '',
        ];

        return view('bs-usage-checks.create', [
            'transaction' => $transaction, 'jenis' => $jenis, 'items' => $items,
            'sourceToken' => $service->token($jenis, $source, $snapshot['fingerprint']),
            'reviewToken' => $service->reviewToken($jenis, $source, $revisionOf, $draft),
            'draft' => $draft, 'revisionOf' => $revisionOf, 'defaults' => $defaults, 'sourceChanged' => $sourceChanged,
            'historyCount' => $transaction->financeChecks()->count(),
        ]);
    }

    public function store(Request $request, string $jenis, int $source, BsUsageCheckService $service)
    {
        $check = $service->store($request->user(), $jenis, $source, $request->all());
        if ($check instanceof BsUsageCheckDraft) {
            return redirect()->route('bs-usage-checks.create', [$jenis, $source])
                ->with('success', 'Draft pemeriksaan tersimpan. Draft belum menjadi hasil Finance pada dashboard dan laporan.');
        }

        return redirect()->route('bs-usage-checks.show', $check)
            ->with('success', 'Pengecekan '.$check->document_no.' berhasil disimpan. Saldo BS tetap.');
    }

    public function show(BsUsageCheck $check)
    {
        $check->load(['details', 'source', 'revisionOf']);
        $history = BsUsageCheck::where('jenis_transaksi', $check->jenis_transaksi)
            ->where('transaksi_pemakaian_id', $check->transaksi_pemakaian_id)->orderByDesc('id')->paginate(10)->withQueryString();

        return view('bs-usage-checks.show', compact('check', 'history'));
    }

    public function report(Request $request)
    {
        // Keep existing bookmarks working inside the same Finance workspace.
        $request->merge(['tampilan' => 'rincian']);

        return $this->index($request);
    }

    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'tampilan' => 'nullable|in:transaksi,rincian,draft',
            'tanggal_awal' => 'nullable|date_format:Y-m-d',
            'tanggal_akhir' => ['nullable', 'date_format:Y-m-d', ...($request->filled('tanggal_awal') ? ['after_or_equal:tanggal_awal'] : [])],
            'periode' => ['nullable', 'regex:/^\\d{4}-?(0[1-9]|1[0-2])$/D'],
            'jenis' => 'nullable|in:OPR,PD,EDK,ELK',
            'referensi' => 'nullable|string|max:64',
            'user_id' => 'nullable|integer|exists:user,id',
            'status_selisih' => 'nullable|in:sesuai,tidak_sesuai',
            'status_finance' => 'nullable|in:PENDING,REVIEWED,APPROVED,REJECTED,REVISION,RECHECK',
            'status_cek' => 'nullable|in:sudah,belum',
            'mode' => 'nullable|in:terakhir,semua',
            'source_id' => 'nullable|required_with:jenis_source|integer|min:1',
            'jenis_source' => 'nullable|required_with:source_id|in:OPR,PD,EDK,ELK',
            'page' => 'nullable|integer|min:1',
        ]);
        if (!empty($filters['periode'])) {
            $filters['periode'] = app(PeriodService::class)->normalize($filters['periode']);
        }
        $filters['tampilan'] = $filters['tampilan'] ?? 'transaksi';
        $filters['mode'] = $filters['tampilan'] === 'rincian' ? ($filters['mode'] ?? 'terakhir') : 'terakhir';
        // Normalize old lookup links to the new status filter.
        if (!$request->has('status_finance') && !empty($filters['status_cek'])) {
            $filters['status_finance'] = $filters['status_cek'] === 'belum' ? 'PENDING' : 'REVIEWED';
        }
        unset($filters['status_cek'], $filters['page']);
        if ($filters['tampilan'] !== 'transaksi') unset($filters['status_finance']);
        if ($filters['tampilan'] === 'draft') unset($filters['status_selisih']);

        return $filters;
    }

    private function sourceData(Request $request, array $filters): array
    {
        $query = app(BsUsageDashboardService::class)->financeSources($request->user());
        foreach (['jenis' => 'jenis', 'user_id' => 'user_id', 'periode' => 'check_period', 'source_id' => 'source_id', 'jenis_source' => 'jenis'] as $filter => $column) {
            if (!empty($filters[$filter])) $query->where($column, $filters[$filter]);
        }
        if (!empty($filters['referensi'])) $query->where('no_referensi', 'like', '%'.$filters['referensi'].'%');
        if (!empty($filters['tanggal_awal'])) $query->where('tanggal_pengecekan', '>=', $filters['tanggal_awal']);
        if (!empty($filters['tanggal_akhir'])) $query->where('tanggal_pengecekan', '<', \Carbon\CarbonImmutable::parse($filters['tanggal_akhir'])->addDay()->toDateString());
        if (($filters['status_finance'] ?? '') === 'REVIEWED') {
            $query->whereNotNull('check_id');
        } elseif (!empty($filters['status_finance'])) {
            $query->where('finance_status', $filters['status_finance']);
        }
        if (!empty($filters['status_selisih'])) {
            // A stale comparison cannot classify the current transaction as matching.
            $query->whereNotNull('check_id')->where('needs_recheck', 0)
                ->where('different_items', $filters['status_selisih'] === 'sesuai' ? '=' : '>', 0);
        }
        $final = "finance_status IN ('APPROVED', 'REJECTED')";
        $totals = (clone $query)->selectRaw("COUNT(*) as count,
            COALESCE(SUM(nominal_user), 0) as nominal_asal,
            COALESCE(SUM(CASE WHEN $final THEN nominal_finance ELSE 0 END), 0) as nominal_disetujui,
            COALESCE(SUM(CASE WHEN $final THEN nominal_user - nominal_finance ELSE 0 END), 0) as selisih,
            COALESCE(SUM(CASE WHEN $final THEN 0 ELSE 1 END), 0) as unresolved_count")->first();
        $sources = $query->select('usage.*')
            ->selectRaw("CASE WHEN $final THEN nominal_user - nominal_finance ELSE NULL END as selisih")
            ->orderByDesc('tanggal')->orderBy('jenis')->orderByDesc('source_id')
            ->paginate(20)->withPath(route('bs-usage-checks.index'))->appends($filters);
        $drafts = BsUsageCheckDraft::whereIn('jenis_transaksi', $sources->pluck('jenis'))
            ->whereIn('transaksi_pemakaian_id', $sources->pluck('source_id'))
            ->get()->keyBy(fn ($draft) => $draft->jenis_transaksi.':'.$draft->transaksi_pemakaian_id);
        foreach ($sources as $source) $source->draft = $drafts->get($source->jenis.':'.$source->source_id);

        return compact('sources', 'totals');
    }

    private function draftData(array $filters): array
    {
        $query = BsUsageCheckDraft::query();
        foreach (['jenis' => 'jenis_transaksi', 'user_id' => 'user_id', 'periode' => 'periode', 'source_id' => 'transaksi_pemakaian_id', 'jenis_source' => 'jenis_transaksi'] as $filter => $column) {
            if (!empty($filters[$filter])) $query->where($column, $filters[$filter]);
        }
        if (!empty($filters['referensi'])) $query->where('no_referensi', 'like', '%'.$filters['referensi'].'%');
        if (!empty($filters['tanggal_awal'])) $query->where('tanggal_pengecekan', '>=', $filters['tanggal_awal']);
        if (!empty($filters['tanggal_akhir'])) $query->where('tanggal_pengecekan', '<', \Carbon\CarbonImmutable::parse($filters['tanggal_akhir'])->addDay()->toDateString());
        $drafts = $query->with(['source', 'revisionOf'])->orderByDesc('updated_at')->orderByDesc('id')
            ->paginate(20)->withPath(route('bs-usage-checks.index'))->appends($filters);

        return compact('drafts');
    }

    private function reportData(array $filters): array
    {
        $headers = BsUsageCheck::query();
        if ($filters['mode'] === 'terakhir') $headers->latestPerSource();
        foreach (['jenis' => 'jenis_transaksi', 'user_id' => 'user_id', 'periode' => 'periode', 'source_id' => 'transaksi_pemakaian_id', 'jenis_source' => 'jenis_transaksi'] as $filter => $column) {
            if (!empty($filters[$filter])) $headers->where($column, $filters[$filter]);
        }
        if (!empty($filters['referensi'])) $headers->where('no_referensi', 'like', '%'.$filters['referensi'].'%');
        if (!empty($filters['tanggal_awal'])) $headers->where('tanggal_pengecekan', '>=', $filters['tanggal_awal']);
        if (!empty($filters['tanggal_akhir'])) {
            $headers->where('tanggal_pengecekan', '<', \Carbon\CarbonImmutable::parse($filters['tanggal_akhir'])->addDay()->toDateString());
        }
        $different = fn (Builder $q) => $q->whereColumn('nominal_asal', '<>', 'nominal_disetujui');
        if (($filters['status_selisih'] ?? '') === 'tidak_sesuai') {
            $headers->whereHas('details', $different);
        } elseif (($filters['status_selisih'] ?? '') === 'sesuai') {
            $headers->whereDoesntHave('details', $different);
        }

        $query = BsUsageCheckDetail::whereIn('bs_usage_check_id', $headers->select('bs_usage_checks.id'));
        $totals = (clone $query)->selectRaw('COALESCE(SUM(nominal_asal), 0) as nominal_asal, COALESCE(SUM(nominal_disetujui), 0) as nominal_disetujui, COALESCE(SUM(nominal_asal - nominal_disetujui), 0) as selisih')->toBase()->first();
        $rows = $query->with('check')->orderByDesc('bs_usage_check_id')->orderBy('id')
            ->paginate(30)->withPath(route('bs-usage-checks.index'))->appends($filters);

        return compact('rows', 'totals');
    }

    private function users()
    {
        return User::orderBy('nama')->get(['id', 'nama']);
    }
}
