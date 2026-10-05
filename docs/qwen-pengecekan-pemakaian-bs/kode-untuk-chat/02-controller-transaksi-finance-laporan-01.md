# Kode existing: controller-transaksi-finance-laporan, bagian 1

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: app/Http/Controllers/AccessManagementController.php

SHA-256: `7a274492a76a15f9a634d17be3398c88a174223c514bffbf25f9febb63a4c9b7`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\Role as MasterRole;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class AccessManagementController extends Controller
{
    public function index()
    {
        foreach (MasterRole::all() as $master) {
            if ($master->code) {
                SpatieRole::firstOrCreate(['name' => $master->code, 'guard_name' => 'web']);
            }
        }

        $roles = SpatieRole::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        $masterNames  = MasterRole::pluck('nama', 'code');
        $masterActive = MasterRole::pluck('is_active', 'code');

        // 🎯 BARIS KUNCI: hitung jumlah user per role master
        $userCounts = User::selectRaw('roles.code as code, COUNT(user.id) as total')
            ->join('roles', 'user.roles_id', '=', 'roles.id')
            ->groupBy('roles.code')
            ->pluck('total', 'code')
            ->toArray();

        return view('admin.access-management', compact(
            'roles',
            'permissions',
            'masterNames',
            'masterActive',
            'userCounts'
        ));
    }

    public function update(Request $request, $roleId)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:spatie_permissions,name',
        ]);

        $role = SpatieRole::findById($roleId);
        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', "Hak akses untuk role <strong>{$role->name}</strong> berhasil diperbarui!");
    }
}
~~~~

## FILE: app/Http/Controllers/ApprovalFinanceController.php

SHA-256: `3e52dad9fdae5f3cfb61b5ed131f8cd6fba8b793699f78e20a46b4515e05d843`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use App\Models\ApprovalBsFinance;
use App\Models\BsActivityLog;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalFinanceController extends Controller
{
    public function index(Request $request)
    {
        // 🛡️ Validasi divisi Finance secara DINAMIS (tidak hardcode code role)
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        $financeStatuses = ['APPROVED_ATASAN', 'DISBURSED', 'REJECTED_FINANCE'];
        $bulanExpr = "DATE_FORMAT(tanggal, '%Y-%m')";

        $availablePeriods = PengajuanBs::whereIn('status', $financeStatuses)
            ->selectRaw("$bulanExpr as bulan")
            ->distinct()
            ->orderBy('bulan', 'desc')
            ->pluck('bulan')->values();

        $currentPeriod = app(\App\Services\PeriodService::class)->current();
        $defaultPeriod = $availablePeriods->contains($currentPeriod)
            ? $currentPeriod
            : ($availablePeriods->first() ?? $currentPeriod);

        $selectedPeriod = $request->query('periode', $defaultPeriod);
        if (!$availablePeriods->contains($selectedPeriod)) {
            $selectedPeriod = $defaultPeriod;
        }

        $data = PengajuanBs::whereIn('status', $financeStatuses)
            ->whereRaw("$bulanExpr = ?", [$selectedPeriod])
            ->with([
                'user',
                'atasan',
                'logs' => fn ($q) => $q->where('activity_code', 'REJECTED_FINANCE')->latest(),
                'financeApprovals' => fn($q) => $q->with('financeUser')->latest(),
            ])
            ->orderByRaw("CASE status
                            WHEN 'APPROVED_ATASAN'  THEN 1
                            WHEN 'DISBURSED'        THEN 2
                            WHEN 'REJECTED_FINANCE' THEN 3
                          END")
            ->latest()
            ->get();

        return view('approval-finance.index', compact('data', 'availablePeriods', 'selectedPeriod'));
    }

    public function disburse(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        if ($bs->status !== 'APPROVED_ATASAN') {
            return back()->with('error', 'Pengajuan ini tidak bisa dicairkan (status bukan APPROVED_ATASAN).');
        }

        $request->validate([
            'nominal_approved' => 'required|numeric|decimal:0,2|min:1|max:' . $bs->nominal_pengajuan
        ]);

        DB::transaction(function () use ($request, $bs) {
            $tanggal = \App\Support\AppTime::now()->toDateString();
            $postingPeriod = app(\App\Services\PeriodService::class)->current();
            app(\App\Services\PeriodService::class)->lockOpen($postingPeriod);
            if (ApprovalBsFinance::where('pengajuan_bs_id', $bs->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['error' => 'BS ini sudah pernah dicairkan.']);
            }

            $docNo = app(DocumentNumberService::class)->next('CAIR', $tanggal);

            ApprovalBsFinance::create([
                'document_no'          => $docNo,
                'pengajuan_bs_id'      => $bs->id,
                'tanggal_cair'         => $tanggal,
                'finance_user_id'      => auth()->id(),
                'nominal_pengajuan_bs' => $bs->nominal_pengajuan,
                'nominal_sisa_bs'      => $bs->nominal_sisa_bs_snapshot,
                'nominal_approved'     => $request->nominal_approved,
                'periode'              => $postingPeriod,
            ]);

            $bs->update([
                'nominal_approved' => $request->nominal_approved,
                'status'           => 'DISBURSED',
            ]);

            BsActivityLog::record(
                $bs->id,
                'DISBURSED',
                'Dicairkan oleh Finance',
                'Pencairan uang telah dikonfirmasi oleh Finance.',
                $request->nominal_approved
            );

            \App\Services\BsNotifications::afterCommit(fn () => $bs->user?->notify(new \App\Notifications\BsDisbursed($bs, $request->nominal_approved)));

            app(\App\Services\BsCashFlowService::class)->recalculateSnapshot((int) $bs->user_id, $postingPeriod);
            app(\App\Services\StockCashFlowService::class)->recalculateSnapshot((int) $bs->user_id, $postingPeriod);
        });

        return back()->with('success', 'BS berhasil dicairkan.');
    }

    public function reject(Request $request, PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        if ($bs->status !== 'APPROVED_ATASAN') {
            return back()->with('error', 'Pengajuan ini tidak bisa ditolak (sudah diproses sebelumnya).');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $bs) {
            $bs->update([
                'status'           => 'REJECTED_FINANCE',
                'rejection_reason' => $request->rejection_reason,
            ]);

            BsActivityLog::record(
                $bs->id,
                'REJECTED_FINANCE',
                'Ditolak Finance',
                'Pencairan ditolak oleh ' . auth()->user()->nama . ' dengan alasan: ' . $request->rejection_reason
            );

            \App\Services\BsNotifications::afterCommit(fn () => $bs->user?->notify(new \App\Notifications\BsRejected($bs)));
            \App\Services\BsNotifications::afterCommit(fn () => $bs->atasan?->notify(new \App\Notifications\BsRejected($bs)));
        });

        return back()->with('success', 'Pencairan BS berhasil ditolak.');
    }

    public function detail(PengajuanBs $bs)
    {
        abort_unless(auth()->user()->can('akses.approval-finance'), 403, 'Anda tidak memiliki akses ke divisi Finance.');

        $bs->load([
            'user',
            'atasan',
            'members',
            'financeApprovals' => fn ($q) => $q->with('financeUser')->latest(),
        ]);

        $logs = BsActivityLog::where('pengajuan_bs_id', $bs->id)
            ->with('actor')
            ->latest()
            ->get();

        return view('approval-finance.detail', compact('bs', 'logs'));
    }
}

~~~~

## FILE: app/Http/Controllers/BsBalanceController.php

SHA-256: `9526945731fb91de83b15bdff61a07a5735b2a0914afcce7c454585ec35be397`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Services\BsCashFlowService;
use App\Services\PeriodService;
use App\Services\TransactionFundingService;
use App\Support\Money;
use Illuminate\Http\Request;

class BsBalanceController extends Controller
{
    public function balances(Request $request, TransactionFundingService $funding, BsCashFlowService $cash, PeriodService $periods)
    {
        $data = $request->validate([
            'module' => 'required|in:operasional_sales,perjalanan_dinas',
            'periode' => 'required|string', 'transaction_id' => 'nullable|integer',
        ]);
        $travel = $data['module'] === 'perjalanan_dinas';
        abort_unless($request->user()->can($travel ? 'akses.transaksi-perjalanan-dinas' : 'akses.transaksi-operasional'), 403);
        $inputter = (int) $request->user()->id;
        if (!empty($data['transaction_id'])) {
            $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
            $document = $model::findOrFail($data['transaction_id']);
            abort_unless($request->user()->canAccessTransaction($document), 403);
            $inputter = (int) $document->user_id;
        }
        $period = $periods->normalize($data['periode']);
        return response()->json(['owners' => $funding->owners($inputter, $travel)->map(function ($owner) use ($cash, $period) {
            $balance = $cash->calculate((int) $owner->id, $period);
            return ['id' => $owner->id, 'name' => $owner->nama, 'balance' => $cash->availableBalance($balance), 'opening_final' => $balance['opening_final']];
        })]);
    }

    public function show(Request $request, BsCashFlowService $cash, PeriodService $periods)
    {
        $request->validate(['periode' => 'sometimes|required|string']);
        $periode = $periods->normalize($request->query('periode', $periods->current()));
        $owner = (int) $request->user()->id;
        $snapshot = $cash->recalculateSnapshot($owner, $periode);
        $available = $cash->availableBalance($snapshot->toArray());
        $periodOptions = Periode::orderByDesc('periode')->pluck('periode')->push($periods->current())->push($periode)->unique()->sortDesc();
        $details = $cash->details($owner, $periode);
        $rows = collect();
        foreach ($details['masuk'] as $entry) {
            $rows->push($entry + ['incoming' => $entry['nominal'], 'outgoing' => '0.00', 'order' => 0]);
        }
        foreach ($details['keluar'] as $entry) {
            $rows->push($entry + ['incoming' => '0.00', 'outgoing' => $entry['nominal'], 'order' => 1]);
        }
        foreach ($details['penyesuaian'] as $entry) {
            $amount = Money::minor($entry['nominal']);
            $rows->push($entry + ['type' => 'Penyesuaian BS historis',
                'incoming' => Money::decimal(max(0, $amount)), 'outgoing' => Money::decimal(max(0, -$amount)), 'order' => 2]);
        }
        $balance = Money::minor($snapshot->saldo_awal);
        $rows = $rows->map(function ($entry) use ($periode) {
            $entry['date'] = empty($entry['date']) ? null : \Carbon\Carbon::parse($entry['date'])->toDateString();
            return $entry + ['sort_date' => $entry['date'] ?? $periode.'-01'];
        })->sortBy([['sort_date', 'asc'], ['order', 'asc'], ['description', 'asc']])->map(function ($entry) use (&$balance) {
            $balance += Money::minor($entry['incoming']) - Money::minor($entry['outgoing']);
            return $entry + ['balance' => Money::decimal($balance)];
        });
        return view('bs-balance.show', compact('periode', 'snapshot', 'available', 'periodOptions', 'rows'));
    }
}

~~~~

## FILE: app/Http/Controllers/BsUsageApprovalController.php

SHA-256: `2bc802a286f2397e141b85197a4f4b761fc135516c1b5616d3a8cf0494a8013a`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPerjalananDinasHeader;
use App\Services\BsCashFlowService;
use App\Services\BsValidationService;
use Illuminate\Http\Request;

class BsUsageApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => 'sometimes|in:PENDING_OWNER,REJECTED_OWNER,VALIDATED,ALL']);
        $status = $request->input('status', 'PENDING_OWNER');
        $query = TransaksiPerjalananDinasHeader::forBsOwner((int) $request->user()->id)
            ->where('user_id', '<>', $request->user()->id)->where('approval_version', '>', 0)
            ->with(['user', 'tujuanDinas'])->withSum('details', 'nominal');
        if ($status !== 'ALL') $query->where('status', $status);
        $requests = $query->orderByDesc('submitted_at')->paginate(15)->withQueryString();
        return view('bs-usage-approvals.index', compact('requests', 'status'));
    }

    public function show(int $id, BsCashFlowService $cash)
    {
        $transaksi = $this->document($id);
        $transaksi->load(['user', 'bsOwner', 'details.costCategory', 'tujuanDinas', 'approvalLogs.actor']);
        $balance = $cash->calculate($transaksi->bsCashOwnerId(), $transaksi->periode);
        return view('bs-usage-approvals.show', compact('transaksi', 'balance'));
    }

    public function approve(Request $request, int $id, BsValidationService $service)
    {
        $request->validate(['approval_version' => 'required|integer|min:1']);
        $service->approve($this->document($id), (int) $request->approval_version);
        return redirect()->route('bs-usage-approvals.show', $id)
            ->with('success', 'Pemakaian disetujui dan dikonfirmasi. Saldo BS telah berkurang sesuai rincian biaya.');
    }

    public function reject(Request $request, int $id, BsValidationService $service)
    {
        $request->validate(['approval_version' => 'required|integer|min:1', 'reason' => 'required|string|max:1000']);
        $service->reject($this->document($id), (int) $request->approval_version, $request->reason);
        return redirect()->route('bs-usage-approvals.show', $id)
            ->with('success', 'Pemakaian ditolak. Saldo BS tidak berkurang.');
    }

    private function document(int $id): TransaksiPerjalananDinasHeader
    {
        $document = TransaksiPerjalananDinasHeader::findOrFail($id);
        abort_unless($document->bsCashOwnerId() === (int) auth()->id() && $document->needsOwnerApproval(), 403);
        abort_unless($document->approval_version > 0, 404);
        return $document;
    }
}

~~~~

## FILE: app/Http/Controllers/LaporanBsController.php

SHA-256: `3584d4b9bf1e7b5e26f0e1294726f7ee447625970a4fe10379998747e88c675e`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBs;
use Illuminate\Http\Request;

class LaporanBsController extends Controller
{
    private function query(Request $request)
    {
        abort_unless(auth()->user()->canSeeAllTransactions(), 403);
        $request->validate(['periode' => 'nullable|string', 'status' => 'nullable|string']);
        return PengajuanBs::with('user')
            ->when($request->filled('periode'), fn ($q) => $q->where('periode', app(\App\Services\PeriodService::class)->normalize($request->periode)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('periode')->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $data = $this->query($request)->get();
        return view('laporan-bs.index', compact('data'));
    }

    public function settle(PengajuanBs $bs)
    {
        abort(403, 'Settlement per pengajuan tidak berlaku pada saldo BS gabungan.');
    }

    public function export(Request $request)
    {
        $headings = ['Doc No', 'User', 'Periode', 'Jenis Pengajuan', 'Nominal Pengajuan', 'Nominal Cair', 'Pengembalian Historis', 'Reimburse Historis', 'Status'];
        $rows = $this->query($request)->get()->map(fn ($bs) => [
            $bs->document_no, $bs->user?->nama ?? '', $bs->periode, $bs->tipe_label,
            (float) $bs->nominal_pengajuan, (float) $bs->nominal_approved,
            (float) $bs->nominal_pengembalian, (float) $bs->nominal_reimburse, $bs->status,
        ])->all();
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['E', 'F', 'G', 'H']),
            'laporan-pengajuan-bs-'.($request->periode ?: 'semua-periode').'.xlsx'
        );
    }
}

~~~~

## FILE: app/Http/Controllers/TransaksiOperasionalController.php

SHA-256: `44cea19d9b273987bbe4a58076c00586966ddb1d0d6c8a352f1513ecdad85d0a`

~~~~php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiOperasionalHeader; 
use App\Models\TransaksiOperasionalDetail; 
use App\Models\User;
use App\Models\CostCategory;

use App\Services\BonSementaraService; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use App\Traits\FiltersByPeriod;
use App\Services\PeriodService;
use App\Services\DocumentNumberService;
use Carbon\Carbon;

class TransaksiOperasionalController extends Controller
{
    use FiltersByPeriod;

    public function create()
    {
        $cost_categories = CostCategory::all();
        
        // 🎯 LOGIC: Ambil daftar bulan yang statusnya OPEN
        $dbPeriods = \App\Models\Periode::pluck('status', 'periode')->toArray(); // Sesuaikan 'period' jika nama kolom Anda 'periode'
        $currentMonth = \App\Support\AppTime::now()->format('Y-m');
        
        $openPeriods = [];
        foreach ($dbPeriods as $periode => $status) {
            if ($status === 'OPEN') {
                $openPeriods[] = $periode;
            }
        }
        
        // 🎯 Jika bulan saat ini belum ada record di database, default-nya dianggap OPEN
        if (!isset($dbPeriods[$currentMonth])) {
            $openPeriods[] = $currentMonth;
        }

        return view('transaksi_operasional.create', compact('cost_categories', 'openPeriods'));
    }
public function store(Request $request, BonSementaraService $bsService)
{
    // 1. Validasi Input Dasar
    $request->validate([
        'save_action' => 'sometimes|required|in:draft,confirm',
        'tanggal' => 'required|date',
        'description' => 'nullable|string|max:255',
        ...\App\Services\TransactionFundingService::rules(),
        'details' => 'required|array|min:1',
        'details.*.cost_category_id' => 'required|exists:cost_category,id',
        'details.*.remark' => 'required|string|max:255',
        'details.*.nominal' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
    ]);

    // 2. Validasi Periode (Pastikan periode OPEN)
    app(PeriodService::class)->assertOpen(substr($request->input('tanggal'), 0, 7));

    $activeUserId = Auth::id();

    // 3. 🎯 VALIDASI E2: 1 Transaksi per Tanggal per User (STRICT REJECT)
    $headerAda = TransaksiOperasionalHeader::where('tanggal', $request->tanggal)
                                           ->where('user_id', $activeUserId) 
                                           ->first();

    if ($headerAda) {
        return back()->withErrors([
            'tanggal' => 'Anda sudah memiliki transaksi Operasional Area Sales pada tanggal ' . $request->tanggal . '. Silakan buka transaksi yang sudah ada (ID: ' . $headerAda->transaksi_id . ') untuk menambahkan detail biaya.'
        ])->withInput();
    }
// 4. 🎯 VALIDASI BS (BON SEMENTARA)
$bsOwnerId = app(\App\Services\TransactionFundingService::class)->resolve($request, false);

    // 🎯 CATATAN: Validasi saldo BS TIDAK dilakukan saat draft
    // Saldo diperiksa saat konfirmasi, termasuk Simpan & Konfirmasi.

    try {
        DB::beginTransaction();

        $transaksi_id = app(DocumentNumberService::class)->next('OAS', $request->tanggal);
        
        $header = TransaksiOperasionalHeader::create([
            'transaksi_id' => $transaksi_id,
            'tanggal'      => $request->tanggal,
            'user_id'      => $activeUserId,
            'bs_owner_id' => $bsOwnerId,
            'periode'      => substr($request->tanggal, 0, 7), 
            'description'  => $request->description ?: null,
            'status'       => 'DRAFT', // 🎯 SIMPAN SEBAGAI DRAFT
        ]);

        foreach ($request->details as $detail) {
            TransaksiOperasionalDetail::create([
                'transaksi_id'     => $transaksi_id,
                'cost_category_id' => $detail['cost_category_id'],
                'remark'           => $detail['remark'],
                'nominal'          => $detail['nominal'],
            ]);
        }
        
        $confirm = $request->input('save_action', 'draft') === 'confirm';
        if ($confirm) {
            app(\App\Services\BsValidationService::class)->finalize($header);
        }

        DB::commit();
        
        return redirect()->route('transaksi_operasional.index')
            ->with('success', $confirm
                ? 'Transaksi Operasional (' . $transaksi_id . ') berhasil disimpan dan dikonfirmasi. Realisasi BS telah dibukukan.'
                : 'Draft Transaksi Operasional (' . $transaksi_id . ') berhasil disimpan. Klik Konfirmasi untuk membukukan pemakaian BS.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->withErrors(['error' => 'Transaksi Gagal: ' . $e->getMessage()])->withInput();
    }
}

   public function show($id)
{
    $transaksi = TransaksiOperasionalHeader::with(['user', 'details', 'bonSementara', 'bsOwner'])->findOrFail($id);

    if (!auth()->user()->canViewBsTransaction($transaksi)) {
        abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
    }

    $cost_categories = CostCategory::all();
    
    // 🎯 TAMBAHKAN INI: Ambil list BS untuk dropdown edit


    return view('transaksi_operasional.show', compact('transaksi', 'cost_categories'));
}


// 🎯 METHOD BARU: UPDATE TRANSAKSI (MODE EDIT)
public function update(Request $request, $id)
{
    $transaksi = TransaksiOperasionalHeader::with('details')->findOrFail($id);

    // 1. Cek Authorization
    if (!auth()->user()->canAccessTransaction($transaksi)) {
        abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
    }

    // 2. Cek Status
    if ($transaksi->status !== 'DRAFT') {
        return back()->withErrors(['error' => 'Hanya transaksi dengan status DRAFT yang dapat diedit.']);
    }

    // 3. Validasi Periode (Pastikan periode lama & periode dari tanggal baru masih OPEN)
    app(PeriodService::class)->assertOpen($transaksi->periode);
    if (substr($request->tanggal, 0, 7) !== $transaksi->periode) {
        app(PeriodService::class)->assertOpen(substr($request->tanggal, 0, 7));
    }

    // 4. Validasi Input
    $request->validate([
        'tanggal' => 'required|date',
        'description' => 'nullable|string|max:255',
        ...\App\Services\TransactionFundingService::rules(),
        'details' => 'nullable|array',
        'details.*.cost_category_id' => 'required_with:details|exists:cost_category,id',
        'details.*.remark' => 'required_with:details|string|max:255',
        'details.*.nominal' => 'required_with:details|numeric|decimal:0,2|min:0|max:9999999999999.99',
        'new_details' => 'nullable|array',
        'new_details.*.cost_category_id' => 'required_with:new_details|exists:cost_category,id',
        'new_details.*.remark' => 'required_with:new_details|string|max:255',
        'new_details.*.nominal' => 'required_with:new_details|numeric|decimal:0,2|min:0|max:9999999999999.99',
    ]);

    // Periksa hak penggunaan saldo sebelum mengubah draft.
    $bsOwnerId = app(\App\Services\TransactionFundingService::class)->resolve($request, false, $transaksi);

    // 6. Cek Duplikasi Tanggal (Kecuali transaksi ini sendiri)
    $headerAda = TransaksiOperasionalHeader::where('tanggal', $request->tanggal)
        ->where('user_id', auth()->id())
        ->where('id', '!=', $transaksi->id)
        ->first();
    if ($headerAda) {
        return back()->withErrors(['tanggal' => 'Anda sudah memiliki transaksi pada tanggal ' . $request->tanggal . ' (ID: ' . $headerAda->transaksi_id . ').'])->withInput();
    }

    DB::beginTransaction();
    try {
        // A. Update Header
        $transaksi->update([
            'tanggal' => $request->tanggal,
            'bs_owner_id' => $bsOwnerId,
            'periode' => substr($request->tanggal, 0, 7),
            'description' => $request->description ?: null,
        ]);

        // B. Update Existing Details
        if ($request->has('details')) {
            foreach ($request->details as $detailId => $data) {
                $detail = TransaksiOperasionalDetail::find($detailId);
                if ($detail && $detail->transaksi_id == $transaksi->transaksi_id) {
                    $detail->update([
                        'cost_category_id' => $data['cost_category_id'],
                        'remark' => $data['remark'],
                        'nominal' => $data['nominal'],
                    ]);
                }
            }
        }

        // C. Hapus Details yang ditandai (dicoret di UI)
        if ($request->has('delete_details') && is_array($request->delete_details)) {
            TransaksiOperasionalDetail::whereIn('id', $request->delete_details)
                ->where('transaksi_id', $transaksi->transaksi_id)
                ->delete();
        }

        // D. Tambah New Details (baris baru yang ditambah saat edit)
        if ($request->has('new_details')) {
            foreach ($request->new_details as $data) {
                TransaksiOperasionalDetail::create([
                    'transaksi_id' => $transaksi->transaksi_id,
                    'cost_category_id' => $data['cost_category_id'],
                    'remark' => $data['remark'],
                    'nominal' => $data['nominal'],
                ]);
            }
        }

        DB::commit();
        // 🎯 Kembali ke halaman detail (Mode View) setelah berhasil
        return redirect()->route('transaksi_operasional.show', $transaksi->id)->with('success', 'Transaksi berhasil diperbarui!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Gagal memperbarui transaksi: ' . $e->getMessage()])->withInput();
    }
}


    /**
 * 🎯 VALIDATE: Mengubah status DRAFT menjadi VALIDATED dan trigger cash flow
 */
    public function validateTransaction($id)
    {
        $transaksi = TransaksiOperasionalHeader::findOrFail($id);
        app(\App\Services\BsValidationService::class)->finalize($transaksi);
        return redirect()->route('transaksi_operasional.show', $transaksi->id)
            ->with('success', 'Transaksi berhasil dikonfirmasi dan saldo BS diperbarui.');
    }



       public function storeDetail(Request $request, $id, BonSementaraService $bsService)
{
    $request->validate([
        'details' => 'required|array|min:1',
        'details.*.cost_category_id' => 'required|exists:cost_category,id',
        'details.*.remark' => 'required|string|max:255',
        'details.*.nominal' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
    ]);

    $header = TransaksiOperasionalHeader::findOrFail($id);

    // 🎯 GUARD: Hanya bisa tambah detail jika masih DRAFT
    if ($header->status !== 'DRAFT') {
        return back()->withErrors(['error' => 'Tidak dapat menambah rincian. Transaksi sudah dikonfirmasi.']);
    }

    // 🎯 VALIDASI PERIODE: Pastikan periode transaksi TIDAK di-close
    app(PeriodService::class)->assertOpen($header->periode);

    try {
        DB::beginTransaction();

        if (!auth()->user()->canAccessTransaction($header)) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        // 🎯 CATATAN: Validasi saldo BS TIDAK dilakukan saat tambah detail draft
        // Validasi saldo dilakukan saat VALIDATE

        foreach ($request->details as $detail) {
            TransaksiOperasionalDetail::create([
                'transaksi_id'     => $header->transaksi_id,
                'cost_category_id' => $detail['cost_category_id'],
                'remark'           => $detail['remark'],
                'nominal'          => $detail['nominal'],
            ]);
        }

        DB::commit();
        
        // 🎯 JANGAN TRIGGER CASH FLOW SAAT TAMBAH DETAIL DRAFT!
        
        return redirect()->back()->with('success', 'Rincian biaya berhasil ditambahkan ke Draft Transaksi (' . $header->transaksi_id . '). Klik "Konfirmasi" untuk membukukan pemakaian BS.');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->withErrors(['error' => 'Gagal Menambah Rincian: ' . $e->getMessage()]);
    }
}
    public function index(Request $request)
    {
        $periode = $request->query('periode');

        $query = TransaksiOperasionalHeader::with('user')->orderBy('tanggal', 'desc');

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->visibleTo(auth()->user());
        }

        $this->filterPeriod($query, $periode);

        $transaksis = $query->paginate(10)->withQueryString();

        return view('transaksi_operasional.index', compact('transaksis', 'periode'));
    }

   public function destroy($id)
{
    $transaksi = TransaksiOperasionalHeader::with('bonSementara', 'details')->findOrFail($id);

    if (!auth()->user()->canAccessTransaction($transaksi)) {
        abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
    }

    // 🎯 GUARD: Hanya bisa hapus jika masih DRAFT
    if ($transaksi->status !== 'DRAFT') {
        return back()->withErrors(['error' => 'Tidak dapat menghapus transaksi yang sudah dikonfirmasi.']);
    }

    // Simpan data SEBELUM delete
    $periode = $transaksi->periode;

    DB::beginTransaction();

    try {
        $transaksi->details()->delete();
        $transaksi->delete();

        DB::commit();

        // 🎯 JANGAN TRIGGER CASH FLOW SAAT HAPUS DRAFT!
        // Karena draft tidak pernah mempengaruhi cash flow
        
        return redirect()->route('transaksi_operasional.index')
            ->with('success', 'Draft Transaksi Operasional berhasil dihapus!');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withErrors(['error' => 'Gagal menghapus transaksi: ' . $e->getMessage()]);
    }
}
    public function export(Request $request)
    {
        $periode = $request->filled('periode') ? app(\App\Services\PeriodService::class)->normalize($request->query('periode')) : null;

        $query = TransaksiOperasionalHeader::with(['user', 'details', 'bonSementara', 'bsOwner']);
        if (!auth()->user()->canSeeAllTransactions()) {
            $query->visibleTo(auth()->user());
        }
        if ($periode) $query->where('periode', $periode);

        $headers = $query->where('status', 'VALIDATED')->orderBy('tanggal')->get();
        $categories = \App\Models\CostCategory::pluck('nama', 'id');

        $headings = ['Transaksi ID', 'Tanggal', 'Periode', 'User', 'Pemilik Saldo BS', 'Cost Category', 'Remark', 'Nominal'];
        $rows = [];
        $total = 0;

        foreach ($headers as $h) {
            foreach ($h->details as $d) {
                $total += (float) $d->nominal;
                $rows[] = [
                    $h->transaksi_id,
                    \Carbon\Carbon::parse($h->tanggal)->format('d/m/Y'),
                    str_replace('-', '', $h->periode ?? ''),
                    $h->user->nama ?? '',
                    $h->bsOwner?->nama ?? $h->bonSementara?->user?->nama ?? 'Arsip tanpa BS',
                    $categories[$d->cost_category_id] ?? '',
                    $d->remark,
                    (float) $d->nominal,
                ];
            }
        }
        $rows[] = ['TOTAL', '', '', '', '', '', '', (float) $total];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['H']),
            'laporan-operasional-sales-' . ($periode ?: 'semua-periode') . '.xlsx'
        );
    }
}

~~~~

## FILE: app/Http/Controllers/TransaksiPerjalananDinasController.php

SHA-256: `a1f631c7b57cdd46122bd55533177501fafeba7dfde86d71e387e4ee18ea7579`

~~~~php
<?php

namespace App\Http\Controllers;

use App\Services\DocumentNumberService;

use Illuminate\Http\Request;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\TransaksiPerjalananDinasDetail;
use App\Models\User;
use App\Models\SettingPlafonPerjalananDinas;
use App\Models\CostCategory;
use App\Models\WilayahDistance;
use App\Models\Periode;
use App\Services\BonSementaraService;
use App\Services\PlafonService;
use App\Notifications\TransaksiOverPlafon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransaksiPerjalananDinasController extends Controller
{
    public function create()
    {
        $cost_categories = CostCategory::all();
        $dbPeriods = Periode::pluck('status', 'periode')->toArray();
        $currentMonth = \App\Support\AppTime::now()->format('Y-m');

        $openPeriods = [];
        foreach ($dbPeriods as $periode => $status) {
            if ($status === 'OPEN') {
                $openPeriods[] = $periode;
            }
        }

        if (!isset($dbPeriods[$currentMonth])) {
            $openPeriods[] = $currentMonth;
        }

        return view('transaksi_perjalanan_dinas.create', compact('cost_categories', 'openPeriods'));
    }

    public function getProvinsiByTeritory(Request $request)
    {
        $teritory = $request->get('teritory');
        $provinsis = \App\Models\Wilayah::where('teritory', $teritory)
            ->whereNull('parent_id')
            ->select('id', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
        return response()->json($provinsis);
    }

    public function getKotaByProvinsi(Request $request)
    {
        $provinsiId = $request->get('provinsi_id');
        $kotas = \App\Models\Wilayah::where('parent_id', $provinsiId)
            ->select('id', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
        return response()->json($kotas);
    }

    public function checkPlafonRealtime(Request $request, PlafonService $plafonService)
    {
        $request->validate([
            'tujuan_dinas_id' => 'required|exists:wilayah,id',
            'total_nominal'    => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
        ]);

        $user = Auth::user();
        $tujuanDinasId = $request->tujuan_dinas_id;
        $totalNominal = (float) $request->total_nominal;
        $tanggal = $request->tanggal ?? \App\Support\AppTime::now()->format('Y-m-d');

        $cek = $plafonService->checkPerjalananDinas($user, $tujuanDinasId, $tanggal, $totalNominal);
        return response()->json($cek);
    }

    public function getPlafonInfo(Request $request, PlafonService $plafonService)
    {
        $request->validate(['tujuan_dinas_id' => 'required|exists:wilayah,id']);

        $user = Auth::user();
        $tujuanDinasId = $request->tujuan_dinas_id;
        $tanggal = $request->tanggal ?? \App\Support\AppTime::now()->format('Y-m-d');

        $plafon = $plafonService->getPlafonPerjalananDinas($user, $tujuanDinasId, $tanggal);

        if ($plafon) {
            return response()->json([
                'found'         => true,
                'plafon_value'  => (float) $plafon->default_value,
                'description'   => $plafon->description,
                'tanggal_berlaku' => $plafon->tanggal_berlaku,
                'message'       => '✅ Plafon ditemukan: Rp ' . number_format($plafon->default_value, 0, ',', '.'),
            ]);
        }

        return response()->json([
            'found'         => false,
            'plafon_value'  => 0,
            'message'       => '⚠️ Plafon untuk rute & level ini belum disetting.',
        ]);
    }

    public function store(Request $request, BonSementaraService $bsService, PlafonService $plafonService)
    {
        $request->validate([
            'save_action' => 'sometimes|required|in:draft,confirm',
            'tanggal' => 'required|date',
            'description' => 'required|string|max:255',
            'tujuan_dinas_id' => 'required|exists:wilayah,id',
            'teritory' => 'required|in:Jawa,Luar Jawa',
            'jarak_km' => 'required|numeric|decimal:0,2|min:0|max:99999999.99',
            'durasi_jam' => 'required|numeric|min:0|max:24',
            ...\App\Services\TransactionFundingService::rules(),
            'details' => 'required|array|min:1',
            'details.*.cost_category_id' => 'required|exists:cost_category,id',
            'details.*.remark' => 'required|string|max:255',
            'details.*.nominal' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
        ]);

        $tujuanWilayah = \App\Models\Wilayah::find($request->tujuan_dinas_id);
        if ($tujuanWilayah && $tujuanWilayah->teritory !== $request->teritory) {
            return redirect()->back()
                ->withErrors(['tujuan_dinas_id' => '❌ Wilayah tujuan tidak sesuai dengan Territory yang dipilih!'])
                ->withInput();
        }

        $bsOwnerId = app(\App\Services\TransactionFundingService::class)->resolve($request, true);

        try {
            DB::beginTransaction();
            $activeUserId = Auth::id();
            $user = auth()->user();

            foreach ($request->details as $index => $detail) {
                $costCategory = CostCategory::find($detail['cost_category_id']);
                $categoryName = strtolower($costCategory->name ?? $costCategory->nama ?? $costCategory->description ?? '');

                if (str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku')) {
                    if ($request->jarak_km <= 200) {
                        DB::rollBack();
                        return redirect()->back()
                            ->withErrors([
                                'details.' . $index . '.cost_category_id' =>
                                ' Lumpsum HANYA untuk perjalanan > 200 KM. Jarak Anda: ' . $request->jarak_km . ' KM'
                            ])
                            ->withInput();
                    }
                }

                if (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi')) {
                    if ($request->durasi_jam <= 8) {
                        DB::rollBack();
                        return redirect()->back()
                            ->withErrors([
                                'details.' . $index . '.cost_category_id' =>
                                ' Hotel/Akomodasi HANYA untuk perjalanan > 8 jam. Durasi Anda: ' . $request->durasi_jam . ' jam'
                            ])
                            ->withInput();
                    }
                }
            }

            $header = TransaksiPerjalananDinasHeader::where('tanggal', $request->tanggal)
                ->where('user_id', $activeUserId)
                ->first();

            if ($header) {
                throw \Illuminate\Validation\ValidationException::withMessages(['tanggal' => 'Transaksi pada tanggal ini sudah ada. Buka transaksi tersebut untuk menambah rincian draft.']);
            } else {
                $transaksi_id = app(DocumentNumberService::class)->next('PD', $request->tanggal);
                $header = TransaksiPerjalananDinasHeader::create([
                    'transaksi_id'    => $transaksi_id,
                    'tanggal'         => $request->tanggal,
                    'user_id'         => $activeUserId,
                    'bs_owner_id' => $bsOwnerId,
                    'periode'         => app(\App\Services\PeriodService::class)->ofDate($request->tanggal),
                    'status'          => 'DRAFT',
                    'description'     => $request->description,
                    'tujuan_dinas_id' => $request->tujuan_dinas_id,
                    'jarak_km'        => $request->jarak_km,
                    'durasi_jam'      => $request->durasi_jam,
                ]);
                $pesan_sukses = 'Draft Perjalanan Dinas (' . $transaksi_id . ') disimpan. Buka detail untuk Konfirmasi.';
            }

            foreach ($request->details as $detail) {
                TransaksiPerjalananDinasDetail::create([
                    'transaksi_id'     => $transaksi_id,
                    'cost_category_id' => $detail['cost_category_id'],
                    'remark'           => $detail['remark'],
                    'nominal'          => $detail['nominal'],
                ]);
            }

            $header->refresh();
            $totalBaru = $header->details()->sum('nominal');

            $cek = $plafonService->checkPerjalananDinas($user, $header->tujuan_dinas_id, $header->tanggal, $totalBaru);
            $wasOver = (bool) $header->is_over_plafon;

            $header->update([
                'is_over_plafon' => $cek['is_over'] ? 1 : 0,
                'plafon_value'   => $cek['plafon_value'],
                'selisih_plafon' => $cek['selisih'],
            ]);

            if ($cek['is_over'] && !$wasOver && $user->atasan_user_id) {
                $atasan = User::find($user->atasan_user_id);
                \App\Services\BsNotifications::send($atasan, new TransaksiOverPlafon(
                    $transaksi_id . ' (Perjalanan Dinas)',
                    $totalBaru, $cek['plafon_value'], $cek['selisih'],
                    route('transaksi_perjalanan_dinas.show', $transaksi_id)
                ));
            }

            if ($request->input('save_action', 'draft') === 'confirm') {
                $status = app(\App\Services\BsValidationService::class)->finalize($header);
                $pesan_sukses = $status === 'PENDING_OWNER'
                    ? 'Permintaan pemakaian BS dikirim kepada pemohon. Saldo belum berkurang sampai disetujui.'
                    : 'Transaksi Perjalanan Dinas (' . $transaksi_id . ') berhasil disimpan dan dikonfirmasi. Realisasi BS telah dibukukan.';
            }

            DB::commit();
            return redirect()->route('transaksi_perjalanan_dinas.index')->with('success', $pesan_sukses);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Transaksi Gagal: ' . $e->getMessage()])->withInput();
        }
    }

    public function validateTransaction($id)
    {
        $transaksi = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();
        $status = app(\App\Services\BsValidationService::class)->finalize($transaksi);
        return redirect()->route('transaksi_perjalanan_dinas.show', $transaksi->transaksi_id)
            ->with('success', $status === 'PENDING_OWNER'
                ? 'Permintaan pemakaian BS dikirim kepada pemohon. Saldo belum berkurang sampai disetujui.'
                : 'Transaksi berhasil dikonfirmasi. Realisasi BS telah dibukukan.');
    }

    public function withdraw(Request $request, $id)
    {
        return $this->returnToDraft($request, $id, false);
    }

    public function revise(Request $request, $id)
    {
        return $this->returnToDraft($request, $id, true);
    }

    private function returnToDraft(Request $request, $id, bool $rejected)
    {
        $request->validate(['approval_version' => 'required|integer|min:1']);
        $transaksi = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();
        app(\App\Services\BsValidationService::class)->returnToDraft($transaksi, (int) $request->approval_version, $rejected);
        return redirect()->route('transaksi_perjalanan_dinas.show', $id)
            ->with('success', 'Transaksi kembali menjadi draft. Perbaiki rincian lalu ajukan kembali untuk persetujuan.');
    }

    public function index()
    {
        $query = TransaksiPerjalananDinasHeader::with(['user', 'tujuanDinas']);

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->visibleTo(auth()->user());
        }

        $transaksis = $query->orderBy('tanggal', 'desc')->paginate(10);
        return view('transaksi_perjalanan_dinas.index', compact('transaksis'));
    }

    public function show($id)
    {
        $transaksi = TransaksiPerjalananDinasHeader::with(['user', 'details', 'tujuanDinas.parent', 'bonSementara', 'bsOwner', 'approvalLogs.actor'])
            ->where('transaksi_id', $id)->firstOrFail();

        if (!auth()->user()->canViewBsTransaction($transaksi)) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        $cost_categories = CostCategory::all();

    

        $currentTerritory = $transaksi->tujuanDinas->teritory ?? 'Jawa';
        $provinces = \App\Models\Wilayah::where('teritory', $currentTerritory)->whereNull('parent_id')->get();

        $currentProvinceId = $transaksi->tujuanDinas->parent_id ?? null;
        $cities = [];
        if ($currentProvinceId) {
            $cities = \App\Models\Wilayah::where('parent_id', $currentProvinceId)->get();
        }

        return view('transaksi_perjalanan_dinas.show', compact(
            'transaksi', 'cost_categories', 'provinces', 'cities', 'currentTerritory', 'currentProvinceId'
        ));
    }

    // 🎯 PERBAIKAN: Tambah guard status DRAFT
    public function update(Request $request, $id, BonSementaraService $bsService, PlafonService $plafonService)
    {
        $transaksi = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();

        if (!auth()->user()->canAccessTransaction($transaksi)) {
            abort(403);
        }

        // 🎯 GUARD: Hanya DRAFT yang bisa di-edit
        if ($transaksi->status !== 'DRAFT') {
            return back()->withErrors(['error' => 'Hanya transaksi dengan status DRAFT yang dapat diedit. Transaksi ini sudah dikonfirmasi.']);
        }

        // 🎯 GUARD: Periode harus OPEN
        app(\App\Services\PeriodService::class)->assertOpen($transaksi->periode);

        $request->validate([
            'tanggal' => 'required|date',
            'description' => 'required|string|max:255',
            'teritory' => 'required|in:Jawa,Luar Jawa',
            'tujuan_dinas_id' => 'required|exists:wilayah,id',
            'jarak_km' => 'required|numeric|decimal:0,2|min:0|max:99999999.99',
            'durasi_jam' => 'required|numeric|min:0|max:24',
            ...\App\Services\TransactionFundingService::rules(),
            'details' => 'nullable|array',
            'details.*.cost_category_id' => 'required_with:details|exists:cost_category,id',
            'details.*.remark' => 'nullable|string|max:255',
            'details.*.nominal' => 'required_with:details|numeric|decimal:0,2|min:0|max:9999999999999.99',
            'new_details' => 'nullable|array',
            'new_details.*.cost_category_id' => 'required_with:new_details|exists:cost_category,id',
            'new_details.*.remark' => 'nullable|string|max:255',
            'new_details.*.nominal' => 'required_with:new_details|numeric|decimal:0,2|min:0|max:9999999999999.99',
        ]);

        $tujuanWilayah = \App\Models\Wilayah::find($request->tujuan_dinas_id);
        if ($tujuanWilayah && $tujuanWilayah->teritory !== $request->teritory) {
            return back()->withErrors(['tujuan_dinas_id' => 'Wilayah tujuan tidak sesuai dengan Territory yang dipilih.']);
        }

        $bsOwnerId = app(\App\Services\TransactionFundingService::class)->resolve($request, true, $transaksi);

        DB::beginTransaction();
        try {
            $transaksi->update([
                'tanggal' => $request->tanggal,
                'description' => $request->description,
                'tujuan_dinas_id' => $request->tujuan_dinas_id,
                'jarak_km' => $request->jarak_km,
                'durasi_jam' => $request->durasi_jam,
                'bs_owner_id' => $bsOwnerId,
                'periode' => \Carbon\Carbon::parse($request->tanggal)->format('Y-m'),
            ]);

            $validateRules = function($catId, $jarak, $durasi) {
                $cat = CostCategory::find($catId);
                $name = strtolower($cat->name ?? $cat->description ?? '');
                if ((str_contains($name, 'lumpsum') || str_contains($name, 'uang saku')) && $jarak <= 200) {
                    throw new \Exception("Lumpsum hanya untuk jarak > 200 KM.");
                }
                if ((str_contains($name, 'hotel') || str_contains($name, 'akomodasi')) && $durasi <= 8) {
                    throw new \Exception("Hotel hanya untuk durasi > 8 Jam.");
                }
            };

            if ($request->has('details')) {
                foreach ($request->details as $detailId => $data) {
                    $detail = TransaksiPerjalananDinasDetail::find($detailId);
                    if ($detail && $detail->transaksi_id == $transaksi->transaksi_id) {
                        $validateRules($data['cost_category_id'], $request->jarak_km, $request->durasi_jam);
                        $detail->update([
                            'cost_category_id' => $data['cost_category_id'],
                            'remark' => $data['remark'],
                            'nominal' => $data['nominal'],
                        ]);
                    }
                }
            }

            if ($request->has('delete_details') && is_array($request->delete_details)) {
                TransaksiPerjalananDinasDetail::whereIn('id', $request->delete_details)
                    ->where('transaksi_id', $transaksi->transaksi_id)
                    ->delete();
            }

            if ($request->has('new_details')) {
                foreach ($request->new_details as $data) {
                    $validateRules($data['cost_category_id'], $request->jarak_km, $request->durasi_jam);
                    TransaksiPerjalananDinasDetail::create([
                        'transaksi_id' => $transaksi->transaksi_id,
                        'cost_category_id' => $data['cost_category_id'],
                        'remark' => $data['remark'],
                        'nominal' => $data['nominal'],
                    ]);
                }
            }

            $transaksi->refresh();
            $totalBaru = $transaksi->details()->sum('nominal');
            $cek = $plafonService->checkPerjalananDinas($transaksi->user, $transaksi->tujuan_dinas_id, $transaksi->tanggal, $totalBaru);

            $transaksi->update([
                'is_over_plafon' => $cek['is_over'] ? 1 : 0,
                'plafon_value' => $cek['plafon_value'],
                'selisih_plafon' => $cek['selisih'],
            ]);

            DB::commit();
            return redirect()->route('transaksi_perjalanan_dinas.show', $transaksi->transaksi_id)
                ->with('success', 'Transaksi berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal update: ' . $e->getMessage()])->withInput();
        }
    }

    // 🎯 PERBAIKAN: Tambah guard status DRAFT
    public function storeDetail(Request $request, $id, BonSementaraService $bsService, PlafonService $plafonService)
    {
        $request->validate([
            'details' => 'required|array|min:1',
            'details.*.cost_category_id' => 'required|exists:cost_category,id',
            'details.*.remark' => 'required|string|max:255',
            'details.*.nominal' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
        ]);

        try {
            DB::beginTransaction();
            $header = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();

            if (!auth()->user()->canAccessTransaction($header)) {
                abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
            }

            // 🎯 GUARD: Hanya DRAFT yang bisa ditambah detail
            if ($header->status !== 'DRAFT') {
                return back()->withErrors(['error' => 'Tidak dapat menambah rincian. Transaksi sudah dikonfirmasi.']);
            }

            // 🎯 GUARD: Periode harus OPEN
            app(\App\Services\PeriodService::class)->assertOpen($header->periode);

            

            foreach ($request->details as $index => $detail) {
                $costCategory = CostCategory::find($detail['cost_category_id']);
                $categoryName = strtolower($costCategory->name ?? $costCategory->nama ?? $costCategory->description ?? '');

                if (str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku')) {
                    if ($header->jarak_km <= 200) {
                        DB::rollBack();
                        return redirect()->back()->withErrors([
                            'details.' . $index . '.cost_category_id' =>
                            '❌ Lumpsum HANYA untuk perjalanan > 200 KM. Jarak transaksi ini: ' . $header->jarak_km . ' KM'
                        ])->withInput();
                    }
                }

                if (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi')) {
                    if ($header->durasi_jam <= 8) {
                        DB::rollBack();
                        return redirect()->back()->withErrors([
                            'details.' . $index . '.cost_category_id' =>
                            '❌ Hotel/Akomodasi HANYA untuk perjalanan > 8 jam. Durasi transaksi ini: ' . $header->durasi_jam . ' jam'
                        ])->withInput();
                    }
                }
            }

            foreach ($request->details as $detail) {
                TransaksiPerjalananDinasDetail::create([
                    'transaksi_id'     => $header->transaksi_id,
                    'cost_category_id' => $detail['cost_category_id'],
                    'remark'           => $detail['remark'],
                    'nominal'          => $detail['nominal'],
                ]);
            }

            $header->refresh();
            $totalBaru = $header->details()->sum('nominal');

            $cek = $plafonService->checkPerjalananDinas($header->user, $header->tujuan_dinas_id, $header->tanggal, $totalBaru);
            $wasOver = (bool) $header->is_over_plafon;

            $header->update([
                'is_over_plafon' => $cek['is_over'] ? 1 : 0,
                'plafon_value'   => $cek['plafon_value'],
                'selisih_plafon' => $cek['selisih'],
            ]);

            if ($cek['is_over'] && !$wasOver && $header->user->atasan_user_id) {
                $atasan = User::find($header->user->atasan_user_id);
                \App\Services\BsNotifications::send($atasan, new TransaksiOverPlafon(
                    $header->transaksi_id . ' (Perjalanan Dinas)',
                    $totalBaru, $cek['plafon_value'], $cek['selisih'],
                    route('transaksi_perjalanan_dinas.show', $header->transaksi_id)
                ));
            }

            DB::commit();
            return redirect()->back()->with('success', 'Rincian biaya berhasil ditambahkan ke Transaksi (' . $id . ')!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Gagal Menambah Rincian: ' . $e->getMessage()]);
        }
    }

    // 🎯 PERBAIKAN: Tambah guard status DRAFT
    public function destroy($id)
    {
        $transaksi = TransaksiPerjalananDinasHeader::where('transaksi_id', $id)->firstOrFail();

        if (!auth()->user()->canAccessTransaction($transaksi)) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        // 🎯 GUARD: Hanya DRAFT yang bisa dihapus
        if ($transaksi->status !== 'DRAFT') {
            return back()->withErrors(['error' => 'Tidak dapat menghapus transaksi yang sudah dikonfirmasi.']);
        }

            $periode = $transaksi->periode;

        $transaksi->details()->delete();
        $transaksi->delete();

        return redirect()->route('transaksi_perjalanan_dinas.index')
            ->with('success', 'Transaksi Perjalanan Dinas berhasil dihapus!');
    }

    public function getAutoNominal(Request $request, PlafonService $plafonService)
    {
        $costCategoryId = $request->cost_category_id;
        $jarakKm = (float) $request->jarak_km;
        $durasiJam = (float) $request->durasi_jam;
        $tujuanDinasId = $request->tujuan_dinas_id;
        $user = Auth::user();

        $costCategory = CostCategory::find($costCategoryId);
        $categoryName = strtolower($costCategory->name ?? $costCategory->description ?? '');

        if (str_contains($categoryName, 'lumpsum') || str_contains($categoryName, 'uang saku')) {
            if ($jarakKm <= 200) {
                return response()->json([
                    'nominal' => 0, 'is_valid' => false, 'is_readonly' => false,
                    'message' => "❌ Lumpsum hanya untuk jarak > 200 KM. Jarak Anda: {$jarakKm} KM"
                ]);
            }
        } elseif (str_contains($categoryName, 'hotel') || str_contains($categoryName, 'akomodasi') || str_contains($categoryName, 'menginap')) {
            if ($durasiJam <= 8) {
                return response()->json([
                    'nominal' => 0, 'is_valid' => false, 'is_readonly' => false,
                    'message' => "❌ Hotel hanya untuk durasi > 8 Jam. Durasi Anda: {$durasiJam} Jam"
                ]);
            }
        }

        if ($tujuanDinasId) {
            $plafon = $plafonService->getPlafonPerjalananDinas($user, $tujuanDinasId, now());
            if ($plafon) {
                return response()->json([
                    'nominal' => 0, 'is_valid' => true, 'is_readonly' => false,
                    'plafon_total' => (float) $plafon->default_value,
                    'message' => "ℹ️ Silakan input nominal manual. Plafon Total Perjalanan: Rp " . number_format($plafon->default_value, 0, ',', '.')
                ]);
            }
        }

        return response()->json([
            'nominal' => 0, 'is_valid' => true, 'is_readonly' => false,
            'message' => "ℹ️ Kategori Umum. Silakan input nominal secara manual."
        ]);
    }

    public function export(Request $request)
    {
        $periode = $request->get('periode');
        $query = TransaksiPerjalananDinasHeader::with(['user', 'details', 'tujuanDinas', 'bonSementara', 'bsOwner']);

        if (!auth()->user()->canSeeAllTransactions()) {
            $query->visibleTo(auth()->user());
        }

        if ($periode) $query->where('periode', $periode);

        $headers = $query->where('status', 'VALIDATED')->orderBy('tanggal')->get();
        $categories = \App\Models\CostCategory::pluck('nama', 'id');

        $headings = ['Transaksi ID', 'Tanggal', 'Periode', 'User', 'Tujuan Dinas', 'Jarak (KM)', 'Durasi (Jam)', 'Pemilik Saldo BS', 'Cost Category', 'Remark', 'Nominal', 'Plafon', 'Status Plafon'];
        $rows = [];
        $total = 0;

        foreach ($headers as $h) {
            foreach ($h->details as $d) {
                $total += (float) $d->nominal;
                $rows[] = [
                    $h->transaksi_id,
                    \Carbon\Carbon::parse($h->tanggal)->format('d/m/Y'),
                    str_replace('-', '', $h->periode ?? ''),
                    $h->user->nama ?? '',
                    $h->tujuanDinas->nama ?? '',
                    (float) $h->jarak_km,
                    (float) $h->durasi_jam,
                    $h->bsOwner?->nama ?? $h->bonSementara?->user?->nama ?? 'Arsip tanpa BS',
                    $categories[$d->cost_category_id] ?? '',
                    $d->remark,
                    (float) $d->nominal,
                    $h->plafon_value ? (float) $h->plafon_value : '',
                    $h->is_over_plafon ? 'OVER' : ($h->plafon_value ? 'SESUAI' : '-'),
                ];
            }
        }
        $rows[] = ['TOTAL', '', '', '', '', '', '', '', '', '', (float) $total, '', ''];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanTableExport($headings, $rows, ['K', 'L']),
            'laporan-perjalanan-dinas-' . ($periode ?: 'semua-periode') . '.xlsx'
        );
    }

    // 🎯 PERBAIKAN: Tambah guard status header
    public function destroyDetail(Request $request, $detailId)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $detail = TransaksiPerjalananDinasDetail::with(['header', 'costCategory'])->findOrFail($detailId);
        $header = $detail->header;
        $user = auth()->user();

        if (!$user->canAccessTransaction($header)) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk menghapus detail ini.'], 403);
        }

        // 🎯 GUARD: Hanya DRAFT yang bisa dihapus detailnya
        if ($header->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Gagal: Transaksi sudah dikonfirmasi. Detail tidak dapat dihapus.'
            ], 403);
        }

        $periodService = app(\App\Services\PeriodService::class);
        if ($periodService->statusOf($header->periode) !== 'OPEN') {
            return response()->json([
                'message' => 'Gagal: Periode transaksi ini sudah CLOSE. Detail tidak dapat dihapus.'
            ], 403);
        }

        

        try {
            DB::beginTransaction();

            $oldValue = [
                'cost_category' => $detail->costCategory->name ?? $detail->cost_category_id,
                'remark'        => $detail->remark,
                'nominal'       => $detail->nominal,
            ];

            $detail->delete();

            $newTotal = $header->details()->sum('nominal');
            $plafonService = app(PlafonService::class);
            $cek = $plafonService->checkPerjalananDinas($header->user, $header->tujuan_dinas_id, $header->tanggal, $newTotal);

            $header->update([
                'is_over_plafon' => $cek['is_over'] ? 1 : 0,
                'plafon_value'   => $cek['plafon_value'] ?? 0,
                'selisih_plafon' => $cek['selisih'] ?? 0,
            ]);

            \App\Models\AuditLog::create([
                'user_id'    => $user->id,
                'action'     => 'DELETE_DETAIL',
                'table_name' => 'transaksi_perjalanan_dinas_detail',
                'record_id'  => $detail->id,
                'reason'     => $request->reason,
                'old_value'  => json_encode($oldValue),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detail berhasil dihapus. Total draft telah diperbarui.',
                'new_total' => $newTotal,
                'is_over' => $cek['is_over'],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal hapus detail transaksi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus detail: ' . $e->getMessage()], 500);
        }
    }
}

~~~~
