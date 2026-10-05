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

    Route::get('/api/bs-balance', [\App\Http\Controllers\BsBalanceController::class, 'balances'])->name('bs-balance.balances');
    Route::get('/saldo-bs', [\App\Http\Controllers\BsBalanceController::class, 'show'])
        ->middleware('permission:akses.cash-flow')->name('bs-balance.show');

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

    Route::get('/dashboard/transaksi', function () {
        $user = auth()->user();
        $canSeeAll = $user->canSeeAllTransactions();
        $uid = $user->id;

        $scope = fn ($table) => $canSeeAll
            ? DB::table($table)
            : DB::table($table)->where($table . '.user_id', $uid);

        $awalBulan  = now()->startOfMonth()->toDateString();
        $akhirBulan = now()->endOfMonth()->toDateString();

        $totalTransaksi =
            $scope('transaksi_operasional_area_sales_header')->count() +
            $scope('transaksi_perjalanan_dinas_header')->count() +
            $scope('perongkosan_event_dalam_kota')->count() +
            $scope('perongkosan_event_luar_kota')->count();

        $sumOperasional = $scope('transaksi_operasional_area_sales_header')
            ->join('transaksi_operasional_area_sales_detail', 'transaksi_operasional_area_sales_header.transaksi_id', '=', 'transaksi_operasional_area_sales_detail.transaksi_id')
            ->sum('transaksi_operasional_area_sales_detail.nominal');

        $sumPerdin = $scope('transaksi_perjalanan_dinas_header')
            ->join('transaksi_perjalanan_dinas_detail', 'transaksi_perjalanan_dinas_header.transaksi_id', '=', 'transaksi_perjalanan_dinas_detail.transaksi_id')
            ->sum('transaksi_perjalanan_dinas_detail.nominal');

        $totalNominal =
            $sumOperasional +
            $sumPerdin +
            $scope('perongkosan_event_dalam_kota')->sum('nominal') +
            $scope('perongkosan_event_luar_kota')->sum('nominal');

        $transaksiBulanIni =
            $scope('transaksi_operasional_area_sales_header')->whereBetween('tanggal', [$awalBulan, $akhirBulan])->count() +
            $scope('transaksi_perjalanan_dinas_header')->whereBetween('tanggal', [$awalBulan, $akhirBulan])->count() +
            $scope('perongkosan_event_dalam_kota')->whereBetween('tanggal', [$awalBulan, $akhirBulan])->count() +
            $scope('perongkosan_event_luar_kota')->whereBetween('tanggal', [$awalBulan, $akhirBulan])->count();

        $overPlafon =
            $scope('transaksi_perjalanan_dinas_header')->where('is_over_plafon', 1)->count() +
            $scope('perongkosan_event_dalam_kota')->where('is_over_plafon', 1)->count() +
            $scope('perongkosan_event_luar_kota')->where('is_over_plafon', 1)->count();

        return view('dashboard.transaksi', compact(
            'totalTransaksi', 'totalNominal', 'transaksiBulanIni', 'overPlafon'
        ));
    })->name('dashboard.transaksi');

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