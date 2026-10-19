<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\CostCategoryController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\EventHourGroupController;
use App\Http\Controllers\SettingPlafonPerjalananDinasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeekdayGroupController;
use App\Http\Controllers\PlafonEventDalamKotaController;
use App\Http\Controllers\PlafonEventLuarKotaController;
use App\Http\Controllers\TransaksiOperasionalController;
use App\Http\Controllers\TransaksiPerjalananDinasController;
use App\Http\Controllers\PerongkosanEventDalamKotaController;
use App\Http\Controllers\PerongkosanEventLuarKotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanBsController;
use App\Http\Controllers\ApprovalAtasanController;
use App\Http\Controllers\ApprovalFinanceController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LaporanBsController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\AccessManagementController;
use App\Http\Controllers\FinanceMonitoringController;

Route::get('/', function () {
    return view('welcome');
});

// ==========================================
// 1. BLOK TAMU (Hanya untuk yang BELUM Login)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('proses_login');
});

// ==========================================
// 2. BLOK KARYAWAN UMUM (Harus Login)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/rincian-biaya/{type}/{detail}/foto', [\App\Http\Controllers\TransactionDetailPhotoController::class, 'show'])
        ->whereIn('type', ['OPR', 'PD'])->whereNumber('detail')->name('transaction-detail-photo.show');
    Route::get('/rincian-pemeriksaan/{detail}/foto', [\App\Http\Controllers\TransactionDetailPhotoController::class, 'history'])
        ->whereNumber('detail')->name('transaction-detail-photo.history');

    Route::middleware('permission:akses.approval-finance')
        ->prefix('pengecekan-pemakaian-bs')->name('bs-usage-checks.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BsUsageCheckController::class, 'index'])->name('index');
            Route::get('/laporan', [\App\Http\Controllers\BsUsageCheckController::class, 'report'])->name('report');
            Route::get('/hasil/{check}', [\App\Http\Controllers\BsUsageCheckController::class, 'show'])->whereNumber('check')->name('show');
            Route::get('/{jenis}/{source}/create', [\App\Http\Controllers\BsUsageCheckController::class, 'create'])
                ->whereIn('jenis', ['OPR', 'PD', 'EDK', 'ELK'])->whereNumber('source')->name('create');
            Route::post('/{jenis}/{source}', [\App\Http\Controllers\BsUsageCheckController::class, 'store'])
                ->whereIn('jenis', ['OPR', 'PD', 'EDK', 'ELK'])->whereNumber('source')->name('store');
        });

    Route::get('/api/bs-balance', [\App\Http\Controllers\BsBalanceController::class, 'balances'])->name('bs-balance.balances');

    Route::middleware(['permission:akses.pengajuan-bs|akses.transaksi-perjalanan-dinas|akses.cash-flow', 'bs.atomic'])
        ->prefix('persetujuan-pemakaian-bs')->name('bs-usage-approvals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BsUsageApprovalController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\BsUsageApprovalController::class, 'show'])->whereNumber('id')->name('show');
            Route::post('/{id}/approve', [\App\Http\Controllers\BsUsageApprovalController::class, 'approve'])->whereNumber('id')->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\BsUsageApprovalController::class, 'reject'])->whereNumber('id')->name('reject');
        });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        if (!auth()->user()->canAccessPortal()) {
            return redirect()->route('dashboard.transaksi');
        }

        return view('dashboard.portal');
    })->name('dashboard');

    Route::get('/dashboard/transaksi', function (Request $request) {
        if ($request->filled('pemakaian_jenis')) {
            return app(\App\Http\Controllers\TransactionRecapController::class)
                ->index($request, app(\App\Services\BsUsageDashboardService::class));
        }
        if (!$request->filled('pemakaian_periode')) {
    $request->query->set(
        'pemakaian_periode',
        app(\App\Services\PeriodService::class)->current()
    );
}
        $dashboard = app(\App\Services\BsUsageDashboardService::class);
        $dashboard->filters($request);
        // Dashboard totals use only the period; list filters belong to the history pages.
        foreach (['pemakaian_referensi', 'pemakaian_status', 'pemakaian_page', 'tampilan'] as $filter) {
            $request->query->remove($filter);
        }
        $bsUsageDashboard = $dashboard->data($request);
        $bsUsageDashboard['funding'] = app(\App\Services\BsFundingRecapService::class)->summary($request->user(), $bsUsageDashboard['filters']);

        $selectedPeriod = $bsUsageDashboard['filters']['pemakaian_periode'];
        $cashSnapshot = app(\App\Services\StockCashFlowService::class)
            ->dashboardSummary((int) $request->user()->id, $selectedPeriod, $request->user()->can('akses.cash-flow'));

        return view('dashboard.transaksi', compact('bsUsageDashboard', 'selectedPeriod', 'cashSnapshot'));
    })->name('dashboard.transaksi');

    Route::get('/rekap-bs', [\App\Http\Controllers\BsFundingRecapController::class, 'index'])->name('transaction-recap.funding');

    Route::get('/laporan-transaksi', [\App\Http\Controllers\TransactionRecapController::class, 'index'])->name('transaction-recap.index');
    Route::get('/laporan-transaksi/modul/{jenis}', [\App\Http\Controllers\TransactionRecapController::class, 'module'])
        ->whereIn('jenis', ['OPR', 'PD', 'EDK', 'ELK'])->name('transaction-recap.module');
    Route::get('/laporan-transaksi/{jenis}/{source}', [\App\Http\Controllers\TransactionRecapController::class, 'show'])
        ->whereIn('jenis', ['OPR', 'PD', 'EDK', 'ELK'])->whereNumber('source')->name('transaction-recap.show');
    Route::post('/konfirmasi-event/{jenis}/{source}', [\App\Http\Controllers\TransactionRecapController::class, 'confirmEvent'])
        ->whereIn('jenis', ['EDK', 'ELK'])->whereNumber('source')->name('transaction-recap.confirm-event');
    Route::post('/perbaiki-pengajuan/{jenis}/{source}', [\App\Http\Controllers\TransactionRecapController::class, 'correct'])
        ->whereIn('jenis', ['OPR', 'PD'])->whereNumber('source')->name('transaction-recap.correct');

    Route::get('/api/periode/status', function (Request $request) {
        $periode = $request->query('periode', now()->format('Y-m'));
        $status  = app(\App\Services\PeriodService::class)->statusOf($periode);

        return response()->json([
            'periode' => $periode,
            'status'  => $status,
            'open'    => $status === 'OPEN',
        ]);
    });

    Route::get('/api/wilayah-provinsi', [TransaksiPerjalananDinasController::class, 'getProvinsiByTeritory'])->name('api.wilayah.provinsi');
    Route::get('/api/wilayah-kota', [TransaksiPerjalananDinasController::class, 'getKotaByProvinsi'])->name('api.wilayah.kota');
    Route::get('/api/event-luar-wilayah-provinsi', [PerongkosanEventLuarKotaController::class, 'getProvinsiByTeritory'])->name('api.event_luar.wilayah.provinsi');
    Route::get('/api/event-luar-wilayah-kota', [PerongkosanEventLuarKotaController::class, 'getKotaByProvinsi'])->name('api.event_luar.wilayah.kota');
    Route::get('/api/plafon-wilayah-provinsi', [SettingPlafonPerjalananDinasController::class, 'getProvinsiByTeritory'])->name('api.plafon.wilayah.provinsi');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');

    // ==========================================
    // MODUL PENGAJUAN BS (permission: akses.pengajuan-bs)
    // ==========================================
    // Access is checked per submission for applicants, supervisors, and Finance.
    Route::get('/pengajuan-bs/{bs}/lampiran', \App\Http\Controllers\PengajuanBsAttachmentController::class)
        ->whereNumber('bs')->name('pengajuan-bs.attachment');

    Route::middleware(['permission:akses.pengajuan-bs', 'bs.atomic'])->group(function () {
        Route::get('/pengajuan-bs/export', [PengajuanBsController::class, 'export'])->name('pengajuan-bs.export');

        Route::resource('pengajuan-bs', PengajuanBsController::class)
            ->except(['show', 'edit', 'update', 'destroy', 'store']);

        Route::get('/pengajuan-bs/{bs}', [PengajuanBsController::class, 'show'])->name('pengajuan-bs.show');

        Route::post('/pengajuan-bs', [PengajuanBsController::class, 'store'])
            ->name('pengajuan-bs.store')
            ->middleware(['company.active', 'atasan.active']);
    });

    // ==========================================
    // MODUL TRANSAKSI OPERASIONAL (permission: akses.transaksi-operasional)
    // ==========================================
    Route::middleware(['permission:akses.transaksi-operasional', 'bs.atomic'])->group(function () {
        Route::get('/transaksi-operasional/export', [TransaksiOperasionalController::class, 'export'])->name('transaksi_operasional.export');

        Route::resource('transaksi-operasional', TransaksiOperasionalController::class)
            ->names('transaksi_operasional')
            ->except(['store', 'update']);

        Route::post('/transaksi-operasional', [TransaksiOperasionalController::class, 'store'])
            ->name('transaksi_operasional.store')
            ->middleware(['company.active', 'atasan.active']);

        Route::put('/transaksi-operasional/{transaksi_operasional}', [TransaksiOperasionalController::class, 'update'])
            ->name('transaksi_operasional.update')
            ->middleware(['company.active', 'atasan.active']);

        Route::post('/transaksi-operasional/{id}/add-detail', [TransaksiOperasionalController::class, 'storeDetail'])
            ->name('transaksi_operasional.store_detail')
            ->middleware(['company.active', 'atasan.active']);

        Route::post('/transaksi-operasional/{id}/validate', [TransaksiOperasionalController::class, 'validateTransaction'])
            ->name('transaksi_operasional.validate')
            ->middleware(['company.active', 'atasan.active']);
    });

    // ==========================================
    // MODUL TRANSAKSI PERJALANAN DINAS (permission: akses.transaksi-perjalanan-dinas)
    // ==========================================
    Route::middleware(['permission:akses.transaksi-perjalanan-dinas', 'bs.atomic'])->group(function () {
        Route::get('/transaksi-perjalanan-dinas/export', [TransaksiPerjalananDinasController::class, 'export'])->name('transaksi_perjalanan_dinas.export');

        Route::resource('transaksi-perjalanan-dinas', TransaksiPerjalananDinasController::class)
            ->parameters(['transaksi-perjalanan-dinas' => 'transaksi_perjalanan_dinas'])
            ->names('transaksi_perjalanan_dinas')
            ->except(['store', 'update']);

        Route::post('/transaksi-perjalanan-dinas', [TransaksiPerjalananDinasController::class, 'store'])
            ->name('transaksi_perjalanan_dinas.store')
            ->middleware(['company.active', 'atasan.active']);

        Route::put('/transaksi-perjalanan-dinas/{transaksi_perjalanan_dinas}', [TransaksiPerjalananDinasController::class, 'update'])
            ->name('transaksi_perjalanan_dinas.update')
            ->middleware(['company.active', 'atasan.active']);

        Route::delete('/transaksi-perjalanan-dinas/detail/{id}', [TransaksiPerjalananDinasController::class, 'destroyDetail'])
            ->name('transaksi_perjalanan_dinas.destroy_detail')
            ->middleware(['company.active', 'atasan.active']);

        Route::post('/transaksi-perjalanan-dinas/{id}/validate', [TransaksiPerjalananDinasController::class, 'validateTransaction'])
            ->name('transaksi_perjalanan_dinas.validate')
            ->middleware(['company.active', 'atasan.active']);

        Route::post('/transaksi-perjalanan-dinas/{id}/withdraw', [TransaksiPerjalananDinasController::class, 'withdraw'])
            ->name('transaksi_perjalanan_dinas.withdraw');
        Route::post('/transaksi-perjalanan-dinas/{id}/revise', [TransaksiPerjalananDinasController::class, 'revise'])
            ->name('transaksi_perjalanan_dinas.revise');

        Route::post('/transaksi-perjalanan-dinas/{id}/add-detail', [TransaksiPerjalananDinasController::class, 'storeDetail'])
            ->name('transaksi_perjalanan_dinas.store_detail')
            ->middleware(['company.active', 'atasan.active']);
    });

    Route::post('/api/calculate-cost-nominal', [TransaksiPerjalananDinasController::class, 'getAutoNominal'])->name('api.calculate_cost_nominal');

    // ==========================================
    // MODUL EVENT DALAM KOTA (permission: akses.event-dalam-kota)
    // ==========================================
    Route::middleware(['permission:akses.event-dalam-kota', 'event.atomic'])->group(function () {
        Route::get('/event-dalam-kota/export', [PerongkosanEventDalamKotaController::class, 'export'])->name('perongkosan_event_dalam_kota.export');

        Route::resource('event-dalam-kota', PerongkosanEventDalamKotaController::class)
            ->names('perongkosan_event_dalam_kota')
            ->except(['store', 'update']);

        Route::post('/event-dalam-kota', [PerongkosanEventDalamKotaController::class, 'store'])
            ->name('perongkosan_event_dalam_kota.store')
            ->middleware(['company.active', 'atasan.active']);

        Route::put('/event-dalam-kota/{event_dalam_kota}', [PerongkosanEventDalamKotaController::class, 'update'])
            ->name('perongkosan_event_dalam_kota.update')
            ->middleware(['company.active', 'atasan.active']);
    });

    // ==========================================
    // MODUL EVENT LUAR KOTA (permission: akses.event-luar-kota)
    // ==========================================
    Route::middleware(['permission:akses.event-luar-kota', 'event.atomic'])->group(function () {
        Route::get('/event-luar-kota/export', [PerongkosanEventLuarKotaController::class, 'export'])->name('perongkosan_event_luar_kota.export');

        Route::resource('event-luar-kota', PerongkosanEventLuarKotaController::class)
            ->names('perongkosan_event_luar_kota')
            ->except(['store', 'update']);

        Route::post('/event-luar-kota', [PerongkosanEventLuarKotaController::class, 'store'])
            ->name('perongkosan_event_luar_kota.store')
            ->middleware(['company.active', 'atasan.active']);

        Route::put('/event-luar-kota/{event_luar_kota}', [PerongkosanEventLuarKotaController::class, 'update'])
            ->name('perongkosan_event_luar_kota.update')
            ->middleware(['company.active', 'atasan.active']);
    });

    // ==========================================
    // APPROVAL ATASAN (dikunci RELASI atasan, bukan role)
    // ==========================================
    Route::middleware(['can:approval-atasan', 'bs.atomic'])->group(function () {
        Route::prefix('approval-atasan')->name('approval-atasan.')->group(function () {
            Route::get('/', [ApprovalAtasanController::class, 'index'])->name('index');
            Route::get('/{bs}/detail', [ApprovalAtasanController::class, 'detail'])->name('detail');
            Route::post('/batch-approve', [ApprovalAtasanController::class, 'batchApprove'])->name('batch-approve');
            Route::post('/batch-reject', [ApprovalAtasanController::class, 'batchReject'])->name('batch-reject');
            Route::post('/{bs}/approve', [ApprovalAtasanController::class, 'approve'])->name('approve');
            Route::post('/{bs}/reject', [ApprovalAtasanController::class, 'reject'])->name('reject');
        });
    });

       // ==========================================
    // APPROVAL FINANCE (permission divisi Finance)
    // ==========================================
    Route::middleware(['permission:akses.approval-finance', 'bs.atomic'])->group(function () {
        Route::prefix('approval-finance')->name('approval-finance.')->group(function () {
            Route::get('/', [ApprovalFinanceController::class, 'index'])->name('index');
            Route::get('/{bs}/detail', [ApprovalFinanceController::class, 'detail'])->name('detail');
            Route::post('/batch-disburse', [ApprovalFinanceController::class, 'batchDisburse'])->name('batch-disburse');
            Route::post('/{bs}/disburse', [ApprovalFinanceController::class, 'disburse'])->name('disburse');
            Route::post('/{bs}/reject', [ApprovalFinanceController::class, 'reject'])->name('reject');
        });
    });

    // ==========================================
    // CASH FLOW
    // ==========================================
    Route::middleware(['permission:akses.cash-flow'])->group(function () {
        Route::get('/cash-flow', [CashFlowController::class, 'index'])->name('cash-flow.index');
        Route::get('/cash-flow/export', [CashFlowController::class, 'export'])->name('cash-flow.export');
        Route::get('/cash-flow/{periode}/export', [CashFlowController::class, 'exportPeriod'])->name('cash-flow.export-period');
        Route::get('/cash-flow/{periode}', [CashFlowController::class, 'show'])->name('cash-flow.show');

        /* ==========================================================
           🆕 HTMX PARTIAL: Pagination transaksi tanpa reload halaman
           Dipanggil oleh link paginator di HTMX (hx-get),
           mengembalikan hanya potongan HTML section Uang Masuk/Keluar.
        ========================================================== */
        Route::get('/cash-flow/{periode}/transactions', [CashFlowController::class, 'transactionsPartial'])
            ->name('cash-flow.transactions-partial');
    });

    // NOTIFIKASI
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // ==========================================
    // LAPORAN PERTANGGUNGJAWABAN BS
    // ==========================================
    Route::middleware(['permission:akses.laporan-bs'])->group(function () {
        Route::get('/laporan-bs', [LaporanBsController::class, 'index'])->name('laporan-bs.index');
        Route::get('/laporan-bs/export', [LaporanBsController::class, 'export'])->name('laporan-bs.export');
        Route::post('/laporan-bs/{bs}/settle', [LaporanBsController::class, 'settle'])->name('laporan-bs.settle');
    });

    // API UNTUK INFO PLAFON
    Route::post('/api/get-plafon-info', [TransaksiPerjalananDinasController::class, 'getPlafonInfo'])->name('api.get_plafon_info');
    Route::post('/api/check-plafon-realtime', [TransaksiPerjalananDinasController::class, 'checkPlafonRealtime'])->name('api.check_plafon_realtime');
    Route::post('/api/event-dalam-kota/plafon-info', [PerongkosanEventDalamKotaController::class, 'getPlafonInfo'])->name('api.event_dalam_kota.plafon_info');
    Route::post('/api/event-luar-kota/plafon-info', [PerongkosanEventLuarKotaController::class, 'getPlafonInfo'])->name('api.event_luar_kota.plafon_info');

    

// ...
Route::middleware(['permission:akses.approval-finance'])->prefix('monitoring-finance')->name('monitoring-finance.')->group(function () {
    Route::get('/', [FinanceMonitoringController::class, 'index'])->name('index');
    Route::get('/company/{company}', [FinanceMonitoringController::class, 'showCompany'])->name('company');
    Route::get('/company/{company}/user/{user}', [FinanceMonitoringController::class, 'showUser'])->name('user');
    Route::get('/company/{company}/user/{user}/module/{jenis}', [FinanceMonitoringController::class, 'moduleIndex'])
        ->whereIn('jenis', ['OPR', 'PD', 'EDK', 'ELK'])
        ->name('module.index');
    Route::get('/company/{company}/user/{user}/module/{jenis}/{source}', [FinanceMonitoringController::class, 'moduleShow'])
        ->whereIn('jenis', ['OPR', 'PD', 'EDK', 'ELK'])
        ->name('module.show');
});
});

// ==========================================
// 3. BLOK VVIP ADMINISTRATOR
// ==========================================
Route::middleware(['auth', 'permission:akses.master-data'])->group(function () {

    Route::get('/dashboard/master-data', function () {
        $counts = [
            'roles'             => DB::table('roles')->count(),
            'users'             => DB::table('user')->count(),
            'companies'         => DB::table('company')->count(),
            'levels'            => DB::table('level')->count(),
            'positions'         => DB::table('position')->count(),
            'wilayah'           => DB::table('wilayah')->count(),
            'cost_categories'   => DB::table('cost_category')->count(),
            'week_day_groups'   => DB::table('weekday_group')->count(),
            'event_hours'       => DB::table('event_hour_group')->count(),
            'plafon_dinas'      => DB::table('setting_plafon_perjalanan_dinas')->count(),
            'plafon_dalam'      => DB::table('setting_plafon_event_marketing_dalam_kota')->count(),
            'plafon_luar'       => DB::table('setting_plafon_event_marketing_luar_kota')->count(),
            'periode'           => DB::table('periode')->count(),
        ];

        $periods = DB::table('periode')->orderBy('periode', 'desc')->get();

        return view('dashboard.master_data', compact('counts', 'periods'));
    })->name('dashboard.master_data');

    Route::resource('roles', RoleController::class);
    Route::post('/users/{user}/password/reveal', [UserController::class, 'revealPassword'])
        ->middleware('throttle:30,1')
        ->name('users.password.reveal');
    Route::resource('users', UserController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('levels', LevelController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('cost_categories', CostCategoryController::class);
    Route::resource('wilayah', WilayahController::class);
    Route::get('/api/wilayah-search', [WilayahController::class, 'search'])->name('api.wilayah.search');
    Route::resource('event_hour_groups', EventHourGroupController::class);
    Route::resource('setting_plafon_perjalanan_dinas', SettingPlafonPerjalananDinasController::class);
    Route::resource('weekday_groups', WeekdayGroupController::class);
    Route::resource('plafon-event-dalam-kota', PlafonEventDalamKotaController::class);
    Route::resource('plafon-event-luar-kota', PlafonEventLuarKotaController::class);

    // ACCESS MANAGEMENT (halaman pengaturan hak akses menu per Role / User Right Group)
    Route::middleware(['permission:akses.access-management'])->group(function () {
        Route::get('/access-management', [AccessManagementController::class, 'index'])->name('access-management.index');
        Route::put('/access-management/{role}', [AccessManagementController::class, 'update'])->name('access-management.update');
    });

    Route::get('/periode', [PeriodeController::class, 'index'])->name('periode.index');
    Route::post('/periode/{periode}/close', [PeriodeController::class, 'close'])->name('periode.close');
    Route::get('/periode/{periode}', [PeriodeController::class, 'show'])->name('periode.show');
    Route::post('/periode/{periode}/reopen', [PeriodeController::class, 'reopen'])->name('periode.reopen');
});
