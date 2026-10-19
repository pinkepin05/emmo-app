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

    public function create(string $jenis, int $source, BsUsageCheckService $service, \App\Services\BsUsageCheckFormService $form)
    {
        return view('bs-usage-checks.create', $form->data($service->source($jenis, $source), $jenis));
    }

    public function store(Request $request, string $jenis, int $source, BsUsageCheckService $service)
{
    $navigation = $request->validate([
        '_workspace' => 'sometimes|boolean',
        'workspace_context' => 'sometimes|array',
        'workspace_context.pemakaian_periode' => ['nullable', 'string', 'regex:/^$|^\d{4}-?(0[1-9]|1[0-2])$/D'],
        'workspace_context.pemakaian_status' => 'nullable|in:FOLLOW_UP,PENDING,APPROVED,REJECTED,REVISION,DIFFERENCE',
        'workspace_context.pemakaian_referensi' => 'nullable|string|max:64',
        'workspace_context.pemakaian_page' => 'nullable|integer|min:1',
        'workspace_context.dari' => 'nullable|in:dashboard,laporan,pengecekan,periode,monitoring',
        'workspace_context.company' => ['nullable', 'integer', 'min:1', 'required_if:workspace_context.dari,monitoring'],
        'workspace_context.user' => ['nullable', 'integer', 'min:1', 'required_if:workspace_context.dari,monitoring'],
        'workspace_context.page' => 'nullable|integer|min:1',
        'workspace_context.jenis_filter' => 'nullable|in:OPR,PD,EDK,ELK',
    ]);

    // Validasi konteks monitoring SEBELUM menyimpan
    if (($navigation['workspace_context']['dari'] ?? null) === 'monitoring') {
        $transaction = $service->source($jenis, $source);
        $transaction->loadMissing('user');
        $companyId = (int) ($navigation['workspace_context']['company'] ?? 0);
        $userId = (int) ($navigation['workspace_context']['user'] ?? 0);

        abort_unless($companyId > 0 && $userId > 0, 403, 'Konteks monitoring tidak lengkap.');
        abort_unless((int) $transaction->user_id === $userId, 403, 'Konteks monitoring tidak sesuai dengan user transaksi.');
        abort_unless($transaction->user !== null && (int) $transaction->user->company_id === $companyId, 403, 'Konteks monitoring tidak sesuai dengan company user transaksi.');
    }

    $check = $service->store($request->user(), $jenis, $source, $request->all());

    if ($request->boolean('_workspace')) {
        $allowedContextKeys = ['pemakaian_periode', 'pemakaian_status', 'pemakaian_referensi', 'pemakaian_page', 'dari', 'company', 'user', 'page', 'jenis_filter'];
        $context = array_intersect_key($navigation['workspace_context'] ?? [], array_flip($allowedContextKeys));
        $params = ['jenis' => $jenis, 'source' => $source];
        foreach ($context as $key => $value) { if ($value === null || $value === '') continue; $params[$key] = $value; }
        if (array_key_exists('pemakaian_periode', $context)) $params['pemakaian_periode'] = $context['pemakaian_periode'] ?? '';

        if ($check instanceof BsUsageCheckDraft) {
            return redirect()->route('transaction-recap.show', $params + ['pemeriksaan' => 1])
                ->with('success', 'Draft pemeriksaan tersimpan. Draft belum menjadi hasil Finance pada dashboard dan laporan.');
        }
        return redirect()->to(route('transaction-recap.show', $params).'#hasil-finance')
            ->with('success', 'Pengecekan '.$check->document_no.' dikonfirmasi. Sisa Saldo BS dan Total Keluar Stok Kas mengikuti hasil Finance terakhir.');
    }

    if ($check instanceof BsUsageCheckDraft) {
        return redirect()->route('bs-usage-checks.create', [$jenis, $source])->with('success', 'Draft pemeriksaan tersimpan.');
    }
    return redirect()->route('bs-usage-checks.show', $check)->with('success', 'Pengecekan '.$check->document_no.' dikonfirmasi.');
}

    public function show(BsUsageCheck $check)
    {
        $check->load(['details', 'source', 'revisionOf']);
        $history = BsUsageCheck::where('jenis_transaksi', $check->jenis_transaksi)
            ->where('transaksi_pemakaian_id', $check->transaksi_pemakaian_id)->with('details')
            ->orderBy('id')->paginate(10)->withQueryString()->fragment('decision-history-title');

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
        // Saved links to the removed revision tab now show all statuses.
        if ($request->query('status_finance') === 'REVISION') {
            $request->query->set('status_finance', '');
        }
        if ($request->query('status_finance') === 'RECHECK') {
            $request->query->set('status_finance', 'PENDING');
        }

        // Tautan filter keputusan lama diarahkan ke status pengecekan.
if (in_array(
    $request->query('status_finance'),
    ['APPROVED', 'REJECTED'],
    true
)) {
    $request->query->set('status_finance', 'REVIEWED');
}

        $filters = $request->validate([
            'tampilan' => 'nullable|in:transaksi,rincian,draft',
            'tanggal_awal' => 'nullable|date_format:Y-m-d',
            'tanggal_akhir' => ['nullable', 'date_format:Y-m-d', ...($request->filled('tanggal_awal') ? ['after_or_equal:tanggal_awal'] : [])],
            'periode' => ['nullable', 'regex:/^\\d{4}-?(0[1-9]|1[0-2])$/D'],
            'jenis' => 'nullable|in:OPR,PD,EDK,ELK',
            'referensi' => 'nullable|string|max:64',
            'user_id' => 'nullable|integer|exists:user,id',
            'status_selisih' => 'nullable|in:sesuai,tidak_sesuai',
            'status_finance' => 'nullable|in:PENDING,REVIEWED',
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

        // ✅ PERUBAHAN 2: Hanya ambil transaksi yang sudah dikonfirmasi (VALIDATED)
        // Menggunakan 'source_status' karena ini adalah alias yang dibuat oleh BsUsageDashboardService
        $query->where('source_status', 'VALIDATED');

        foreach (['jenis' => 'jenis', 'user_id' => 'user_id', 'periode' => 'check_period', 'source_id' => 'source_id', 'jenis_source' => 'jenis'] as $filter => $column) {
            if (!empty($filters[$filter])) $query->where($column, $filters[$filter]);
        }
        if (!empty($filters['referensi'])) $query->where('no_referensi', 'like', '%'.$filters['referensi'].'%');
        if (!empty($filters['tanggal_awal'])) $query->where('tanggal_pengecekan', '>=', $filters['tanggal_awal']);
        if (!empty($filters['tanggal_akhir'])) $query->where('tanggal_pengecekan', '<', \Carbon\CarbonImmutable::parse($filters['tanggal_akhir'])->addDay()->toDateString());
        if (($filters['status_finance'] ?? '') === 'REVIEWED') {
    $query->whereNotNull('check_id')
        ->where('needs_recheck', 0);
} elseif (($filters['status_finance'] ?? '') === 'PENDING') {
    $query->where('finance_status', 'PENDING');
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
