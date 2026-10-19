<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckActiveAtasan;
use App\Http\Middleware\CheckActiveCompany;
use App\Http\Middleware\InjectNotificationBell;
use App\Models\BsCashFlowSnapshot;
use App\Models\StockCashFlowSnapshot;
use App\Models\PengajuanBs;
use App\Models\Periode;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\User;
use App\Services\BonSementaraService;
use App\Services\BsCashFlowService;
use App\Services\StockCashFlowService;
use App\Services\BsPeriodClosingService;
use App\Services\BsValidationService;
use App\Services\PeriodService;
use App\Support\Money;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use \Tests\Concerns\FinanceMonitoringScenarios;

class BsCashFlowTest extends TestCase
{
    use \Tests\Concerns\CostCategoryStatusScenarios;
    use \Tests\Concerns\TransactionRecapScenarios;
    use \Tests\Concerns\UnifiedTransactionScenarios;
    use \Tests\Concerns\PeriodClosingReviewScenarios;
    use \Tests\Concerns\PeriodClosingExclusionScenarios;
    use \Tests\Concerns\BsFundingRecapScenarios;
    use \Tests\Concerns\DashboardPeriodScenarios;
    use \Tests\Concerns\TransactionActionScenarios;
    use \Tests\Concerns\SharedBsUsageScenarios;
    use \Tests\Concerns\LegacyFinanceFixtures;
    use \Tests\Concerns\FinanceRejectionScenarios;
    use \Tests\Concerns\BsAttachmentScenarios;
    use \Tests\Concerns\TransactionDetailPhotoScenarios;
    use \Tests\Concerns\FinanceCashPostingScenarios;
    use \Tests\Concerns\BsApprovalPeriodScenarios;
    use \Tests\Concerns\ApprovalAtasanBatchScenarios;
    use FinanceMonitoringScenarios;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName(), 'These tests require the isolated in-memory database.');
        $this->travelTo(now()->setDate(2026, 9, 16)->setTime(10, 0));
        DB::connection()->getPdo()->sqliteCreateFunction('DATE_FORMAT', fn ($date, $format) => substr($date ?? '', 0, 7), 2);
        $this->createSchema();
        (require database_path('migrations/2026_07_16_075422_create_permission_tables.php'))->up();
        (require database_path('migrations/2026_09_16_100000_add_bs_cash_flow_and_travel_validation.php'))->up();
        (require database_path('migrations/2026_09_17_100000_restore_readable_document_numbers.php'))->up();
        (require database_path('migrations/2026_09_18_100000_defer_closing_balances_until_period_close.php'))->up();
        (require database_path('migrations/2026_09_18_110000_create_stock_cash_flow_snapshots_table.php'))->up();
        (require database_path('migrations/2026_09_21_100000_create_reimbursement_claims.php'))->up();
        (require database_path('migrations/2026_09_23_100000_add_payment_proofs_to_reimbursement_claims.php'))->up();
        (require database_path('migrations/2026_09_23_110000_add_personal_funds_to_stock_cash_flow.php'))->up();
        (require database_path('migrations/2026_09_24_140000_add_pooled_bs_funding.php'))->up();
        (require database_path('migrations/2026_09_24_160000_add_bs_owner_approval.php'))->up();
        (require database_path('migrations/2026_09_25_150000_create_bs_usage_checks.php'))->up();
        (require database_path('migrations/2026_09_28_100000_extend_finance_checks_to_transaction_modules.php'))->up();
        (require database_path('migrations/2026_09_30_160000_add_finance_check_drafts.php'))->up();
        (require database_path('migrations/2026_10_05_090000_track_finance_resubmissions.php'))->up();
        (require database_path('migrations/2026_10_07_030313_add_attachment_path_to_pengajuan_bs_table.php'))->up();
        (require database_path('migrations/2026_10_07_120000_mark_finance_based_cash_snapshots.php'))->up();
        (require database_path('migrations/2026_10_07_160000_track_finance_approval_per_source_detail.php'))->up();
        (require database_path('migrations/2026_10_08_100000_add_photos_to_transaction_details.php'))->up();
        (require database_path('migrations/2026_10_09_025352_add_operasional_and_perjalanan_dinas_columns_to_stock_cash_flow_snapshots_table.php'))->up();
        (require database_path('migrations/2026_10_09_032839_fix_operasional_perjalanan_dinas_nullable.php'))->up();
        (require database_path('migrations/2026_10_12_100000_create_period_closing_logs_table.php'))->up();
        DB::table('user')->insert([
            ['id' => 1, 'nama' => 'Pemilik', 'roles_id' => null, 'atasan_user_id' => 2],
            ['id' => 2, 'nama' => 'Atasan / Finance', 'roles_id' => null, 'atasan_user_id' => null],
            ['id' => 3, 'nama' => 'Anggota', 'roles_id' => null, 'atasan_user_id' => 2],
        ]);
        $permissions = ['akses.pengajuan-bs', 'akses.transaksi-operasional', 'akses.transaksi-perjalanan-dinas', 'akses.cash-flow', 'akses.approval-finance', 'akses.master-data', 'akses.laporan-bs', 'akses.settle-bs', 'akses.event-dalam-kota', 'akses.event-luar-kota'];
        foreach ($permissions as $permission) \Spatie\Permission\Models\Permission::create(['name' => $permission, 'guard_name' => 'web']);
        foreach ([1, 3] as $id) User::findOrFail($id)->givePermissionTo(array_slice($permissions, 0, 4));
        User::findOrFail(2)->givePermissionTo($permissions);
        User::findOrFail(1)->givePermissionTo(['akses.event-dalam-kota', 'akses.event-luar-kota']);
        Notification::fake();
        $this->withoutMiddleware([CheckActiveAtasan::class, CheckActiveCompany::class, InjectNotificationBell::class]);
        $this->actingAs(User::findOrFail(1));
        Periode::create(['periode' => '2026-09', 'status' => 'OPEN']);
    }

    private function createSchema(): void
    {
      Schema::create('user', function (Blueprint $t) { 
    $t->increments('id'); $t->string('nama'); $t->integer('roles_id')->nullable(); $t->integer('company_id')->nullable(); $t->integer('atasan_user_id')->nullable(); $t->boolean('is_active')->default(true); 
});
        Schema::create('roles', function (Blueprint $t) { $t->increments('id'); $t->string('code'); });
        Schema::create('periode', function (Blueprint $t) {
            $t->increments('id'); $t->string('periode', 7)->unique(); $t->string('status');
            $t->timestamp('closed_at')->nullable(); $t->integer('closed_by')->nullable();
            $t->timestamp('reopened_at')->nullable(); $t->string('reopen_reason')->nullable(); $t->timestamps();
        });
        Schema::create('pengajuan_bs', function (Blueprint $t) {
            $t->increments('id'); $t->string('document_no')->unique(); $t->date('tanggal'); $t->integer('user_id');
            $t->integer('atasan_user_id')->nullable(); $t->string('periode'); $t->string('tipe_pengajuan')->default('operasional_sales');
            foreach (['nominal_pengajuan','nominal_approved','nominal_sisa_bs_snapshot','nominal_pengembalian','nominal_reimburse'] as $name) $t->decimal($name, 15, 2)->default(0);
            $t->string('status'); $t->string('description')->nullable(); $t->string('approval_message')->nullable(); $t->string('rejection_reason')->nullable();
            $t->string('settled_periode')->nullable(); $t->timestamp('settled_at')->nullable(); $t->timestamps();
        });
        Schema::create('approval_bs_finance', function (Blueprint $t) {
            $t->increments('id'); $t->string('document_no')->unique(); $t->integer('pengajuan_bs_id'); $t->date('tanggal_cair'); $t->integer('finance_user_id'); $t->string('periode');
            foreach (['nominal_pengajuan_bs','nominal_sisa_bs','nominal_approved'] as $name) $t->decimal($name, 15, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('pengajuan_bs_members', function (Blueprint $t) { $t->integer('pengajuan_bs_id'); $t->integer('user_id'); });
        foreach (['transaksi_operasional_area_sales_header', 'transaksi_perjalanan_dinas_header'] as $table) {
            Schema::create($table, function (Blueprint $t) use ($table) {
                $t->increments('id'); $t->string('transaksi_id', 11)->unique(); $t->date('tanggal'); $t->integer('user_id'); $t->integer('bs_id')->nullable(); $t->string('periode')->nullable(); $t->string('description')->nullable();
                if ($table === 'transaksi_operasional_area_sales_header') $t->string('status')->default('DRAFT');
                else { $t->integer('tujuan_dinas_id')->default(1); $t->decimal('jarak_km')->default(0); $t->decimal('durasi_jam')->default(0); $t->boolean('is_over_plafon')->default(false); $t->decimal('plafon_value', 15, 2)->nullable(); $t->decimal('selisih_plafon', 15, 2)->nullable(); }
            });
        }
        foreach (['transaksi_operasional_area_sales_detail','transaksi_perjalanan_dinas_detail'] as $table) {
            Schema::create($table, function (Blueprint $t) { $t->increments('id'); $t->string('transaksi_id'); $t->integer('cost_category_id')->default(1); $t->string('remark')->nullable(); $t->decimal('nominal', 15, 2); $t->softDeletes(); });
        }
        foreach (['perongkosan_event_dalam_kota','perongkosan_event_luar_kota'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->increments('id'); $t->integer('user_id'); $t->date('tanggal'); $t->string('periode'); $t->decimal('nominal', 15, 2); $t->boolean('is_over_plafon')->default(false);
                $t->string('transaksi_id')->nullable(); $t->string('description')->nullable(); $t->boolean('is_libur')->default(false);
                $t->integer('jam_kerja_event_id')->nullable(); $t->integer('lokasi_event_id')->nullable();
                $t->decimal('jarak_km')->default(0); $t->decimal('durasi_jam')->default(0);
                $t->decimal('plafon_value', 15, 2)->nullable(); $t->decimal('selisih_plafon', 15, 2)->nullable();
                foreach (['foto_event_1', 'foto_event_2', 'foto_event_3'] as $photo) $t->string($photo)->nullable();
            });
        }
        Schema::create('event_hour_group', function (Blueprint $t) { $t->increments('id'); $t->string('code'); });
        DB::table('event_hour_group')->insert(['id' => 1, 'code' => '8 JAM']);
        Schema::create('cash_flow_snapshots', function (Blueprint $t) {
            $t->increments('id'); $t->integer('user_id'); $t->string('periode');
            foreach (['saldo_awal','total_masuk','total_keluar','saldo_akhir'] as $name) $t->decimal($name, 15, 2)->default(0);
            $t->timestamp('last_updated')->nullable(); $t->unique(['user_id','periode']);
        });
        Schema::create('bs_activity_logs', function (Blueprint $t) {
            $t->increments('id'); $t->integer('pengajuan_bs_id'); $t->string('activity_code'); $t->string('activity_name'); $t->text('description')->nullable(); $t->decimal('nominal', 15, 2)->nullable(); $t->integer('actor_user_id')->nullable(); $t->timestamps();
        });
        Schema::create('cost_category', function (Blueprint $t) { $t->increments('id'); $t->string('nama'); $t->string('name')->nullable(); $t->string('description')->nullable(); $t->string('code')->nullable(); $t->boolean('is_active')->default(true); });
        DB::table('cost_category')->insert(['id' => 1, 'nama' => 'Transport']);
        Schema::create('wilayah', function (Blueprint $t) { $t->increments('id'); $t->string('nama'); $t->string('teritory'); $t->integer('parent_id')->nullable(); });
        DB::table('wilayah')->insert(['id' => 1, 'nama' => 'Semarang', 'teritory' => 'Jawa']);
        
        Schema::create('company', function (Blueprint $t) {
    $t->increments('id'); $t->string('code')->nullable()->unique(); $t->string('nama'); $t->boolean('is_active')->default(true);
});
    }

    public function test_finance_checks_replace_posted_balances_without_changing_source_transactions(): void
    {
        $bs = $this->bs();
        $opr = $this->expense($bs, status: 'VALIDATED', amount: '100000');
        $pd = $this->expense($bs, status: 'VALIDATED', amount: '200000', travel: true, attributes: ['user_id' => 3]);
        $balance = app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609');
        app(BsCashFlowService::class)->recalculateSnapshot(1, '202609');
        app(StockCashFlowService::class)->recalculateSnapshot(1, '202609');
        $tables = ['pengajuan_bs', 'approval_bs_finance', 'bs_usage_logs', 'bs_usage_approval_logs', 'reimbursement_claims', 'periode'];
        $before = [];
        foreach ($tables as $table) $before[$table] = DB::table($table)->get()->toJson();
        $this->actingAs(User::findOrFail(2));
        $service = app(\App\Services\BsUsageCheckService::class);
        foreach (['OPR' => $opr, 'PD' => $pd] as $type => $source) {
            $attributes = $source->fresh()->getAttributes();
            $snapshot = $service->snapshot($source);
            $data = [
                'tanggal_pengecekan' => '2026-10-01',
                'source_token' => $service->token($type, $source->id, $snapshot['fingerprint']),
                'details' => [['source_detail_id' => $source->details()->first()->id, 'cost_category_id' => 1, 'nominal_disetujui' => '1.25', 'catatan' => 'Verifikasi saja']],
            ];
            for ($i = 0; $i < 2; $i++) {
                $this->post(route('bs-usage-checks.store', [$type, $source->id]), $data)->assertSessionHasNoErrors()->assertRedirect();
            }
            $this->assertSame($attributes, $source->fresh()->getAttributes());
            $this->assertDatabaseHas('bs_usage_checks', ['jenis_transaksi' => $type, 'user_id' => $source->user_id, 'user_checker_id' => 2, 'periode' => '2026-10']);
        }
        foreach ($before as $table => $rows) $this->assertSame($rows, DB::table($table)->get()->toJson(), $table.' changed after Finance checks');
        $this->assertSame('1000000.00', $balance);
        $this->assertFinanceCash('2.50', '999997.50');
        $this->assertDatabaseCount('bs_usage_checks', 4);
        $this->actingAs(User::findOrFail(1));
        $this->get(route('transaksi_operasional.show', $opr->id))->assertOk()->assertSee('Sudah Dicek Finance');
        $this->actingAs(User::findOrFail(3));
        $this->get(route('transaksi_perjalanan_dinas.show', $pd->transaksi_id))->assertOk()->assertSee('Sudah Dicek Finance');
    }

    private function dashboardFinanceCheck($source, string $type = 'OPR', array $approved = [], string $date = '2026-09-16')
    {
        $service = app(\App\Services\BsUsageCheckService::class);
        $snapshot = $service->snapshot($source);

        return $service->store(User::findOrFail(2), $type, $source->id, [
            'tanggal_pengecekan' => $date,
            'source_token' => $service->token($type, $source->id, $snapshot['fingerprint']),
            'details' => $snapshot['items']->map(fn ($item) => [
                'source_detail_id' => $item['source_detail_id'],
                'cost_category_id' => $item['cost_category_id'],
                'nominal_disetujui' => $approved[$item['cost_category_id']] ?? $item['nominal_asal'],
                'catatan' => 'Catatan privat Finance',
            ])->all(),
        ]);
    }

    public function test_dashboard_shows_only_confirmed_usage_with_latest_finance_result_and_pending_amounts(): void
    {
        $bs = $this->bs();
        $opr = $this->expense($bs, status: 'VALIDATED', amount: '100', attributes: ['validated_at' => now(), 'validated_by' => 1]);
        $pd = $this->expense($bs, status: 'VALIDATED', amount: '50', travel: true, attributes: ['validated_at' => now(), 'validated_by' => 1]);
        $draft = $this->expense($bs, amount: '999');
        $pending = $this->expense($bs, status: 'PENDING_OWNER', amount: '777', travel: true);
        $rejected = $this->expense($bs, status: 'REJECTED_OWNER', amount: '666', travel: true);
        // A historical check on a source now in draft must not count.
        $draft->update(['status' => 'VALIDATED']);
        $this->dashboardFinanceCheck($draft);
        $draft->update(['status' => 'DRAFT']);
        $this->dashboardFinanceCheck($opr, approved: [1 => '90']);
        $last = $this->dashboardFinanceCheck($opr, approved: [1 => '95'], date: '2026-10-01');
        $response = $this->get(route('dashboard.transaksi'))->assertOk()->assertSee('Rekap Pengajuan dan Hasil Finance')
            ->assertSee('Operasional Sales')->assertSee('Perjalanan Dinas')
            ->assertDontSee($draft->transaksi_id)->assertDontSee($pending->transaksi_id)->assertDontSee($rejected->transaksi_id)
            ->assertSee('Belum Dicek')->assertDontSee('Catatan privat Finance');
        $data = $response->viewData('bsUsageDashboard');
        $this->assertEquals(2, $data['summary']->confirmed_count);
        $this->assertEquals(150, $data['summary']->nominal_user);
        $this->assertEquals(95, $data['summary']->nominal_finance);
        $this->assertEquals(5, $data['summary']->selisih);
        $this->assertEquals(55, $data['summary']->nominal_pending);
        $this->assertEquals(50, $data['summary']->nominal_waiting);
        $this->assertEquals(2, $data['summary']->pending_count);
        $this->assertEquals(1, $data['summary']->checked_count);
        $this->assertEquals(1, $data['summary']->unchecked_count);
        $oprRow = $data['rows']->firstWhere('jenis', 'OPR');
        $pdRow = $data['rows']->firstWhere('jenis', 'PD');
        $this->assertEquals($last->id, $oprRow->check_id);
        $this->assertNull($pdRow->check_id);
        $this->assertNull($pdRow->nominal_finance);
        $this->assertNull($pdRow->selisih);
        $this->assertSame('2026-09', $data['filters']['pemakaian_periode']);
    }

    public function test_dashboard_keeps_owner_scope_and_allows_finance_to_see_confirmed_usage_across_users(): void
    {
        $bs = $this->bs();
        $own = $this->expense($bs, status: 'VALIDATED', amount: '100');
        $member = $this->expense($bs, status: 'VALIDATED', amount: '200', travel: true, attributes: ['user_id' => 3, 'bs_owner_id' => 1, 'validated_by' => 1]);
        $check = $this->dashboardFinanceCheck($member, 'PD', [1 => '150']);
        $this->get(route('dashboard.transaksi', ['user_id' => 3]))->assertOk()->assertDontSee($member->transaksi_id)
            ->assertViewHas('bsUsageDashboard', fn ($data) => $data['summary']->confirmed_count == 1 && $data['summary']->nominal_user == 100);
        $this->actingAs(User::findOrFail(3));
        $this->get(route('transaction-recap.module', ['jenis' => 'PD']))->assertOk()->assertSee($member->transaksi_id)->assertDontSee($own->transaksi_id)
            ->assertSee('Rp 150,00')->assertSee('Oleh Pemilik')->assertDontSee('href="'.route('bs-usage-checks.show', $check).'"', false);
        $this->actingAs(User::findOrFail(2));
        $this->get(route('transaction-recap.module', ['jenis' => 'PD']))->assertOk()->assertSee($member->transaksi_id)
            ->assertSee('Detail Transaksi')
            ->assertSee(route('transaction-recap.show', ['jenis' => 'PD', 'source' => $member->id, 'pemakaian_periode' => '2026-09', 'pemakaian_page' => 1, 'dari' => 'laporan']))
            ->assertDontSee('href="'.route('bs-usage-checks.show', $check).'"', false);
        $this->get(route('dashboard.transaksi'))->assertOk()
            ->assertViewHas('bsUsageDashboard', fn ($data) => $data['summary']->confirmed_count == 2 && $data['summary']->nominal_user == 300);
    }

    public function test_dashboard_zero_approval_is_real_and_offsetting_item_differences_are_not_matching(): void
    {
        DB::table('cost_category')->insert(['id' => 2, 'nama' => 'Makan']);
        $bs = $this->bs();
        $zero = $this->expense($bs, status: 'VALIDATED', amount: '100');
        $offset = $this->expense($bs, status: 'VALIDATED', amount: '100', travel: true);
        $offset->details()->create(['cost_category_id' => 2, 'nominal' => 50, 'remark' => 'Makan']);
        $this->dashboardFinanceCheck($zero, approved: [1 => '0']);
        $this->dashboardFinanceCheck($offset, 'PD', [1 => '90', 2 => '60']);
        $data = $this->get(route('dashboard.transaksi'))->assertOk()->assertSee('Selisih Persetujuan Finance')->viewData('bsUsageDashboard');
        $this->assertEquals(2, $data['summary']->checked_count);
        $this->assertEquals(2, $data['summary']->different_count);
        $this->assertEquals(150, $data['summary']->nominal_finance);
        $this->assertEquals(100, $data['summary']->selisih);
        $zeroRow = $data['rows']->firstWhere('jenis', 'OPR');
        $this->assertNotNull($zeroRow->nominal_finance);
        $this->assertEquals(0, $zeroRow->nominal_finance);
        $this->assertEquals(0, $data['rows']->firstWhere('jenis', 'PD')->selisih);
        $this->assertEquals(100, $data['summary']->nominal_pending);
        $this->assertEquals(2, $data['summary']->pending_count);
        $this->assertEquals(0, $data['summary']->nominal_unchecked);
        $this->get(route('transaction-recap.module', ['jenis' => 'PD', 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->firstWhere('jenis', 'PD')->nominal_pending == 0);
    }

    public function test_dashboard_returns_changed_categories_to_pending_even_with_unchanged_total(): void
    {
        DB::table('cost_category')->insert(['id' => 2, 'nama' => 'Makan']);
        $source = $this->expense($this->bs(), status: 'VALIDATED', amount: '100', travel: true);
        $this->dashboardFinanceCheck($source, 'PD', [1 => '90']);
        $source->details()->first()->update(['cost_category_id' => 2]);
        $source->update(['status' => 'VALIDATED']);
        $data = $this->get(route('dashboard.transaksi'))->assertOk()
            ->assertSee('Pending / Belum Dicek')->assertDontSee('Perlu Cek Ulang')->viewData('bsUsageDashboard');
        $this->assertEquals(1, $data['summary']->unchecked_count);
        $this->assertEquals(100, $data['summary']->nominal_unchecked);
        $this->assertSame('PENDING', $data['rows']->first()->finance_status);
        $this->assertEquals(0, $data['summary']->checked_count);
        $this->assertEquals(100, $data['summary']->nominal_pending);
        $this->assertEquals(0, $data['summary']->nominal_finance);
        $this->assertNull($data['rows']->first()->selisih);
        $this->dashboardFinanceCheck($source, 'PD', [2 => '95']);
        $deleted = $source->details()->create(['cost_category_id' => 1, 'nominal' => 500, 'remark' => 'Sudah dihapus']);
        $deleted->delete();
        $data = $this->get(route('dashboard.transaksi'))->assertOk()->viewData('bsUsageDashboard');
        $this->assertEquals(0, $data['summary']->unchecked_count);
        $this->assertEquals(1, $data['summary']->checked_count);
        $this->assertEquals(95, $data['summary']->nominal_finance);
        $this->assertEquals(5, $data['summary']->selisih);
    }

    public function test_dashboard_usage_filters_and_pagination_keep_totals_for_all_filtered_rows(): void
    {
        $bs = $this->bs();
        for ($i = 0; $i < 12; $i++) $this->expense($bs, status: 'VALIDATED', amount: '10');
        $this->expense($bs, status: 'VALIDATED', amount: '50', travel: true);
        $older = $this->expense($bs, status: 'VALIDATED', amount: '100', attributes: ['periode' => '2026-08', 'tanggal' => '2026-08-01']);
        $response = $this->get(route('transaction-recap.module', ['jenis' => 'OPR', 'pemakaian_page' => 2, 'pemakaian_status' => 'FOLLOW_UP']))->assertOk()->assertDontSee($older->transaksi_id);
        $data = ['rows' => $response->viewData('rows'), 'summary' => $response->viewData('summary')];
        $this->assertEquals(12, $data['rows']->total());
        $this->assertCount(2, $data['rows']);
        $this->assertEquals(120, $data['summary']->nominal_user);
        $this->assertEquals(120, $data['summary']->nominal_pending);
        $this->assertEquals(120, $data['summary']->nominal_unchecked);
        $this->assertEquals(0, $data['summary']->selisih);
        $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '']))->assertOk()->assertViewHas('bsUsageDashboard', fn ($data) => $data['summary']->confirmed_count == 14 && $data['summary']->nominal_user == 270);
        $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '202608']))->assertOk()->assertViewHas('bsUsageDashboard', fn ($data) => $data['summary']->confirmed_count == 1);
        $this->get(route('dashboard.transaksi', ['pemakaian_periode' => '2027-01']))->assertOk()
            ->assertViewHas('bsUsageDashboard', fn ($data) => $data['rows']->isEmpty() && $data['summary']->confirmed_count == 0);
        foreach ([['pemakaian_periode' => '2026-13'], ['pemakaian_jenis' => 'EVENT'], ['pemakaian_page' => 0]] as $filters) {
            $this->getJson(route('dashboard.transaksi', $filters))->assertUnprocessable();
        }
    }

    private function bs(array $attributes = [], bool $paid = true): PengajuanBs
    {
        $bs = PengajuanBs::create($attributes + ['document_no' => 'TEST-'.uniqid(), 'tanggal' => '2026-09-01', 'periode' => '2026-09', 'user_id' => 1, 'atasan_user_id' => 2, 'status' => 'DISBURSED', 'nominal_pengajuan' => '1000000.00', 'nominal_approved' => '1000000.00']);
        if ($paid) DB::table('approval_bs_finance')->insert(['document_no' => 'CAIR-'.$bs->id, 'pengajuan_bs_id' => $bs->id, 'tanggal_cair' => $bs->tanggal, 'periode' => $bs->periode, 'finance_user_id' => 2, 'nominal_approved' => $bs->nominal_approved]);
        return $bs;
    }

    private function expense(PengajuanBs $bs, string $status = 'DRAFT', string $amount = '600000.00', bool $travel = false, array $attributes = [])
    {
        $class = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        $header = $class::create($attributes + ['transaksi_id' => 'T'.substr(uniqid(), -9), 'tanggal' => $bs->tanggal->format('Y-m-d'), 'periode' => $bs->periode, 'user_id' => $bs->user_id, 'bs_id' => $bs->id, 'status' => $status]);
        $header->details()->create(['transaksi_id' => $header->transaksi_id, 'cost_category_id' => 1, 'remark' => 'Biaya uji', 'nominal' => $amount]);
        return $header;
    }

    private function pendingMemberUsage(string $amount = '600000.00'): TransaksiPerjalananDinasHeader
    {
        $group = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $group->members()->attach(3);
        $document = $this->expense($group, amount: $amount, travel: true, attributes: ['user_id' => 3]);
        $this->actingAs(User::findOrFail(3));
        $this->post(route('transaksi_perjalanan_dinas.validate', $document->transaksi_id))->assertSessionHasNoErrors();
        return $document->fresh();
    }

    public function test_member_request_waits_without_reserving_or_spending_cash_and_notifies_owner_once(): void
    {
        $document = $this->pendingMemberUsage();
        $this->assertSame('PENDING_OWNER', $document->status);
        $this->assertSame(1, $document->approval_version);
        $this->assertNotNull($document->submitted_at);
        $this->assertNull($document->validated_at);
        $this->assertDatabaseCount('bs_usage_logs', 0);
        $this->assertDatabaseHas('bs_usage_approval_logs', ['transaction_id' => $document->id, 'action' => 'SUBMITTED', 'actor_id' => 3, 'nominal' => '600000.00']);
        Notification::assertSentTo(User::findOrFail(1), \App\Notifications\BsUsageApprovalUpdated::class,
            fn ($notification, $channels) => $notification->action === 'SUBMITTED'
                && $channels === ['database']
                && $notification->toArray(User::findOrFail(1))['url'] === route('bs-usage-approvals.show', $document->id));
        $this->postJson(route('transaksi_perjalanan_dinas.validate', $document->transaksi_id))->assertUnprocessable();
        Notification::assertCount(1);
        $this->travelTo(now()->addDays(5));
        $this->assertSame('PENDING_OWNER', $document->fresh()->status);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertSame('0.00', app(StockCashFlowService::class)->recalculateSnapshot(1, '202609')->total_keluar);
        $this->get(route('transaksi_perjalanan_dinas.show', $document->transaksi_id))->assertOk()
            ->assertSee('Menunggu Persetujuan Pemohon')->assertSee('Tarik Permintaan ke Draft');
        $this->actingAs(User::findOrFail(1));
        $this->get(route('bs-usage-approvals.index'))->assertOk()->assertSee($document->transaksi_id)->assertSee('Anggota');
        $this->get(route('bs-usage-approvals.show', $document->id))->assertOk()
            ->assertSee('Biaya uji')->assertSee('Rp 600.000,00')->assertSee('Setujui &amp; Konfirmasi', false);
        $this->get(route('dashboard.transaksi'))->assertOk()->assertDontSee('Persetujuan Pemakaian BS');
        $this->assertEmpty(app(BsCashFlowService::class)->details(1, '2026-09')['keluar']);
    }

    public function test_only_actual_owner_can_review_and_approve_even_without_travel_module_permission(): void
    {
        $document = $this->pendingMemberUsage();
        DB::table('roles')->insert(['id' => 1, 'code' => 'ADMIN']);
        DB::table('user')->where('id', 2)->update(['roles_id' => 1]);
        foreach ([2, 3] as $id) {
            $this->actingAs(User::findOrFail($id));
            $this->get(route('bs-usage-approvals.show', $document->id))->assertForbidden();
            $this->postJson(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])->assertForbidden();
            $this->postJson(route('bs-usage-approvals.reject', $document->id), ['approval_version' => 1, 'reason' => 'Tidak sesuai'])->assertForbidden();
            $this->get(route('bs-usage-approvals.index'))->assertOk()->assertDontSee($document->transaksi_id);
        }
        $owner = User::findOrFail(1);
        $owner->syncPermissions(['akses.pengajuan-bs']);
        $this->actingAs($owner->fresh());
        $this->get(route('transaksi_perjalanan_dinas.show', $document->transaksi_id))->assertForbidden();
        $this->get(route('bs-usage-approvals.show', $document->id))->assertOk()->assertSee('Biaya uji');
        $this->postJson(route('bs-usage-approvals.approve', $document->id), [])->assertUnprocessable()->assertJsonValidationErrors('approval_version');
        $this->post(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $this->assertSame('VALIDATED', $document->fresh()->status);
        $this->assertEquals(1, $document->fresh()->validated_by);
        $this->assertEquals(1, $document->fresh()->owner_reviewed_by);
        $this->assertDatabaseHas('bs_usage_logs', ['inputter_id' => 3, 'actor_id' => 1, 'bs_owner_id' => 1]);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        Notification::assertSentTo(User::findOrFail(3), \App\Notifications\BsUsageApprovalUpdated::class,
            fn ($notification) => $notification->action === 'APPROVED');
        $this->postJson(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])->assertUnprocessable();
        $this->postJson(route('bs-usage-approvals.reject', $document->id), ['approval_version' => 1, 'reason' => 'Ulang'])->assertUnprocessable();
        $this->assertDatabaseCount('bs_usage_logs', 1);
        $this->assertDatabaseCount('bs_usage_approval_logs', 2);
        Notification::assertCount(2);
    }

    public function test_pending_rincian_are_locked_until_withdrawn_and_stale_approval_cannot_post_a_new_version(): void
    {
        $document = $this->pendingMemberUsage();
        $url = '/transaksi-perjalanan-dinas/'.$document->transaksi_id;
        $this->putJson($url, $this->transactionPayload(true))->assertUnprocessable();
        $this->postJson($url.'/add-detail', ['details' => [['cost_category_id' => 1, 'remark' => 'Tambahan', 'nominal' => 50]]])->assertUnprocessable();
        $this->deleteJson('/transaksi-perjalanan-dinas/detail/'.$document->details->first()->id)->assertUnprocessable();
        $this->deleteJson($url)->assertUnprocessable();
        $this->assertEquals(600000, $document->details()->sum('nominal'));
        $this->actingAs(User::findOrFail(1));
        $this->postJson(route('transaksi_perjalanan_dinas.withdraw', $document->transaksi_id), ['approval_version' => 1])->assertForbidden();
        $this->actingAs(User::findOrFail(3));
        $this->post(route('transaksi_perjalanan_dinas.withdraw', $document->transaksi_id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $this->assertSame('DRAFT', $document->fresh()->status);
        $this->actingAs(User::findOrFail(1));
        $this->postJson(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])->assertUnprocessable();
        $this->get(route('bs-usage-approvals.index'))->assertOk()->assertDontSee($document->transaksi_id);
        $this->get(route('bs-usage-approvals.index', ['status' => 'ALL']))->assertOk()->assertSee($document->transaksi_id);
        $this->actingAs(User::findOrFail(3));
        $this->postJson($url.'/add-detail', ['details' => [['cost_category_id' => 1, 'remark' => 'Tambahan', 'nominal' => '100000']]])->assertRedirect();
        $this->assertEquals(700000, $document->details()->sum('nominal'));
        $this->post($url.'/validate')->assertSessionHasNoErrors();
        $this->assertSame(2, $document->fresh()->approval_version);
        $this->actingAs(User::findOrFail(1));
        $this->postJson(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])->assertUnprocessable();
        $this->assertDatabaseCount('bs_usage_logs', 0);
        $this->post(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 2])->assertSessionHasNoErrors();
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertDatabaseHas('bs_usage_approval_logs', ['action' => 'WITHDRAWN', 'approval_version' => 1, 'nominal' => '600000.00']);
        $this->assertDatabaseHas('bs_usage_approval_logs', ['action' => 'APPROVED', 'approval_version' => 2, 'nominal' => '700000.00']);
    }

    public function test_rejection_keeps_cash_and_reason_and_member_can_revise_and_resubmit(): void
    {
        $document = $this->pendingMemberUsage();
        $this->actingAs(User::findOrFail(1));
        $url = route('bs-usage-approvals.reject', $document->id);
        $this->postJson($url, ['approval_version' => 1, 'reason' => ' '])->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->post($url, ['approval_version' => 1, 'reason' => 'Keterangan biaya belum lengkap'])->assertSessionHasNoErrors();
        $this->assertSame('REJECTED_OWNER', $document->fresh()->status);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertDatabaseCount('bs_usage_logs', 0);
        Notification::assertSentTo(User::findOrFail(3), \App\Notifications\BsUsageApprovalUpdated::class,
            fn ($notification) => $notification->action === 'REJECTED');
        $this->actingAs(User::findOrFail(3));
        $this->get(route('transaksi_perjalanan_dinas.show', $document->transaksi_id))->assertOk()->assertSee('Keterangan biaya belum lengkap')->assertSee('Buka Draft untuk Perbaikan');
        $this->postJson(route('transaksi_perjalanan_dinas.validate', $document->transaksi_id))->assertUnprocessable();
        $this->post(route('transaksi_perjalanan_dinas.revise', $document->transaksi_id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $this->assertNull($document->fresh()->owner_rejection_reason);
        $this->post(route('transaksi_perjalanan_dinas.validate', $document->transaksi_id))->assertSessionHasNoErrors();
        $this->assertSame(2, $document->fresh()->approval_version);
        $this->actingAs(User::findOrFail(1));
        $this->post(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 2])->assertSessionHasNoErrors();
        $this->get(route('bs-usage-approvals.show', $document->id))->assertOk()->assertSee('Keterangan biaya belum lengkap')->assertSee('Dibuka untuk perbaikan');
        $this->assertDatabaseHas('bs_usage_approval_logs', ['action' => 'REJECTED', 'approval_version' => 1, 'reason' => 'Keterangan biaya belum lengkap']);
        $this->assertDatabaseCount('bs_usage_logs', 1);
    }

    public function test_approval_checks_current_cash_and_preserves_pending_request_when_funds_are_insufficient(): void
    {
        $document = $this->pendingMemberUsage();
        $ownExpense = $this->expense($document->bonSementara, amount: '500000.00');
        $this->actingAs(User::findOrFail(1));
        $this->post('/transaksi-operasional/'.$ownExpense->id.'/validate')->assertSessionHasNoErrors();
        $this->confirmUsageFinance($ownExpense);
        $this->postJson(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('bs_balance');
        $this->assertSame('PENDING_OWNER', $document->fresh()->status);
        $this->assertNull($document->fresh()->owner_reviewed_at);
        $this->assertDatabaseCount('bs_usage_logs', 1);
        $this->assertDatabaseCount('bs_usage_approval_logs', 1);
        Notification::assertCount(1);
        $this->assertSame('500000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->bs(['nominal_approved' => '100000.00']);
        $this->post(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $this->assertSame('600000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->confirmUsageFinance($document);
        $this->assertFinanceCash('1100000.00', '0.00');
    }

    public function test_revoked_member_cannot_be_approved_but_owner_can_reject_and_member_can_withdraw(): void
    {
        $document = $this->pendingMemberUsage();
        $document->bonSementara->members()->detach(3);
        $this->actingAs(User::findOrFail(1));
        $this->postJson(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('bs_owner_id');
        $this->post(route('bs-usage-approvals.reject', $document->id), ['approval_version' => 1, 'reason' => 'Akses grup berakhir'])->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail(3));
        $this->post(route('transaksi_perjalanan_dinas.revise', $document->transaksi_id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $this->postJson(route('transaksi_perjalanan_dinas.validate', $document->transaksi_id))
            ->assertUnprocessable()->assertJsonValidationErrors('bs_owner_id');
        $document->bonSementara->members()->attach(3);
        $this->post(route('transaksi_perjalanan_dinas.validate', $document->transaksi_id))->assertSessionHasNoErrors();
        $document->bonSementara->members()->detach(3);
        $this->post(route('transaksi_perjalanan_dinas.withdraw', $document->transaksi_id), ['approval_version' => 2])->assertSessionHasNoErrors();
        $this->assertSame('DRAFT', $document->fresh()->status);
        $this->assertDatabaseCount('bs_usage_logs', 0);
    }

    public function test_approval_rolls_back_status_cash_audit_and_notifications_when_snapshot_write_fails(): void
    {
        $document = $this->pendingMemberUsage();
        $this->actingAs(User::findOrFail(1));
        $mock = \Mockery::mock(StockCashFlowService::class);
        $mock->shouldReceive('recalculateSnapshot')->once()->andThrow(new \RuntimeException('Simulated stock failure'));
        $this->app->instance(StockCashFlowService::class, $mock);
        $this->postJson(route('bs-usage-approvals.approve', $document->id), ['approval_version' => 1])->assertStatus(500);
        $this->assertSame('PENDING_OWNER', $document->fresh()->status);
        $this->assertNull($document->fresh()->validated_at);
        $this->assertDatabaseCount('bs_usage_logs', 0);
        $this->assertDatabaseCount('bs_usage_approval_logs', 1);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertSame(0, DB::transactionLevel());
        Notification::assertCount(1);
    }

    public function test_pending_usage_blocks_period_close_until_owner_resolves_request(): void
    {
        $document = $this->pendingMemberUsage();
        $this->actingAs(User::findOrFail(1));
        $this->travelTo(now()->setDate(2026, 10, 2));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        try {
            app(BsPeriodClosingService::class)->close($period->id);
            $this->fail('Pending requests must block closing');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('menunggu persetujuan', $e->errors()['error'][0]);
        }
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->get(route('dashboard.transaksi'))->assertOk()->assertDontSee('Persetujuan Pemakaian BS');
        $this->post(route('bs-usage-approvals.reject', $document->id), ['approval_version' => 1, 'reason' => 'Tidak jadi dipakai'])->assertSessionHasNoErrors();
        app(BsPeriodClosingService::class)->close($period->id);
        $this->assertSame('CLOSED', $period->fresh()->status);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202610'));
        $this->actingAs(User::findOrFail(3));
        $this->postJson(route('transaksi_perjalanan_dinas.revise', $document->transaksi_id), ['approval_version' => 1])->assertUnprocessable();
        $this->assertSame('REJECTED_OWNER', $document->fresh()->status);
    }

    public function test_closed_period_disallows_pending_approval_rejection_and_withdrawal(): void
    {
        $document = $this->pendingMemberUsage();
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $this->actingAs(User::findOrFail(1));
        foreach (['approve', 'reject'] as $action) {
            $this->postJson(route('bs-usage-approvals.'.$action, $document->id), ['approval_version' => 1, 'reason' => 'Ditutup'])
                ->assertUnprocessable();
        }
        $this->get(route('bs-usage-approvals.show', $document->id))->assertOk()->assertDontSee('Setujui &amp; Konfirmasi', false);
        $this->actingAs(User::findOrFail(3));
        $this->postJson(route('transaksi_perjalanan_dinas.withdraw', $document->transaksi_id), ['approval_version' => 1])->assertUnprocessable();
        $this->assertSame('PENDING_OWNER', $document->fresh()->status);
        $this->assertDatabaseCount('bs_usage_logs', 0);
    }

    private function cashFlowWorkbook(string $url, string $filename): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $response = $this->get($url)->assertOk()->assertDownload($filename)
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = $response->baseResponse->getFile()->getPathname();
        $binder = \PhpOffice\PhpSpreadsheet\Cell\Cell::getValueBinder();

        try {
            // Read as an independent Excel client, without the exporter's global text binder.
            \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder);

            return \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        } finally {
            \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder($binder);
            unlink($path);
        }
    }

    public function test_operational_calendar_blocks_only_the_current_users_transaction_dates(): void
    {
        $bs = $this->bs();
        $this->expense($bs, attributes: ['tanggal' => '2026-09-16']);
        $this->expense($bs, status: 'VALIDATED', attributes: ['tanggal' => '2026-09-14']);
        $this->expense($bs, attributes: ['user_id' => 3, 'tanggal' => '2026-09-15']);
        $this->expense($bs, travel: true, attributes: ['tanggal' => '2026-09-13']);

        $this->get(route('transaksi_operasional.create'))->assertOk()
            ->assertViewHas('usedDates', ['2026-09-14', '2026-09-16'])
            ->assertSee('name="tanggal" class="form-control" value=""', false);

        $this->actingAs(User::findOrFail(3));
        $this->get(route('transaksi_operasional.create'))->assertOk()
            ->assertViewHas('usedDates', ['2026-09-15'])
            ->assertSee('name="tanggal" class="form-control" value="2026-09-16"', false);
    }

    public function test_operational_calendar_restores_only_an_unused_old_date(): void
    {
        $this->expense($this->bs(), attributes: ['tanggal' => '2026-09-14']);

        $this->withSession(['_old_input' => ['tanggal' => '2026-09-14', 'description' => 'Kunjungan tersimpan']])
            ->get(route('transaksi_operasional.create'))->assertOk()
            ->assertSee('name="tanggal" class="form-control" value=""', false)
            ->assertSee('Kunjungan tersimpan');

        $this->withSession(['_old_input' => ['tanggal' => '2026-09-15']])
            ->get(route('transaksi_operasional.create'))->assertOk()
            ->assertSee('name="tanggal" class="form-control" value="2026-09-15"', false);
    }

    public function test_operational_calendar_tracks_saved_and_deleted_drafts_and_rejects_duplicate_submissions(): void
    {
        $this->bs();
        $createUrl = route('transaksi_operasional.create');
        $storeUrl = route('transaksi_operasional.store');
        $this->post($storeUrl, $this->transactionPayload())->assertSessionHasNoErrors();
        $this->get($createUrl)->assertOk()->assertViewHas('usedDates', ['2026-09-16']);

        $this->from($createUrl)->post($storeUrl, $this->transactionPayload())
            ->assertRedirect($createUrl)->assertSessionHasErrors('tanggal');
        $this->assertDatabaseCount('transaksi_operasional_area_sales_header', 1);
        $this->assertDatabaseCount('transaksi_operasional_area_sales_detail', 1);

        $header = TransaksiOperasionalHeader::firstOrFail();
        $this->actingAs($this->transactionActionUser())->delete(route('transaksi_operasional.destroy', $header->id))->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail(1));
        $this->get($createUrl)->assertOk()->assertViewHas('usedDates', []);
        $this->post($storeUrl, $this->transactionPayload())->assertSessionHasNoErrors();
        $this->assertDatabaseCount('transaksi_operasional_area_sales_header', 1);
    }

    public function test_travel_calendar_blocks_only_the_current_users_transaction_dates(): void
    {
        $bs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $bs->members()->attach(3);
        $this->expense($bs, travel: true, attributes: ['tanggal' => '2026-09-16']);
        $this->expense($bs, status: 'VALIDATED', travel: true, attributes: ['tanggal' => '2026-09-14']);
        $this->expense($bs, status: 'PENDING_OWNER', travel: true, attributes: ['tanggal' => '2026-09-12']);
        $this->expense($bs, status: 'REJECTED_OWNER', travel: true, attributes: ['tanggal' => '2026-09-11']);
        $this->expense($bs, travel: true, attributes: ['user_id' => 3, 'bs_owner_id' => 1, 'tanggal' => '2026-09-15']);
        $this->expense($bs, attributes: ['tanggal' => '2026-09-13']);

        $this->get(route('transaksi_perjalanan_dinas.create'))->assertOk()
            ->assertViewHas('usedDates', ['2026-09-11', '2026-09-12', '2026-09-14', '2026-09-16'])
            ->assertSee('name="tanggal" class="form-control" value=""', false);

        $this->actingAs(User::findOrFail(3));
        $this->get(route('transaksi_perjalanan_dinas.create'))->assertOk()
            ->assertViewHas('usedDates', ['2026-09-15'])
            ->assertSee('name="tanggal" class="form-control" value="2026-09-16"', false);
    }

    public function test_travel_calendar_restores_only_an_unused_old_date(): void
    {
        $this->expense($this->bs(['tipe_pengajuan' => 'perjalanan_dinas']), travel: true, attributes: ['tanggal' => '2026-09-14']);

        $this->withSession(['_old_input' => ['tanggal' => '2026-09-14', 'description' => 'Kunjungan tersimpan']])
            ->get(route('transaksi_perjalanan_dinas.create'))->assertOk()
            ->assertSee('name="tanggal" class="form-control" value=""', false)
            ->assertSee('Kunjungan tersimpan');

        $this->withSession(['_old_input' => ['tanggal' => '2026-09-15']])
            ->get(route('transaksi_perjalanan_dinas.create'))->assertOk()
            ->assertSee('name="tanggal" class="form-control" value="2026-09-15"', false);
    }

    public function test_travel_calendar_tracks_saved_and_deleted_drafts_and_rejects_duplicate_submissions(): void
    {
        $bs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $bs->members()->attach(3);
        $createUrl = route('transaksi_perjalanan_dinas.create');
        $storeUrl = route('transaksi_perjalanan_dinas.store');
        $this->post($storeUrl, $this->transactionPayload(true))->assertSessionHasNoErrors();
        $this->get($createUrl)->assertOk()->assertViewHas('usedDates', ['2026-09-16']);

        $this->from($createUrl)->post($storeUrl, $this->transactionPayload(true))
            ->assertRedirect($createUrl)->assertSessionHasErrors('tanggal');
        $this->assertDatabaseCount('transaksi_perjalanan_dinas_header', 1);
        $this->assertDatabaseCount('transaksi_perjalanan_dinas_detail', 1);

        $header = TransaksiPerjalananDinasHeader::firstOrFail();
        $this->actingAs($this->transactionActionUser())->delete(route('transaksi_perjalanan_dinas.destroy', $header->transaksi_id))->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail(1));
        $this->get($createUrl)->assertOk()->assertViewHas('usedDates', []);
        $this->post($storeUrl, $this->transactionPayload(true))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('transaksi_perjalanan_dinas_header', 1);

        $this->actingAs(User::findOrFail(3));
        $this->post($storeUrl, $this->transactionPayload(true) + ['bs_owner_id' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('transaksi_perjalanan_dinas_header', 2);
    }

    private function transactionPayload(bool $travel = false): array
    {
        $data = [
            'tanggal' => '2026-09-16', 'description' => 'Biaya kunjungan',
            'details' => [['cost_category_id' => 1, 'remark' => 'Transport', 'nominal' => '150000.25']],
        ];
        return $travel ? $data + ['tujuan_dinas_id' => 1, 'teritory' => 'Jawa', 'jarak_km' => 100, 'durasi_jam' => 8] : $data;
    }

    private function archivedNonBsExpense(bool $travel = false, string $status = 'VALIDATED', string $date = '2026-09-16')
    {
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        $header = $model::create([
            'transaksi_id' => ($travel ? 'PD-' : 'OAS-').'ARSIP-'.uniqid(),
            'tanggal' => $date, 'periode' => substr($date, 0, 7), 'user_id' => 1,
            'bs_id' => null, 'status' => $status, 'description' => 'Transaksi historis',
        ]);
        $header->details()->create(['cost_category_id' => 1, 'remark' => 'Biaya lama', 'nominal' => '250000.00']);
        return $header;
    }

    private function archivedClaim($header, string $status = 'PAID', string $paymentDate = '2026-09-16')
    {
        return \App\Models\ReimbursementClaim::create([
            'transaction_type' => $header instanceof TransaksiPerjalananDinasHeader ? 'perjalanan_dinas' : 'operasional_sales',
            'transaction_id' => $header->id, 'document_no' => $header->transaksi_id, 'user_id' => 1,
            'expense_date' => $header->tanggal, 'amount' => '250000.00', 'items' => [],
            'status' => $status, 'submitted_by' => 1, 'submitted_at' => now(),
            'payment_date' => $status === 'PAID' ? $paymentDate : null,
        ]);
    }

    public function test_both_modules_use_pooled_funds_without_references_and_reject_obsolete_funding_requests(): void
    {
        foreach ([false, true] as $travel) {
            $base = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
            $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
            $data = $this->transactionPayload($travel);
            $bs = $this->bs(['tipe_pengajuan' => $travel ? 'operasional_sales' : 'perjalanan_dinas']);
            $this->postJson($base, $data + ['funding_source' => 'PERSONAL'])->assertUnprocessable()->assertJsonValidationErrors('funding_source');
            $this->postJson($base, $data + ['bs_id' => $bs->id])->assertUnprocessable()->assertJsonValidationErrors('bs_id');
            $this->assertSame(0, $model::count());
            $this->post($base, $data)->assertSessionHasNoErrors();
            $header = $model::firstOrFail();
            $url = $base.'/'.($travel ? $header->transaksi_id : $header->id);
            $this->put($url, $data)->assertSessionHasNoErrors();
            $this->putJson($url, $data + ['funding_source' => 'PERSONAL'])->assertUnprocessable()->assertJsonValidationErrors('funding_source');
            $this->putJson($url, $data + ['bs_id' => $bs->id])->assertUnprocessable()->assertJsonValidationErrors('bs_id');
            $this->assertEquals(1, $header->fresh()->bs_owner_id);
            $this->assertNull($header->fresh()->bs_id);
            $this->post($url.'/validate')->assertSessionHasNoErrors();
            $this->assertSame('VALIDATED', $header->fresh()->status);
        }
        $this->assertDatabaseCount('reimbursement_claims', 0);
    }

    public function test_legacy_non_bs_drafts_can_join_the_pool_but_final_archives_are_not_charged(): void
    {
        foreach ([false, true] as $travel) {
            $owner = $travel ? 3 : 1;
            $this->actingAs(User::findOrFail($owner));
            $header = $this->archivedNonBsExpense($travel, 'DRAFT');
            $header->update(['user_id' => $owner]);
            $archive = $this->archivedNonBsExpense($travel, 'VALIDATED', '2026-09-15');
            $archive->update(['user_id' => $owner]);
            $url = $travel ? '/transaksi-perjalanan-dinas/'.$header->transaksi_id : '/transaksi-operasional/'.$header->id;
            $this->postJson($url.'/validate')->assertUnprocessable()->assertJsonValidationErrors('bs_balance');
            $this->assertSame('DRAFT', $header->fresh()->status);
            $this->bs(['user_id' => $owner]);
            $this->post($url.'/validate')->assertSessionHasNoErrors();
            $this->assertSame('VALIDATED', $header->fresh()->status);
            $this->assertEquals($owner, $header->fresh()->bs_owner_id);
            $this->assertSame('0.00', app(BsCashFlowService::class)->calculate($owner, '2026-09')['total_keluar']);
            $this->assertNull($archive->fresh()->bs_owner_id);
        }
        $this->assertDatabaseCount('reimbursement_claims', 0);
    }

    public function test_funding_forms_show_pooled_balance_and_legacy_records_remain_readable(): void
    {
        foreach ([false, true] as $travel) {
            $base = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
            $header = $this->archivedNonBsExpense($travel, 'DRAFT');
            $url = $base.'/'.($travel ? $header->transaksi_id : $header->id);
            foreach ([$base.'/create', $url.'?edit=1'] as $page) {
                $response = $this->get($page)->assertOk()->assertSee('Sisa Saldo BS:')
                    ->assertDontSee('funding_source')->assertDontSee('PERSONAL')->assertDontSee('Reimbursement');
                $dom = new \DOMDocument();
                @$dom->loadHTML($response->getContent());
                $xpath = new \DOMXPath($dom);
                $this->assertSame(0, $xpath->query('//form//*[@name="bs_id"]')->length);
                $this->assertSame($travel ? 1 : 0, $xpath->query('//form//select[@name="bs_owner_id"]')->length);
            }
            $header->update(['status' => 'VALIDATED']);
            $claim = $this->archivedClaim($header);
            $this->get($url)->assertOk()->assertSee('Arsip tanpa BS')->assertDontSee('Reimbursement');
            $this->putJson($url, $this->transactionPayload($travel))->assertUnprocessable();
            $this->assertSame('VALIDATED', $header->fresh()->status);
            $this->assertSame('PAID', $claim->fresh()->status);
        }
    }

    public function test_removed_reimbursement_endpoints_cannot_read_or_change_archived_claims(): void
    {
        $claim = $this->archivedClaim($this->archivedNonBsExpense(), 'APPROVED');
        $before = $claim->fresh()->getAttributes();
        foreach ([1, 2] as $userId) {
            $this->actingAs(User::findOrFail($userId));
            foreach (['/reimbursements', '/reimbursements/'.$claim->id, '/reimbursements/'.$claim->id.'/payment-proofs/0'] as $url) {
                $this->get($url)->assertNotFound();
            }
            foreach (['approve', 'reject', 'pay'] as $action) {
                $this->post('/reimbursements/'.$claim->id.'/'.$action, ['payment_reference' => 'OLD-REQUEST'])->assertNotFound();
            }
        }
        $this->assertSame($before, $claim->fresh()->getAttributes());
    }

    public function test_cash_flow_excludes_archived_personal_expenses_and_payments_and_keeps_bs_and_events(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'CLOSED']);
        BsCashFlowSnapshot::create([
            'user_id' => 1, 'periode' => '2026-08', 'total_masuk' => 26000000, 'total_keluar' => 4900000,
            'saldo_akhir' => 21100000, 'opening_final' => true,
        ]);
        StockCashFlowSnapshot::create([
            'user_id' => 1, 'periode' => '2026-08', 'total_masuk' => 26000000, 'total_keluar_bs' => 4900000,
            'total_event_dalam' => 1950000, 'total_keluar' => 6850000, 'saldo_akhir' => 19150000, 'opening_final' => true,
        ]);
        $closedBefore = StockCashFlowSnapshot::where('periode', '2026-08')->firstOrFail()->getAttributes();
        $bs = $this->bs(['nominal_approved' => 9200000]);
        $this->expense($bs, 'VALIDATED', '1270000.00');
        DB::table('perongkosan_event_luar_kota')->insert([
            'user_id' => 1, 'tanggal' => '2026-09-16', 'periode' => '2026-09', 'nominal' => 200000,
        ]);
        $personal = $this->archivedNonBsExpense();
        $claim = $this->archivedClaim($personal);
        StockCashFlowSnapshot::create([
            'user_id' => 1, 'periode' => '2026-09', 'saldo_awal' => 19150000, 'total_masuk' => 9450000,
            'total_keluar' => 1720000, 'total_keluar_pribadi' => 250000,
            'total_masuk_reimbursement' => 250000, 'includes_personal_funds' => true,
        ]);
        $this->seedFinanceApprovals();
        foreach (['/dashboard/transaksi', '/cash-flow', '/cash-flow/202609'] as $page) {
            $this->get($page)->assertOk()->assertDontSee('Uang Pribadi')->assertDontSee('uang pribadi')
                ->assertDontSee('Reimbursement')->assertDontSee('/reimbursements');
        }
        $stock = StockCashFlowSnapshot::where('periode', '2026-09')->firstOrFail();
        $this->assertSame('19150000.00', $stock->saldo_awal);
        $this->assertSame('9200000.00', $stock->total_masuk);
        $this->assertSame('1470000.00', $stock->total_keluar);
        $this->assertSame('0.00', $stock->total_keluar_pribadi);
        $this->assertSame('0.00', $stock->total_masuk_reimbursement);
        $this->assertFalse($stock->includes_personal_funds);
        $this->assertNull($stock->saldo_akhir);
        $this->assertSame('26880000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '2026-09'));
        $this->assertSame($closedBefore, StockCashFlowSnapshot::where('periode', '2026-08')->firstOrFail()->getAttributes());

        $details = app(StockCashFlowService::class)->details(1, '2026-09');
        $this->assertCount(1, $details['masuk']);
        $this->assertCount(2, $details['keluar']);
        $book = $this->cashFlowWorkbook('/cash-flow/202609/export', 'stok-kas-202609.xlsx');
        $this->assertSame('K', $book->getSheet(0)->getHighestDataColumn());
        $this->assertEquals(9200000, $book->getSheet(0)->getCell('D2')->getValue());
        $this->assertEquals(1470000, $book->getSheet(0)->getCell('H2')->getValue());
        foreach ($book->getAllSheets() as $sheet) {
            $content = json_encode($sheet->toArray());
            $this->assertStringNotContainsString('Reimbursement', $content);
            $this->assertStringNotContainsString('Uang Pribadi', $content);
            $this->assertStringNotContainsString($personal->transaksi_id, $content);
        }
        $book->disconnectWorksheets();

        $this->travelTo(now()->setDate(2026, 10, 1));
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        $this->assertSame('26880000.00', $stock->fresh()->saldo_akhir);
        $this->assertSame('26880000.00', StockCashFlowSnapshot::where('user_id', 1)->where('periode', '2026-10')->firstOrFail()->saldo_awal);
        $this->assertSame('PAID', $claim->fresh()->status);
        $this->assertSame('VALIDATED', $personal->fresh()->status);
    }

    public function test_archived_reimbursement_payment_does_not_block_reopening_a_period(): void
    {
        $header = $this->archivedNonBsExpense(date: '2026-09-10');
        $this->archivedClaim($header, paymentDate: '2026-10-01');
        $this->travelTo(now()->setDate(2026, 10, 1));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        app(BsPeriodClosingService::class)->close($period->id);
        app(BsPeriodClosingService::class)->reopen($period->id, 'Koreksi BS');
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertDatabaseCount('reimbursement_claims', 1);
    }

    public function test_archived_reimbursement_notifications_are_hidden_without_being_deleted(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->string('type'); $table->morphs('notifiable');
            $table->text('data'); $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
        $user = User::findOrFail(1);
        for ($i = 0; $i < 9; $i++) {
            $user->notifications()->create([
                'id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'App\\Notifications\\ReimbursementUpdated',
                'data' => ['title' => 'Klaim lama', 'url' => '/reimbursements/1'],
            ]);
        }
        $current = $user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'App\\Notifications\\BsDisbursed',
            'data' => ['title' => 'BS cair', 'url' => '/pengajuan-bs/1'],
        ]);
        $this->getJson('/notifications/unread')->assertOk()->assertJsonPath('count', 1)
            ->assertJsonCount(1, 'items')->assertJsonPath('items.0.id', $current->id);
        $this->assertDatabaseCount('notifications', 10);
    }

    public function test_cash_flow_rows_open_their_source_documents_including_shared_bs(): void
    {
        $bs = $this->bs();
        $operational = $this->expense($bs, 'VALIDATED', '120000.00');
        $travelBs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $travelBs->members()->attach(3);
        $shared = $this->expense($travelBs, 'VALIDATED', '80000.00', travel: true, attributes: ['user_id' => 3]);
        $settled = $this->bs(['status' => 'SETTLED', 'settled_periode' => '2026-09', 'nominal_pengembalian' => '25000.00']);
        $this->actingAs(User::findOrFail(1));
        $localEvent = DB::table('perongkosan_event_dalam_kota')->insertGetId([
            'user_id' => 1, 'tanggal' => '2026-09-16', 'periode' => '2026-09', 'nominal' => '100000.00',
            'transaksi_id' => 'EV-DALAM', 'description' => 'Kegiatan dalam kota', 'jam_kerja_event_id' => 1,
        ]);
        $outsideEvent = DB::table('perongkosan_event_luar_kota')->insertGetId([
            'user_id' => 1, 'tanggal' => '2026-09-16', 'periode' => '2026-09', 'nominal' => '200000.25',
            'transaksi_id' => 'EV-LUAR', 'description' => 'Kegiatan luar kota', 'lokasi_event_id' => 1,
            'jam_kerja_event_id' => 1, 'foto_event_1' => 'events_luar_kota/bukti.png',
        ]);
        $this->seedFinanceApprovals();
        $response = $this->get('/cash-flow/202609')->assertOk();
        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $links = (new \DOMXPath($dom))->query('//a[contains(concat(" ", normalize-space(@class), " "), " trx-link ")]');
        $urls = [];
        foreach ($links as $link) $urls[] = $link->getAttribute('href');
        $expected = [
            route('pengajuan-bs.show', $bs->id), route('pengajuan-bs.show', $travelBs->id),
            route('pengajuan-bs.show', $settled->id),
            route('transaksi_operasional.show', $operational->id),
            route('transaksi_perjalanan_dinas.show', $shared->transaksi_id),
            route('perongkosan_event_dalam_kota.show', $localEvent),
            route('perongkosan_event_luar_kota.show', $outsideEvent),
        ];
        $this->assertCount(8, $urls); // The settled BS appears in both receipts and adjustments.
        $this->assertEqualsCanonicalizing($expected, array_values(array_unique($urls)));
        foreach ($expected as $url) $this->get($url)->assertOk();
        $this->get(route('perongkosan_event_luar_kota.show', $outsideEvent))->assertSeeText('EV-LUAR')->assertSeeText('Kegiatan luar kota')
            ->assertSeeText('Semarang')->assertSeeText('200.000,25')->assertSee('storage/events_luar_kota/bukti.png', false);
        $this->assertSame('VALIDATED', $shared->fresh()->status);

        // Following a source URL keeps the source module's ownership and permission checks.
        User::findOrFail(3)->givePermissionTo(['akses.event-dalam-kota', 'akses.event-luar-kota']);
        $this->actingAs(User::findOrFail(3));
        $this->get(route('perongkosan_event_luar_kota.show', $outsideEvent))->assertForbidden();
        $this->get(route('perongkosan_event_dalam_kota.show', $localEvent))->assertForbidden();
        User::findOrFail(1)->revokePermissionTo('akses.event-luar-kota');
        $this->actingAs(User::findOrFail(1));
        $this->get(route('perongkosan_event_luar_kota.show', $outsideEvent))->assertForbidden();
    }

    public function test_cash_flow_export_buttons_are_available_on_history_and_detail(): void
    {
        $this->get('/cash-flow')->assertOk()
            ->assertSeeText('Export Excel')
            ->assertSee(route('cash-flow.export'), false)
            ->assertSee(route('cash-flow.export-period', '202609'), false);
        $this->get('/cash-flow/202609')->assertOk()
            ->assertSee('Export Excel')->assertSee(route('cash-flow.export-period', '202609'), false);
    }

    public function test_cash_flow_period_export_contains_owned_cash_transactions_and_literal_text(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $bs = $this->bs([
            'document_no' => '=2+2', 'status' => 'SETTLED', 'settled_periode' => '2026-09',
            'nominal_reimburse' => '10.50', 'nominal_pengembalian' => '35.75',
        ]);
        $this->expense($bs, 'VALIDATED');
        $this->expense($bs, 'VALIDATED', '100000.25', travel: true, attributes: ['user_id' => 3]);
        $draft = $this->expense($bs, 'DRAFT', '900000.00');
        $unrelated = $this->expense($this->bs(['user_id' => 3]), 'VALIDATED', '999000.00');
        DB::table('perongkosan_event_dalam_kota')->insert([
            ['user_id' => 1, 'tanggal' => '2026-09-30', 'periode' => '2026-10', 'nominal' => '250000.25', 'transaksi_id' => '=1+1'],
            ['user_id' => 3, 'tanggal' => '2026-09-10', 'periode' => '2026-09', 'nominal' => '999000.00', 'transaksi_id' => 'OTHER-USER'],
            ['user_id' => 1, 'tanggal' => '2026-10-01', 'periode' => '2026-09', 'nominal' => '111000.00', 'transaksi_id' => 'OTHER-MONTH'],
        ]);
        DB::table('perongkosan_event_luar_kota')->insert([
            'user_id' => 1, 'tanggal' => '2026-09-01', 'periode' => '2026-09', 'nominal' => '249999.75',
            'transaksi_id' => 'EV-LKR-260901-001', 'description' => 'Event luar kota uji',
        ]);

        $this->seedFinanceApprovals();
        $workbook = $this->cashFlowWorkbook('/cash-flow/202609/export?user_id=3', 'stok-kas-202609.xlsx');
        $this->assertSame(['Ringkasan Stok Kas', 'Uang Masuk', 'Uang Keluar', 'Penyesuaian BS', 'Pemakaian BS Bersama'], $workbook->getSheetNames());
        $summary = $workbook->getSheet(0);
        $this->assertEquals([
            '202609', 'OPEN', 0, 1000000, 700000.25, 250000.25, 249999.75,
            1200000.25, -25.25, null, 'Menunggu penutupan periode sebelumnya',
        ], $summary->rangeToArray('A2:K2', null, false, false)[0]);
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC, $summary->getCell('C2')->getDataType());
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC, $summary->getCell('H2')->getDataType());
        $this->assertNull($summary->getCell('J2')->getValue());
        $this->assertSame('#,##0.00;[Red]-#,##0.00', $summary->getStyle('H2')->getNumberFormat()->getFormatCode());
        $this->assertSame('A2', $summary->getFreezePane());
        $this->assertSame('A1:K2', $summary->getAutoFilter()->getRange());

        $incoming = $workbook->getSheetByName('Uang Masuk');
        $this->assertEquals(['2026-09-01', 'BS Dicairkan', '=2+2', 1000000], $incoming->rangeToArray('A2:D2', null, false, false)[0]);
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $incoming->getCell('C2')->getDataType());
        $outgoing = $workbook->getSheetByName('Uang Keluar');
        $this->assertSame(5, $outgoing->getHighestDataRow());
        $this->assertEquals(1200000.25, array_sum(array_column($outgoing->rangeToArray('A2:D5', null, false, false), 3)));
        $this->assertStringStartsWith('=1+1', $outgoing->getCell('C5')->getValue());
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $outgoing->getCell('C5')->getDataType());
        $outgoingText = json_encode($outgoing->toArray(null, false, false));
        $this->assertStringContainsString('Anggota', $outgoingText);
        foreach ([$draft->transaksi_id, $unrelated->transaksi_id, 'OTHER-USER', 'OTHER-MONTH'] as $excluded) {
            $this->assertStringNotContainsString($excluded, $outgoingText);
        }
        $this->assertEquals(['=2+2', -25.25], $workbook->getSheetByName('Penyesuaian BS')->rangeToArray('A2:B2', null, false, false)[0]);
        $workbook->disconnectWorksheets();
    }

    public function test_cash_flow_history_export_preserves_closed_balances_and_user_scope(): void
    {
        DB::table('perongkosan_event_dalam_kota')->insert([
            'user_id' => 1, 'tanggal' => '2026-09-10', 'periode' => '2026-09', 'nominal' => '1234.56',
        ]);
        $this->bs(['user_id' => 3]);
        $this->seedFinanceApprovals();
        $this->travelTo(now()->setDate(2026, 10, 16));
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        $snapshot = StockCashFlowSnapshot::where('user_id', 1)->where('periode', '2026-09')->firstOrFail();
        $frozen = $snapshot->getAttributes();

        $workbook = $this->cashFlowWorkbook('/cash-flow/export?user_id=3', 'stok-kas-semua-periode.xlsx');
        $this->assertSame(['Ringkasan Stok Kas'], $workbook->getSheetNames());
        $sheet = $workbook->getSheet(0);
        $this->assertSame(3, $sheet->getHighestDataRow());
        $this->assertEquals(['202610', 'OPEN', -1234.56], $sheet->rangeToArray('A2:C2', null, false, false)[0]);
        $this->assertNull($sheet->getCell('J2')->getValue());
        $this->assertEquals([
            '202609', 'CLOSED', 0, 0, 0, 1234.56, 0, 1234.56, 0, -1234.56, 'Final',
        ], $sheet->rangeToArray('A3:K3', null, false, false)[0]);
        $this->assertSame($frozen, $snapshot->fresh()->getAttributes());
        $workbook->disconnectWorksheets();

        $workbook = $this->cashFlowWorkbook('/cash-flow/2026-09/export', 'stok-kas-202609.xlsx');
        $this->assertEquals(-1234.56, $workbook->getSheet(0)->getCell('J2')->getValue());
        $this->assertSame($frozen, $snapshot->fresh()->getAttributes());
        $workbook->disconnectWorksheets();
    }

    public function test_cash_flow_export_supports_empty_periods_and_zero_closing_balance(): void
    {
        $workbook = $this->cashFlowWorkbook('/cash-flow/2026-09/export', 'stok-kas-202609.xlsx');
        foreach (['Uang Masuk', 'Uang Keluar', 'Penyesuaian BS'] as $name) {
            $this->assertSame(1, $workbook->getSheetByName($name)->getHighestDataRow());
        }
        $this->assertEquals(0, $workbook->getSheet(0)->getCell('C2')->getValue());
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC, $workbook->getSheet(0)->getCell('C2')->getDataType());
        $this->assertNull($workbook->getSheet(0)->getCell('J2')->getValue());
        $workbook->disconnectWorksheets();

        $this->travelTo(now()->setDate(2026, 10, 16));
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        $workbook = $this->cashFlowWorkbook('/cash-flow/202609/export', 'stok-kas-202609.xlsx');
        $this->assertEquals(0, $workbook->getSheet(0)->getCell('J2')->getValue());
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC, $workbook->getSheet(0)->getCell('J2')->getDataType());
        $workbook->disconnectWorksheets();
    }

    public function test_cash_flow_export_rejects_invalid_periods_and_requires_permission(): void
    {
        foreach (['202613', 'invalid', '2026-00'] as $period) {
            $this->getJson('/cash-flow/'.$period.'/export')->assertUnprocessable()->assertJsonValidationErrors('periode');
        }
        User::findOrFail(1)->revokePermissionTo('akses.cash-flow');
        $this->actingAs(User::findOrFail(1));
        foreach (['/cash-flow/export', '/cash-flow/202609/export'] as $url) {
            $this->get($url)->assertForbidden();
        }
        auth()->logout();
        foreach (['/cash-flow/export', '/cash-flow/202609/export'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_drafts_do_not_reduce_cash_and_finalization_posts_once(): void
    {
        $header = $this->expense($this->bs());
        $cash = app(BsCashFlowService::class);
        $this->assertSame('0.00', $cash->calculate(1, '202609')['total_keluar']);
        $this->post('/transaksi-operasional/'.$header->id.'/validate')->assertSessionHasNoErrors();
        $this->assertSame('VALIDATED', $header->fresh()->status);
        $this->assertNull(BsCashFlowSnapshot::first()->saldo_akhir);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->post('/transaksi-operasional/'.$header->id.'/validate')->assertSessionHasErrors('error');
        $this->assertSame(1, DB::table('bs_usage_logs')->count());
        $this->assertNull(BsCashFlowSnapshot::first()->saldo_akhir);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
    }

    public function test_travel_uses_same_finalization_and_member_spending_belongs_to_owner(): void
    {
        $bs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $bs->members()->attach(3);
        $header = $this->expense($bs, travel: true, attributes: ['user_id' => 3]);
        $this->actingAs(User::findOrFail(3));
        $this->post('/transaksi-perjalanan-dinas/'.$header->transaksi_id.'/validate')->assertSessionHasNoErrors();
        $this->assertSame('PENDING_OWNER', $header->fresh()->status);
        $this->actingAs(User::findOrFail(1));
        $this->post(route('bs-usage-approvals.approve', $header->id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $this->assertSame('VALIDATED', $header->fresh()->status);
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar']);
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(3, '2026-09')['total_keluar']);
    }

    public function test_legacy_draft_uses_combined_receipts_instead_of_its_old_document_limit(): void
    {
        $small = $this->bs(['nominal_approved' => '100000.00']);
        $this->bs(['nominal_approved' => '900000.00', 'tipe_pengajuan' => 'perjalanan_dinas']);
        $header = $this->expense($small, amount: '500000.00');
        $this->post('/transaksi-operasional/'.$header->id.'/validate')->assertSessionHasNoErrors();
        $this->assertSame('VALIDATED', $header->fresh()->status);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
    }

    public function test_submission_detail_shows_application_and_disbursement_without_per_document_spending(): void
    {
        $bs = $this->bs();
        $validated = $this->expense($bs, 'VALIDATED', '600000.00');
        $draft = $this->expense($bs, 'DRAFT', '300000.00');
        $other = $this->expense($this->bs(), 'VALIDATED', '100000.00');

        $this->seedFinanceApprovals();
        $this->get(route('pengajuan-bs.show', $bs))
            ->assertOk()
            ->assertSee($bs->document_no)
            ->assertSee('Nominal Pengajuan')
            ->assertSee('Nominal Cair')
            ->assertDontSee('Rincian Pemakaian BS Ini')
            ->assertDontSee('Sisa Nominal BS Ini')
            ->assertDontSee($validated->transaksi_id)
            ->assertDontSee($draft->transaksi_id)
            ->assertDontSee($other->transaksi_id);
        $this->assertSame('1300000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo((int) $bs->user_id, '202609'));
    }

    public function test_member_can_read_submission_without_exposing_transaction_rows(): void
    {
        $bs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $bs->members()->attach(3);
        $ownerTransaction = $this->expense($bs, 'VALIDATED', '200000.00', travel: true);
        $memberTransaction = $this->expense($bs, 'VALIDATED', '100000.00', travel: true, attributes: ['user_id' => 3]);

        $this->actingAs(User::findOrFail(3));
        $this->get(route('pengajuan-bs.show', $bs))
            ->assertOk()
            ->assertSee($bs->document_no)
            ->assertSee('Anggota Grup Dinas:')
            ->assertDontSee('Rincian Pemakaian BS Ini')
            ->assertDontSee($memberTransaction->transaksi_id)
            ->assertDontSee($ownerTransaction->transaksi_id)
            ->assertDontSee('Sisa Nominal BS Ini');
    }

    public function test_failed_snapshot_write_rolls_back_finalization_and_audit(): void
    {
        $header = $this->expense($this->bs());
        $mock = \Mockery::mock(BsCashFlowService::class, [app(PeriodService::class)])->makePartial();
        $mock->shouldReceive('recalculateSnapshot')->andThrow(new \RuntimeException('Simulated write failure'));
        $this->app->instance(BsCashFlowService::class, $mock);
        try { app(BsValidationService::class)->finalize($header); $this->fail('Expected write failure'); }
        catch (\RuntimeException $e) { $this->assertSame('Simulated write failure', $e->getMessage()); }
        $this->assertSame('DRAFT', $header->fresh()->status);
        $this->assertSame(0, DB::table('bs_activity_logs')->count());
        $this->assertDatabaseCount('bs_usage_logs', 0);
    }

    public function test_finance_posts_to_actual_disbursement_month_and_cannot_repeat(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'CLOSED']);
        $bs = $this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-15', 'status' => 'APPROVED_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));
        $this->post('/approval-finance/'.$bs->id.'/disburse', ['nominal_approved' => '1000000'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('approval_bs_finance', ['pengajuan_bs_id' => $bs->id, 'periode' => '2026-09']);
        $this->assertSame(1, DB::table('approval_bs_finance')->whereDate('tanggal_cair', '2026-09-16')->count());
        $this->post('/approval-finance/'.$bs->id.'/disburse', ['nominal_approved' => '1000000'])->assertSessionHas('error');
        $this->assertSame(1, DB::table('approval_bs_finance')->count());
    }

    public function test_approval_and_rejection_do_not_post_cash(): void
    {
        $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));
        $this->post('/approval-atasan/'.$bs->id.'/approve')->assertSessionHasNoErrors();
        $this->assertSame('APPROVED_ATASAN', $bs->fresh()->status);
        Notification::assertSentTo(User::findOrFail(2), \App\Notifications\BsApproved::class);
        $this->post('/approval-atasan/'.$bs->id.'/reject', ['rejection_reason' => 'Terlambat'])->assertSessionHas('error');
        $this->post('/approval-finance/'.$bs->id.'/reject', ['rejection_reason' => 'Tidak dicairkan'])->assertSessionHasNoErrors();
        $this->assertSame('REJECTED_FINANCE', $bs->fresh()->status);
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(1, '202609')['total_masuk']);
    }

    public function test_unrelated_supervisor_cannot_approve(): void
    {
        $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $this->actingAs(User::findOrFail(3));
        $this->post('/approval-atasan/'.$bs->id.'/approve')->assertForbidden();
        $this->assertSame('PENDING_ATASAN', $bs->fresh()->status);
    }

    public function test_closed_period_rejects_finalization_and_draft_deletion(): void
    {
        $header = $this->expense($this->bs());
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $this->post('/transaksi-operasional/'.$header->id.'/validate')->assertSessionHasErrors('tanggal');
        $this->delete('/transaksi-operasional/'.$header->id)->assertSessionHasErrors('tanggal');
        $this->assertNotNull($header->fresh());
    }

    public function test_validated_travel_cannot_be_changed_or_deleted(): void
    {
        $header = $this->expense($this->bs(['tipe_pengajuan' => 'perjalanan_dinas']), 'VALIDATED', travel: true);
        $this->delete('/transaksi-perjalanan-dinas/'.$header->transaksi_id)->assertSessionHasErrors('error');
        $this->deleteJson('/transaksi-perjalanan-dinas/detail/'.$header->details->first()->id, ['reason' => 'Tidak boleh'])->assertUnprocessable();
        $this->putJson('/transaksi-perjalanan-dinas/'.$header->transaksi_id, ['tanggal' => '2026-09-02'])->assertUnprocessable();
        $this->postJson('/transaksi-perjalanan-dinas/'.$header->transaksi_id.'/add-detail', ['details' => [['cost_category_id' => 1, 'nominal' => 1, 'remark' => 'Tidak boleh']]])->assertUnprocessable();
        $this->assertNotNull($header->fresh());
        $this->assertSame(1, $header->details()->count());
    }

    public function test_rollover_waits_for_close_and_preserves_empty_months(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 16));
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $bs = $this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-10']);
        $this->expense($bs, 'VALIDATED');
        $this->seedFinanceApprovals('2026-08');
        $cash = app(BsCashFlowService::class);
        $this->assertSame('0.00', $cash->calculate(1, '202609')['saldo_awal']);
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-08')->value('id'));
        $this->assertSame('400000.00', $cash->calculate(1, '202609')['saldo_awal']);
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        $this->assertSame('400000.00', $cash->calculate(1, '202610')['saldo_awal']);
    }

    public function test_close_rejects_out_of_order_months_and_pending_drafts(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 16));
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        try { app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id')); $this->fail('Expected order guard'); }
        catch (ValidationException $e) { $this->assertStringContainsString('sebelumnya', $e->getMessage()); }
        $bs = $this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-10']);
        $this->expense($bs);
        $this->expectException(ValidationException::class);
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-08')->value('id'));
    }

    public function test_closed_bs_snapshot_is_not_rewritten_by_a_recalculation(): void
    {
        $bs = $this->bs();
        $cash = app(BsCashFlowService::class);
        $cash->recalculateSnapshot(1, '202609');
        $this->travelTo(now()->setDate(2026, 10, 16));
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        DB::table('approval_bs_finance')->where('pengajuan_bs_id', $bs->id)->update(['nominal_approved' => 2000000]);
        $this->assertSame('1000000.00', $cash->recalculateSnapshot(1, '202609')->saldo_akhir);
    }

    public function test_event_and_non_bs_travel_do_not_reduce_bs_cash(): void
    {
        $bs = $this->bs();
        $this->expense($bs, 'VALIDATED', travel: true, attributes: ['bs_id' => null]);
        DB::table('perongkosan_event_dalam_kota')->insert(['user_id' => 1, 'tanggal' => '2026-09-10', 'periode' => '2026-09', 'nominal' => 200000]);
        $this->assertNull(app(BsCashFlowService::class)->calculate(1, '202609')['saldo_akhir']);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertSame(1, DB::table('perongkosan_event_dalam_kota')->count());
    }

    public function test_existing_settlement_adjustments_are_preserved_separately(): void
    {
        $bs = $this->bs(['status' => 'SETTLED', 'settled_periode' => '2026-09', 'nominal_pengembalian' => '400000.00']);
        $this->expense($bs, 'VALIDATED');
        $this->seedFinanceApprovals();
        $result = app(BsCashFlowService::class)->calculate(1, '202609');
        $this->assertSame('600000.00', $result['total_keluar']);
        $this->assertSame('-400000.00', $result['penyesuaian']);
        $this->assertNull($result['saldo_akhir']);
        $this->assertSame('0.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
    }

    public function test_paused_settlement_rejects_even_authorized_finance_without_changing_balances(): void
    {
        $this->assertFalse(config('bs.settlement_enabled'));
        DB::table('roles')->insert(['id' => 1, 'code' => 'FINANCE']);
        User::findOrFail(2)->update(['roles_id' => 1]);
        $this->actingAs(User::findOrFail(2));
        $bs = $this->bs();
        $this->expense($bs, 'VALIDATED');
        $this->seedFinanceApprovals();
        $before = $bs->fresh()->getAttributes();
        $cash = app(BsCashFlowService::class);
        $snapshot = $cash->recalculateSnapshot(1, '202609')->fresh()->getAttributes();

        $this->postJson(route('laporan-bs.settle', $bs))->assertForbidden()
            ->assertJsonPath('message', 'Settlement per pengajuan tidak berlaku pada saldo BS gabungan.');

        $this->assertSame($before, $bs->fresh()->getAttributes());
        $this->assertSame($snapshot, BsCashFlowSnapshot::firstOrFail()->getAttributes());
        $this->assertNull($cash->calculate(1, '202609')['saldo_akhir']);
        $this->assertSame('400000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertDatabaseCount('bs_activity_logs', 0);
        $this->assertDatabaseCount('bs_usage_logs', 0);
        Notification::assertNothingSent();
    }

    public function test_paused_settlement_report_hides_actions_and_keeps_historical_adjustments(): void
    {
        DB::table('roles')->insert(['id' => 1, 'code' => 'FINANCE']);
        User::findOrFail(2)->update(['roles_id' => 1]);
        $this->actingAs(User::findOrFail(2));
        $open = $this->bs();
        $returned = $this->bs(['status' => 'SETTLED', 'settled_periode' => '2026-09', 'nominal_pengembalian' => '400000.00']);
        $this->expense($returned, 'VALIDATED');
        $reimbursed = $this->bs(['user_id' => 3, 'status' => 'SETTLED', 'settled_periode' => '2026-09', 'nominal_reimburse' => '200000.00']);
        $this->expense($reimbursed, 'VALIDATED', '1200000.00');

        $this->seedFinanceApprovals();
        $this->get(route('laporan-bs.index'))->assertOk()
            ->assertSee('Semua pencairan masuk ke saldo BS gabungan.')
            ->assertSee('Dana masuk ke saldo BS gabungan')->assertDontSee('Total Realisasi')->assertDontSee('Sisa Akhir')->assertSee('User setor')->assertSee('Reimburse')
            ->assertSee($returned->document_no)->assertSee($reimbursed->document_no)
            ->assertDontSee('User wajib setor')->assertDontSee('Company reimburse')
            ->assertDontSee('action="'.route('laporan-bs.settle', $open).'"', false);
        $cash = app(BsCashFlowService::class);
        $this->assertNull($cash->calculate(1, '202609')['saldo_akhir']);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertSame('200000.00', $cash->calculate(3, '202609')['penyesuaian']);
        $this->assertNull($cash->calculate(3, '202609')['saldo_akhir']);
        $this->assertSame('0.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(3, '202609'));
    }

    public function test_period_math_is_independent_of_the_day_and_money_is_exact(): void
    {
        $this->travelTo(now()->setDate(2026, 1, 31));
        $p = app(PeriodService::class);
        $this->assertSame('2026-01', $p->previous('202602'));
        $this->assertSame('2026-02', $p->next('202601'));
        $this->assertSame('2027-01', $p->next('202612'));
        $this->assertSame('2024-02', $p->previous('202403'));
        $this->assertSame('0.30', Money::decimal(Money::minor('0.10') + Money::minor('0.20')));
        $this->expectException(ValidationException::class);
        $p->normalize('202613');
    }

    public function test_submission_approval_disbursement_and_operational_finalization_end_to_end(): void
    {
        $this->post('/pengajuan-bs', ['tipe_pengajuan' => 'operasional_sales', 'nominal_pengajuan' => '1000000', 'description' => 'Operasional'])->assertSessionHasNoErrors();
        $bs = PengajuanBs::firstOrFail();
        $this->assertSame('PENDING_ATASAN', $bs->status);
        $this->assertSame('BS-20260916-001', $bs->document_no);
        $this->actingAs(User::findOrFail(2));
        $this->post('/approval-atasan/'.$bs->id.'/approve')->assertSessionHasNoErrors();
        $this->post('/approval-finance/'.$bs->id.'/disburse', ['nominal_approved' => '1000000'])->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail(1));
        $this->post('/transaksi-operasional', ['tanggal' => '2026-09-16', 'funding_source' => 'BS', 'bs_owner_id' => $bs->user_id, 'description' => 'Kunjungan',
            'details' => [['cost_category_id' => 1, 'remark' => 'Transport', 'nominal' => '600000']]])->assertSessionHasNoErrors();
        $header = TransaksiOperasionalHeader::firstOrFail();
        $this->assertSame('OAS26091601', $header->transaksi_id);
        $this->assertSame('CAIR-20260916-001', $bs->financeApprovals()->firstOrFail()->document_no);
        $this->assertSame($header->transaksi_id, $header->details()->firstOrFail()->transaksi_id);
        $this->assertNull(app(BsCashFlowService::class)->calculate(1, '202609')['saldo_akhir']);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->post('/transaksi-operasional/'.$header->id.'/validate')->assertSessionHasNoErrors();
        $this->assertFinanceCash('0.00', '1000000.00');
        $this->confirmUsageFinance($header);
        $this->assertNull(BsCashFlowSnapshot::firstOrFail()->saldo_akhir);
        $this->assertSame('400000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
    }

    public static function confirmationModules(): array
    {
        return ['operasional' => [false], 'perjalanan dinas' => [true]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('confirmationModules')]
    public function test_save_draft_and_default_save_do_not_post_cash(bool $travel): void
    {
        $bs = $this->bs(['tipe_pengajuan' => $travel ? 'perjalanan_dinas' : 'operasional_sales']);
        $url = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        foreach ([null, 'draft'] as $index => $action) {
            $data = $this->transactionPayload($travel) + ['bs_owner_id' => $bs->user_id];
            $data['tanggal'] = '2026-09-'.(15 + $index);
            $data['details'][0]['nominal'] = '1200000';
            if ($action !== null) $data['save_action'] = $action;
            $this->post($url, $data)->assertRedirect($url)->assertSessionHasNoErrors();
            $header = $model::where('tanggal', $data['tanggal'])->firstOrFail();
            $this->assertSame('DRAFT', $header->status);
            $this->assertNull($header->validated_at);
            $this->assertNull($header->validated_by);
        }
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar']);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        $this->assertDatabaseCount('bs_usage_logs', 0);
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('confirmationModules')]
    public function test_save_and_confirm_posts_once_and_locks_the_new_transaction(bool $travel): void
    {
        $bs = $this->bs(['tipe_pengajuan' => $travel ? 'perjalanan_dinas' : 'operasional_sales']);
        $url = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        $data = $this->transactionPayload($travel) + ['bs_owner_id' => $bs->user_id, 'save_action' => 'confirm'];
        $this->post($url, $data)->assertRedirect($url)->assertSessionHasNoErrors();
        $header = $model::firstOrFail();
        $detailUrl = $url.'/'.($travel ? $header->transaksi_id : $header->id);
        $this->assertSame('VALIDATED', $header->status);
        $this->assertTrue($header->validated_at->equalTo(now()));
        $this->assertEquals(1, $header->validated_by);
        $this->assertSame('0.00', (string) BsCashFlowSnapshot::firstOrFail()->total_keluar);
        $this->assertSame('0.00', (string) StockCashFlowSnapshot::firstOrFail()->total_keluar);
        $this->get($detailUrl)->assertOk()->assertSee('Dikonfirmasi')->assertSee('Waktu Konfirmasi')
            ->assertDontSee('action="'.url($detailUrl.'/validate').'"', false);
        $this->get($url)->assertOk()->assertSee('Dikonfirmasi');

        $this->post($detailUrl.'/validate')->assertSessionHasErrors('error');
        $this->put($detailUrl, $data)->assertSessionHasErrors('error');
        $this->delete($detailUrl)->assertSessionHasErrors('error');
        $this->post($url, $data)->assertSessionHasErrors('tanggal');
        $this->assertSame(1, $model::count());
        $this->assertSame(1, $header->details()->count());
        $this->assertSame(1, DB::table('bs_usage_logs')->count());
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '2026-09'));
        $this->assertSame(0, DB::transactionLevel());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('confirmationModules')]
    public function test_failed_direct_confirmation_keeps_input_and_can_be_saved_as_draft(bool $travel): void
    {
        $bs = $this->bs(['tipe_pengajuan' => $travel ? 'perjalanan_dinas' : 'operasional_sales']);
        $url = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        $data = $this->transactionPayload($travel) + ['bs_owner_id' => $bs->user_id, 'save_action' => 'confirm'];
        $data['details'] = [
            ['cost_category_id' => 1, 'remark' => 'Transport kunjungan uji', 'nominal' => '1200000'],
            ['cost_category_id' => 1, 'remark' => 'Parkir kunjungan uji', 'nominal' => '25000'],
        ];
        $this->from($url.'/create')->post($url, $data)->assertRedirect($url.'/create')
            ->assertSessionHasErrors('bs_balance')->assertSessionHasInput('details', $data['details']);
        $this->assertSame(0, $model::count());
        $this->assertDatabaseCount($travel ? 'transaksi_perjalanan_dinas_detail' : 'transaksi_operasional_area_sales_detail', 0);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        $this->assertDatabaseCount('bs_usage_logs', 0);
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
        $this->assertSame(0, DB::transactionLevel());
        $this->get($url.'/create')->assertOk()->assertSee('Transport kunjungan uji')->assertSee('Parkir kunjungan uji')
            ->assertSee('value="1200000"', false)->assertSee('value="25000"', false);

        $data['save_action'] = 'draft';
        $this->post($url, $data)->assertSessionHasNoErrors();
        $this->assertSame('DRAFT', $model::firstOrFail()->status);
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('confirmationModules')]
    public function test_direct_confirmation_rejects_invalid_action_and_zero_total(bool $travel): void
    {
        $bs = $this->bs(['tipe_pengajuan' => $travel ? 'perjalanan_dinas' : 'operasional_sales']);
        $url = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        $data = $this->transactionPayload($travel) + ['bs_owner_id' => $bs->user_id, 'save_action' => 'invalid'];
        $this->post($url, $data)->assertSessionHasErrors('save_action');
        $data['save_action'] = 'confirm';
        $data['details'][0]['nominal'] = '0';
        $this->post($url, $data)->assertSessionHasErrors('details');
        $this->assertSame(0, $model::count());
        $this->assertDatabaseCount('bs_activity_logs', 0);
        $this->assertDatabaseCount('bs_usage_logs', 0);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('confirmationModules')]
    public function test_direct_confirmation_requires_cash_and_authorized_owner_and_open_period(bool $travel): void
    {
        $url = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        $this->bs(['user_id' => 3]);
        $data = $this->transactionPayload($travel) + ['save_action' => 'confirm'];
        $this->post($url, $data + ['bs_owner_id' => 3])->assertSessionHasErrors('bs_owner_id');
        $this->bs(['status' => 'APPROVED_ATASAN'], paid: false);
        $this->post($url, $data)->assertSessionHasErrors('bs_balance');
        $this->bs(['tipe_pengajuan' => $travel ? 'operasional_sales' : 'perjalanan_dinas']);
        Periode::updateOrCreate(['periode' => '2026-09'], ['status' => 'CLOSED']);
        $this->post($url, $data)->assertSessionHasErrors('tanggal');
        $this->assertSame(0, $model::count());
        $this->assertDatabaseCount('bs_usage_logs', 0);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('confirmationModules')]
    public function test_direct_confirmation_rolls_back_document_audit_and_cash_if_posting_fails(bool $travel): void
    {
        $bs = $this->bs(['tipe_pengajuan' => $travel ? 'perjalanan_dinas' : 'operasional_sales']);
        $url = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
        $model = $travel ? TransaksiPerjalananDinasHeader::class : TransaksiOperasionalHeader::class;
        $mock = \Mockery::mock(StockCashFlowService::class);
        $mock->shouldReceive('recalculateSnapshot')->once()->andThrow(new \RuntimeException('Simulated write failure'));
        $this->app->instance(StockCashFlowService::class, $mock);
        $this->post($url, $this->transactionPayload($travel) + ['bs_owner_id' => $bs->user_id, 'save_action' => 'confirm'])
            ->assertSessionHasErrors('error');
        $this->assertSame(0, $model::count());
        $this->assertDatabaseCount($travel ? 'transaksi_perjalanan_dinas_detail' : 'transaksi_operasional_area_sales_detail', 0);
        $this->assertDatabaseCount('bs_activity_logs', 0);
        $this->assertDatabaseCount('bs_usage_logs', 0);
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
        $this->assertSame(0, DB::transactionLevel());
        Notification::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('confirmationModules')]
    public function test_transaction_forms_offer_draft_and_confirmation_and_existing_drafts_can_be_confirmed(bool $travel): void
    {
        $url = $travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional';
        $this->get($url.'/create')->assertOk()->assertSee('name="save_action" value="draft"', false)
            ->assertSee('name="save_action" value="confirm"', false)->assertSee('Simpan Draft')
            ->assertSee('Simpan &amp; Konfirmasi', false)->assertDontSee('Validasi &amp; Submit', false);
        $bs = $this->bs(['tipe_pengajuan' => $travel ? 'perjalanan_dinas' : 'operasional_sales']);
        $header = $this->expense($bs, travel: $travel);
        $detailUrl = $url.'/'.($travel ? $header->transaksi_id : $header->id);
        $this->get($detailUrl)->assertOk()->assertSee('Konfirmasi')
            ->assertSee('action="'.url($detailUrl.'/validate').'"', false)->assertDontSee('Validasi &amp; Submit', false);
        $this->post($detailUrl.'/validate')->assertSessionHasNoErrors();
        $this->assertSame('VALIDATED', $header->fresh()->status);
    }

    public function test_travel_member_can_save_and_confirm_against_the_bs_owners_cash(): void
    {
        $bs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $bs->members()->attach(3);
        $this->actingAs(User::findOrFail(3));
        $this->post('/transaksi-perjalanan-dinas', $this->transactionPayload(true) + ['bs_owner_id' => $bs->user_id, 'save_action' => 'confirm'])
            ->assertSessionHasNoErrors();
        $header = TransaksiPerjalananDinasHeader::firstOrFail();
        $this->assertSame('PENDING_OWNER', $header->status);
        $this->assertNull($header->validated_by);
        $this->actingAs(User::findOrFail(1));
        $this->post(route('bs-usage-approvals.approve', $header->id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $this->assertSame('VALIDATED', $header->fresh()->status);
        $this->assertEquals(1, $header->fresh()->validated_by);
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(1, '2026-09')['total_keluar']);
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(3, '2026-09')['total_keluar']);
    }

    public function test_travel_draft_can_exceed_balance_but_finalization_cannot(): void
    {
        $bs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $data = ['tanggal' => '2026-09-16', 'funding_source' => 'BS', 'bs_owner_id' => $bs->user_id, 'description' => 'Kunjungan', 'tujuan_dinas_id' => 1,
            'teritory' => 'Jawa', 'jarak_km' => 300, 'durasi_jam' => 10,
            'details' => [['cost_category_id' => 1, 'remark' => 'Transport', 'nominal' => '1200000']]];
        $this->post('/transaksi-perjalanan-dinas', $data)->assertSessionHasNoErrors();
        $header = TransaksiPerjalananDinasHeader::firstOrFail();
        $this->assertSame('DRAFT', $header->status);
        $this->assertSame('PD26091601', $header->transaksi_id);
        $this->post('/transaksi-perjalanan-dinas/'.$header->transaksi_id.'/validate')->assertSessionHasErrors('bs_balance');
        $this->assertSame('DRAFT', $header->fresh()->status);
        $this->assertSame(0, DB::transactionLevel());
        $this->assertSame('0.00', app(BsCashFlowService::class)->calculate(1, '202609')['total_keluar']);
    }

    public function test_dashboard_combines_bs_and_events_and_refreshes_an_existing_open_snapshot(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'CLOSED']);
        BsCashFlowSnapshot::create([
            'user_id' => 1, 'periode' => '2026-08', 'saldo_akhir' => '21100000.00', 'opening_final' => true,
        ]);
        $bs = $this->bs(['nominal_approved' => '6700000.00']);
        $bs->members()->attach(3);
        $this->expense($bs, 'VALIDATED', '400000.00');
        $this->expense($bs, 'VALIDATED', '170000.00', travel: true, attributes: ['user_id' => 3]);
        $this->expense($bs, 'DRAFT', '1000000.00');
        DB::table('perongkosan_event_dalam_kota')->insert([
            'user_id' => 1, 'tanggal' => '2026-09-10', 'periode' => '2026-09', 'nominal' => 3500000,
        ]);
        DB::table('cash_flow_snapshots')->insert([
            'user_id' => 1, 'periode' => '2026-09', 'saldo_akhir' => 23475000,
            'total_masuk' => 31700000, 'total_keluar' => 5070000,
        ]);
        BsCashFlowSnapshot::create(['user_id' => 1, 'periode' => '2026-09', 'saldo_akhir' => '123.00']);

        $this->seedFinanceApprovals();
        $this->get('/dashboard/transaksi')->assertOk()
            ->assertSee('+Rp 6.700.000')->assertSee('-Rp 4.070.000')
            ->assertSeeInOrder(['Stok Kas', '202609', 'Saldo Awal', 'Rp 21.100.000', 'Total Masuk', '+Rp 6.700.000'])
            ->assertDontSee('Saldo Akhir')
            ->assertDontSee('Rp 23.475.000')->assertDontSee('menunggu penutupan');
        $dashboardSnapshot = StockCashFlowSnapshot::where('user_id', 1)->where('periode', '2026-09')->firstOrFail();
        $this->assertNull($dashboardSnapshot->saldo_akhir);
        $this->assertSame('23730000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertSame('21100000.00', $dashboardSnapshot->saldo_awal);
        $this->assertSame('570000.00', $dashboardSnapshot->total_keluar_bs);
        $this->assertSame('3500000.00', $dashboardSnapshot->total_event_dalam);
        $this->assertSame('4070000.00', $dashboardSnapshot->total_keluar);
        $this->assertSame('4070000.00', BsCashFlowSnapshot::where('periode', '2026-09')->firstOrFail()->total_keluar);
        $this->get('/cash-flow')->assertOk()->assertViewHas('currentSnapshot',
            fn ($snapshot) => $snapshot->only(['saldo_awal', 'total_masuk', 'total_keluar', 'saldo_akhir'])
                === $dashboardSnapshot->only(['saldo_awal', 'total_masuk', 'total_keluar', 'saldo_akhir'])
        );
        $this->assertDatabaseHas('cash_flow_snapshots', [
            'user_id' => 1, 'periode' => '2026-09', 'saldo_akhir' => 23475000,
            'total_masuk' => 31700000, 'total_keluar' => 5070000,
        ]);

        $this->expense($bs, 'VALIDATED', '30000.00');
        $this->seedFinanceApprovals();
        $this->get('/dashboard/transaksi')->assertOk()
            ->assertSee('-Rp 4.100.000')->assertDontSee('Saldo Akhir');
    }

    public function test_dashboard_creates_bs_summary_with_pending_opening_and_settlement_adjustment(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $this->bs(['status' => 'SETTLED', 'settled_periode' => '2026-09', 'nominal_pengembalian' => '400000.00']);

        $this->get('/dashboard/transaksi')->assertOk()
            ->assertSeeInOrder(['Stok Kas', '202609', 'Saldo awal menunggu penutupan periode sebelumnya.',
                'Penyesuaian BS: -Rp 400.000'])
            ->assertDontSee('Saldo Akhir')
            ->assertSee('+Rp 1.000.000')->assertSee('-Rp 0');
        $this->assertNull(BsCashFlowSnapshot::firstOrFail()->saldo_akhir);
        $this->assertSame('600000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertDatabaseCount('cash_flow_snapshots', 0);
    }

    public function test_dashboard_preserves_the_closed_bs_snapshot(): void
    {
        $bs = $this->bs();
        Periode::where('periode', '2026-09')->update(['status' => 'CLOSED']);
        $snapshot = app(BsCashFlowService::class)->recalculateSnapshot(1, '202609')->fresh();
        DB::table('approval_bs_finance')->where('pengajuan_bs_id', $bs->id)->update(['nominal_approved' => 2000000]);

        $this->get('/dashboard/transaksi')->assertOk()
            ->assertSeeInOrder(['Stok Kas', 'Saldo Awal', 'Rp 0', 'Total Masuk', '+Rp 1.000.000']);
        $this->assertSame($snapshot->getAttributes(), $snapshot->fresh()->getAttributes());
        $this->get('/cash-flow')->assertOk()->assertViewHas('currentSnapshot',
            fn ($current) => $current->saldo_akhir === $snapshot->saldo_akhir
        );
        $this->assertDatabaseCount('cash_flow_snapshots', 0);
    }

    public function test_dashboard_does_not_build_cash_summary_without_permission(): void
    {
        $user = User::findOrFail(1);
        $user->revokePermissionTo('akses.cash-flow');
        $this->actingAs($user);

        $this->get('/dashboard/transaksi')->assertOk()->assertDontSee('Lihat Detail');
        $this->assertDatabaseCount('bs_cash_flow_snapshots', 0);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
        $this->assertDatabaseCount('cash_flow_snapshots', 0);
    }

    public function test_pages_render_with_pending_opening_and_final_travel_actions_hidden(): void
    {
        Periode::create(['periode' => '2026-08', 'status' => 'OPEN']);
        $bs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $header = $this->expense($bs, travel: true);
        $this->get('/cash-flow')->assertOk()->assertSee('Stok Kas')->assertSee('202609')->assertSee('menunggu penutupan');
        $this->get('/cash-flow/202609')->assertOk()->assertSee('Saldo awal menunggu penutupan')->assertDontSee('Saldo Akhir');
        $this->get('/transaksi-perjalanan-dinas/'.$header->transaksi_id)->assertOk()->assertSee('Konfirmasi');
        $header->update(['status' => 'VALIDATED']);
        $this->get('/transaksi-perjalanan-dinas/'.$header->transaksi_id)->assertOk()->assertDontSee('action="'.route('transaksi_perjalanan_dinas.validate', $header->transaksi_id).'"', false);
        $this->get('/transaksi-perjalanan-dinas')->assertOk()->assertSee('Dikonfirmasi');
        $this->get('/transaksi-operasional')->assertOk();
    }

    public function test_owner_can_read_member_document_but_cannot_mutate_it(): void
    {
        $bs = $this->bs(['tipe_pengajuan' => 'perjalanan_dinas']);
        $bs->members()->attach(3);
        $header = $this->expense($bs, travel: true, attributes: ['user_id' => 3]);
        $this->get('/transaksi-perjalanan-dinas/'.$header->transaksi_id)->assertOk();
        $this->get('/transaksi-perjalanan-dinas')->assertOk()->assertSee($header->transaksi_id);
        $this->post('/transaksi-perjalanan-dinas/'.$header->transaksi_id.'/validate')->assertForbidden();
        $this->delete('/transaksi-perjalanan-dinas/'.$header->transaksi_id)->assertForbidden();
        $this->assertSame('DRAFT', $header->fresh()->status);
    }

    public function test_finance_permission_is_required_and_rejected_bs_cannot_disburse(): void
    {
        $bs = $this->bs(['status' => 'APPROVED_ATASAN'], false);
        $this->post('/approval-finance/'.$bs->id.'/disburse', ['nominal_approved' => 1000000])->assertForbidden();
        $this->actingAs(User::findOrFail(2));
        $bs->update(['status' => 'REJECTED_ATASAN']);
        $this->post('/approval-finance/'.$bs->id.'/disburse', ['nominal_approved' => 1000000])->assertSessionHas('error');
        $this->assertSame(0, DB::table('approval_bs_finance')->count());
    }

    public function test_controller_error_rolls_back_status_and_does_not_leave_a_transaction_open(): void
    {
        $bs = $this->bs(['status' => 'PENDING_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));
        \App\Models\BsActivityLog::creating(fn () => throw new \RuntimeException('Simulated audit failure'));
        try { $this->post('/approval-atasan/'.$bs->id.'/approve')->assertStatus(500); }
        finally { \App\Models\BsActivityLog::flushEventListeners(); }
        $this->assertSame('PENDING_ATASAN', $bs->fresh()->status);
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_second_draft_cannot_spend_the_same_balance(): void
    {
        $bs = $this->bs();
        $one = $this->expense($bs);
        $two = $this->expense($bs);
        $this->post('/transaksi-operasional/'.$one->id.'/validate')->assertSessionHasNoErrors();
        $this->confirmUsageFinance($one);
        $this->post('/transaksi-operasional/'.$two->id.'/validate')->assertSessionHasErrors('bs_balance');
        $this->assertSame('DRAFT', $two->fresh()->status);
        $this->assertNull(BsCashFlowSnapshot::firstOrFail()->saldo_akhir);
        $this->assertSame('400000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
    }

    public function test_period_close_and_reopen_routes_recalculate_rollover(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 16));
        $bs = $this->bs();
        $this->expense($bs, 'VALIDATED');
        $this->seedFinanceApprovals();
        $cash = app(BsCashFlowService::class);
        $snapshot = $cash->recalculateSnapshot(1, '202609');
        app(\App\Services\CashFlowService::class)->recalculateSnapshot(1, '202609');
        $this->assertNull($snapshot->saldo_akhir);
        $this->assertDatabaseHas('cash_flow_snapshots', ['user_id' => 1, 'periode' => '2026-09', 'saldo_akhir' => null]);
        $this->actingAs(User::findOrFail(2));
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $this->post('/periode/'.$period->id.'/close')->assertSessionHasNoErrors();
        $this->assertSame('CLOSED', $period->fresh()->status);
        $this->assertSame('400000.00', $snapshot->fresh()->saldo_akhir);
        $this->assertSame('400000.00', $cash->calculate(1, '202610')['saldo_awal']);
        $this->assertNull($cash->calculate(1, '202610')['saldo_akhir']);
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $table) {
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-09', 'saldo_akhir' => 400000]);
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-10', 'saldo_awal' => 400000, 'saldo_akhir' => null]);
        }
        $this->actingAs(User::findOrFail(1));
        $this->get('/cash-flow/202609')->assertOk()->assertSeeInOrder(['Saldo Akhir', 'Rp 400.000']);
        $this->get('/cash-flow')->assertOk()->assertSeeInOrder(['202609', '>Akhir<', 'Rp 400.000'], false);
        $this->get('/cash-flow/202610')->assertOk()->assertDontSeeText('Saldo Akhir');

        $this->actingAs(User::findOrFail(2));
        $this->post('/periode/'.$period->id.'/reopen', ['reason' => 'Koreksi periode'])->assertSessionHasNoErrors();
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertNull($snapshot->fresh()->saldo_akhir);
        $this->assertSame('0.00', $cash->calculate(1, '202610')['saldo_awal']);
        $this->assertFalse($cash->calculate(1, '202610')['opening_final']);
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $table) {
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-09', 'saldo_akhir' => null]);
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-10', 'saldo_awal' => 0, 'saldo_akhir' => null]);
        }
        $this->actingAs(User::findOrFail(1));
        $this->get('/cash-flow/202609')->assertOk()->assertDontSeeText('Saldo Akhir');
        $this->get('/cash-flow')->assertOk()->assertDontSeeText('Saldo Akhir');
        $header = $this->expense($bs, amount: '150000.25');
        $this->post('/transaksi-operasional/'.$header->id.'/validate')->assertSessionHasNoErrors();
        $this->confirmUsageFinance($header);
        $this->assertNull($snapshot->fresh()->saldo_akhir);

        $this->actingAs(User::findOrFail(2));
        $this->post('/periode/'.$period->id.'/close')->assertSessionHasNoErrors();
        $this->assertSame('249999.75', $snapshot->fresh()->saldo_akhir);
        $this->assertSame('249999.75', $cash->calculate(1, '202610')['saldo_awal']);
        $closedSnapshot = $snapshot->fresh()->getAttributes();
        $this->post('/periode/'.$period->id.'/close')->assertSessionHasErrors('error');
        $this->assertSame($closedSnapshot, $snapshot->fresh()->getAttributes());
    }

    public function test_open_balances_stay_null_when_pages_are_refreshed_even_with_a_final_opening(): void
    {
        $this->expense($this->bs(), 'VALIDATED');
        $this->seedFinanceApprovals();
        for ($refresh = 0; $refresh < 2; $refresh++) {
            foreach (['/dashboard/transaksi', '/cash-flow', '/cash-flow/202609'] as $url) {
                $this->get($url)->assertOk()->assertDontSeeText('Saldo Akhir');
            }
            $snapshot = BsCashFlowSnapshot::firstOrFail();
            $this->assertTrue($snapshot->opening_final);
            $this->assertNull($snapshot->saldo_akhir);
            $this->assertSame('600000.00', $snapshot->total_keluar);
            $this->assertSame('400000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        }
    }

    public function test_an_unregistered_past_period_does_not_get_a_closing_balance(): void
    {
        $this->bs(['periode' => '2026-08', 'tanggal' => '2026-08-10']);
        $this->get('/cash-flow/202608')->assertOk()->assertDontSee('Saldo Akhir');
        $snapshot = BsCashFlowSnapshot::where('periode', '2026-08')->firstOrFail();
        $this->assertSame('1000000.00', $snapshot->total_masuk);
        $this->assertNull($snapshot->saldo_akhir);
        $this->assertNull(app(BsCashFlowService::class)->calculate(1, '202608')['saldo_akhir']);
    }

    public function test_zero_closing_balance_is_shown_after_close_and_rolls_through_empty_months(): void
    {
        $this->expense($this->bs(), 'VALIDATED', '1000000.00');
        $this->seedFinanceApprovals();
        $this->travelTo(now()->setDate(2026, 11, 16));
        $cash = app(BsCashFlowService::class);
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        $this->assertSame('0.00', $cash->recalculateSnapshot(1, '202609')->saldo_akhir);
        $this->get('/cash-flow/202609')->assertOk()->assertSeeInOrder(['Saldo Akhir', 'Rp 0']);
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-10')->value('id'));
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $table) {
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-10', 'saldo_akhir' => 0]);
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-11', 'saldo_awal' => 0, 'saldo_akhir' => null]);
        }
    }

    public function test_failed_close_rolls_back_final_balances_and_period_status(): void
    {
        $this->bs();
        $snapshot = app(BsCashFlowService::class)->recalculateSnapshot(1, '202609');
        $period = Periode::where('periode', '2026-09')->firstOrFail();
        $this->travelTo(now()->setDate(2026, 10, 16));
        $legacy = \Mockery::mock(\App\Services\CashFlowService::class);
        $legacy->shouldReceive('ensureSnapshotForPeriod')->once()->andThrow(new \RuntimeException('Simulated closing failure'));
        $this->app->instance(\App\Services\CashFlowService::class, $legacy);

        try {
            app(BsPeriodClosingService::class)->close($period->id);
            $this->fail('Expected closing failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated closing failure', $e->getMessage());
        }
        $this->assertSame('OPEN', $period->fresh()->status);
        $this->assertNull($period->fresh()->closed_at);
        $this->assertNull($snapshot->fresh()->saldo_akhir);
        $this->assertDatabaseMissing('periode', ['periode' => '2026-10']);
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_closing_balance_migration_clears_only_unclosed_balances(): void
    {
        $migration = require database_path('migrations/2026_09_18_100000_defer_closing_balances_until_period_close.php');
        $migration->down();
        Periode::create(['periode' => '2026-08', 'status' => 'CLOSED']);
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $table) {
            foreach (['2026-08', '2026-09', '2026-10'] as $periode) {
                DB::table($table)->insert(['user_id' => 1, 'periode' => $periode, 'saldo_awal' => 500, 'total_masuk' => 1000, 'total_keluar' => 250, 'saldo_akhir' => 1250.75]);
            }
        }
        $migration->up();
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $table) {
            $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => '2026-08', 'saldo_akhir' => 1250.75]);
            foreach (['2026-09', '2026-10'] as $periode) {
                $this->assertDatabaseHas($table, ['user_id' => 1, 'periode' => $periode, 'saldo_awal' => 500, 'total_masuk' => 1000, 'total_keluar' => 250, 'saldo_akhir' => null]);
            }
            DB::table($table)->insert(['user_id' => 3, 'periode' => '2026-09']);
            $this->assertDatabaseHas($table, ['user_id' => 3, 'periode' => '2026-09', 'saldo_akhir' => null]);
        }
    }

    public function test_stock_cash_counts_approved_events_by_owner_and_date_in_bs_funds(): void
    {
        $bs = $this->bs();
        $this->expense($bs, 'VALIDATED');
        $this->expense($bs, 'DRAFT', '900000.00');
        $this->expense($bs, 'VALIDATED', '100000.00', travel: true, attributes: ['bs_id' => null]);
        DB::table('perongkosan_event_dalam_kota')->insert([
            ['user_id' => 1, 'tanggal' => '2026-09-30', 'periode' => '2026-10', 'nominal' => '250000.25'],
            ['user_id' => 3, 'tanggal' => '2026-09-10', 'periode' => '2026-09', 'nominal' => '999000.00'],
            ['user_id' => 1, 'tanggal' => '2026-10-01', 'periode' => '2026-09', 'nominal' => '111000.00'],
        ]);
        DB::table('perongkosan_event_luar_kota')->insert([
            'user_id' => 1, 'tanggal' => '2026-09-01', 'periode' => '2026-09', 'nominal' => '249999.75',
            'transaksi_id' => 'EV-LKR-260901-001', 'description' => 'Event luar kota uji',
        ]);
        $this->seedFinanceApprovals();
        $stock = app(StockCashFlowService::class)->recalculateSnapshot(1, '202609');
        $this->assertSame('1000000.00', $stock->total_masuk);
        $this->assertSame('600000.00', $stock->total_keluar_bs);
        $this->assertSame('250000.25', $stock->total_event_dalam);
        $this->assertSame('249999.75', $stock->total_event_luar);
        $this->assertSame('0.00', $stock->total_keluar_pribadi);
        $this->assertSame('1100000.00', $stock->total_keluar);
        $this->assertNull($stock->saldo_akhir);
        $this->assertSame('-100000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo((int) $bs->user_id, '202609'));
        $this->get('/cash-flow/202609')->assertOk()
            ->assertSee('Event Dalam Kota')->assertSee('Event Luar Kota')->assertSee('EV-LKR-260901-001')
            ->assertSee('Event luar kota uji')->assertSee('Rp 1.100.000')->assertDontSee('Rp 999.000')
            ->assertDontSee('Rp 111.000')->assertDontSee('Saldo Akhir');
        $details = app(StockCashFlowService::class)->details(1, '202609');
        $this->assertCount(3, $details['keluar']);
        $this->assertSame(110000000, array_sum(array_map(fn ($row) => Money::minor($row['nominal']), $details['keluar'])));

        // Imported approved expenses exceed the pool; a new submission cannot spend it again.
        $header = $this->expense($bs, amount: '400000.00');
        $this->post('/transaksi-operasional/'.$header->id.'/validate')->assertSessionHasErrors('bs_balance');
        $this->assertSame('DRAFT', $header->fresh()->status);
        $this->assertSame('-100000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo((int) $bs->user_id, '202609'));
        $this->assertSame('1100000.00', $stock->fresh()->total_keluar);
        $this->assertNull($stock->fresh()->saldo_akhir);
    }

    public function test_combined_closing_balance_rolls_over_once_and_reopen_recalculates_it(): void
    {
        $bs = $this->bs();
        $this->expense($bs, 'VALIDATED');
        $eventId = DB::table('perongkosan_event_dalam_kota')->insertGetId([
            'user_id' => 1, 'tanggal' => '2026-09-10', 'periode' => '2026-09', 'nominal' => 100000,
        ]);
        DB::table('perongkosan_event_luar_kota')->insert([
            'user_id' => 1, 'tanggal' => '2026-09-15', 'periode' => '2026-09', 'nominal' => 50000,
        ]);
        $this->seedFinanceApprovals();
        $stock = app(StockCashFlowService::class);
        $this->assertNull($stock->recalculateSnapshot(1, '202609')->saldo_akhir);
        $this->travelTo(now()->setDate(2026, 11, 16));
        $closing = app(BsPeriodClosingService::class);
        $september = Periode::where('periode', '2026-09')->firstOrFail();
        $closing->close($september->id);
        $snapshot = $stock->recalculateSnapshot(1, '202609');
        $this->assertSame('250000.00', $snapshot->saldo_akhir);
        $this->assertSame('250000.00', app(BsCashFlowService::class)->recalculateSnapshot(1, '202609')->saldo_akhir);
        $this->assertSame('250000.00', $stock->recalculateSnapshot(1, '202610')->saldo_awal);
        $this->assertNull($stock->recalculateSnapshot(1, '202610')->saldo_akhir);
        $this->get('/cash-flow/202609')->assertOk()->assertSeeInOrder(['Saldo Akhir', 'Rp 250.000']);
        $frozen = $snapshot->getAttributes();
        $this->assertSame($frozen, $stock->recalculateSnapshot(1, '202609')->getAttributes());

        $october = Periode::where('periode', '2026-10')->firstOrFail();
        $closing->close($october->id);
        $this->assertSame('250000.00', $stock->recalculateSnapshot(1, '202610')->saldo_akhir);
        $this->assertSame('250000.00', $stock->recalculateSnapshot(1, '202611')->saldo_awal);
        $closing->reopen($october->id, 'Koreksi periode Oktober');
        $closing->reopen($september->id, 'Koreksi biaya event');
        $this->assertNull($snapshot->fresh()->saldo_akhir);
        $this->assertSame('0.00', $stock->recalculateSnapshot(1, '202610')->saldo_awal);
        $this->assertFalse($stock->recalculateSnapshot(1, '202610')->opening_final);
        DB::table('perongkosan_event_dalam_kota')->where('id', $eventId)->update(['nominal' => 200000]);
        $this->seedFinanceApprovals();
        $closing->close($september->id);
        $this->assertSame('150000.00', $snapshot->fresh()->saldo_akhir);
        $this->assertSame('150000.00', $stock->recalculateSnapshot(1, '202610')->saldo_awal);
        $this->assertSame('150000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202610'));
    }

    public function test_imported_approved_event_only_deficit_is_shared_by_stock_and_bs(): void
    {
        DB::table('perongkosan_event_luar_kota')->insert([
            'user_id' => 3, 'tanggal' => '2026-09-10', 'periode' => '2026-09', 'nominal' => '200000.25',
        ]);
        $this->seedFinanceApprovals();
        $this->travelTo(now()->setDate(2026, 10, 16));
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        $snapshot = StockCashFlowSnapshot::where('user_id', 3)->where('periode', '2026-09')->firstOrFail();
        $this->assertSame('-200000.25', $snapshot->saldo_akhir);
        $this->assertSame('0.00', $snapshot->total_masuk);
        $this->assertSame('-200000.25', app(StockCashFlowService::class)->recalculateSnapshot(3, '202610')->saldo_awal);
        $this->assertSame('-200000.25', app(BonSementaraService::class)->getRealtimeSisaSaldo(3, '202610'));
        $this->assertDatabaseCount('pengajuan_bs', 0);
    }

    public function test_event_create_move_and_delete_refresh_both_periods_without_changing_bs(): void
    {
        $bs = $this->bs();
        Periode::create(['periode' => '2026-10', 'status' => 'OPEN']);
        Storage::fake('public');
        $plafon = \Mockery::mock(\App\Services\PlafonService::class);
        $plafon->shouldReceive('checkEvent')->andReturn(['is_over' => false, 'plafon_value' => 500000, 'selisih' => 0]);
        $this->app->instance(\App\Services\PlafonService::class, $plafon);
        $payload = ['tanggal' => '2026-09-12', 'is_libur' => '0', 'jam_kerja_event_id' => 1,
            'description' => 'Event uji', 'nominal' => 100000, 'lokasi_event_id' => 1, 'teritory' => 'Jawa'];
        foreach (['dalam', 'luar'] as $type) {
            $table = 'perongkosan_event_'.$type.'_kota';
            $photo = \Illuminate\Http\UploadedFile::fake()->createWithContent('event.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
            $this->post('/event-'.$type.'-kota', $payload + ['foto_event_1' => $photo])->assertSessionHasNoErrors()->assertRedirect();
            $event = DB::table($table)->first();
            $this->assertSame('2026-09', $event->periode);
            $this->assertSame('0.00', StockCashFlowSnapshot::where('user_id', 1)->where('periode', '2026-09')->firstOrFail()->total_keluar);
            $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo((int) $bs->user_id, '202609'));

            $this->put('/event-'.$type.'-kota/'.$event->id, array_replace($payload, ['tanggal' => '2026-10-01', 'nominal' => 150000, 'user_id' => 3]))
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->assertDatabaseHas($table, ['id' => $event->id, 'periode' => '2026-10', 'user_id' => 1]);
            $this->assertSame('0.00', StockCashFlowSnapshot::where('user_id', 1)->where('periode', '2026-09')->firstOrFail()->total_keluar);
            $october = StockCashFlowSnapshot::where('user_id', 1)->where('periode', '2026-10')->firstOrFail();
            $this->assertSame('0.00', $october->total_keluar);
            $this->assertNull($october->saldo_akhir);
            $this->assertFalse($october->opening_final);
            $this->actingAs($this->transactionActionUser())->delete('/event-'.$type.'-kota/'.$event->id)->assertSessionHasNoErrors()->assertRedirect();
            $this->actingAs(User::findOrFail(1));
            $this->assertDatabaseCount($table, 0);
            $this->assertSame('0.00', $october->fresh()->total_keluar);
        }
    }

    public function test_closed_event_periods_reject_creation_edits_moves_and_deletion(): void
    {
        Periode::create(['periode' => '2026-10', 'status' => 'OPEN']);
        foreach (['dalam', 'luar'] as $type) {
            $table = 'perongkosan_event_'.$type.'_kota';
            DB::table($table)->insert(['user_id' => 1, 'tanggal' => '2026-09-10', 'periode' => '2026-10', 'nominal' => 100000]);
        }
        $this->seedFinanceApprovals();
        $this->travelTo(now()->setDate(2026, 10, 16));
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        $stock = StockCashFlowSnapshot::where('user_id', 1)->where('periode', '2026-09')->firstOrFail();
        $before = $stock->getAttributes();
        foreach (['dalam', 'luar'] as $type) {
            $table = 'perongkosan_event_'.$type.'_kota';
            $event = DB::table($table)->first();
            $this->post('/event-'.$type.'-kota', ['tanggal' => '2026-09-12'])->assertSessionHasErrors('tanggal');
            $this->put('/event-'.$type.'-kota/'.$event->id, ['tanggal' => '2026-09-12'])->assertSessionHasErrors('tanggal');
            $this->put('/event-'.$type.'-kota/'.$event->id, ['tanggal' => '2026-10-12'])->assertSessionHasErrors('tanggal');
            $this->delete('/event-'.$type.'-kota/'.$event->id)->assertSessionHasErrors('tanggal');
            $this->assertSame((array) $event, (array) DB::table($table)->first());
            $this->assertSame($before, $stock->fresh()->getAttributes());
        }
    }

    public function test_event_write_rolls_back_if_combined_snapshot_fails(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('events/rollback.png', 'test photo');
        $id = DB::table('perongkosan_event_dalam_kota')->insertGetId([
            'user_id' => 1, 'tanggal' => '2026-09-10', 'periode' => '2026-09', 'nominal' => 100000,
            'foto_event_1' => 'events/rollback.png',
        ]);
        $mock = \Mockery::mock(StockCashFlowService::class);
        $mock->shouldReceive('recalculateSnapshot')->once()->andThrow(new \RuntimeException('Simulated stock write failure'));
        $this->app->instance(StockCashFlowService::class, $mock);
        $this->actingAs($this->transactionActionUser())->delete('/event-dalam-kota/'.$id)->assertStatus(500);
        $this->assertDatabaseHas('perongkosan_event_dalam_kota', ['id' => $id, 'nominal' => 100000]);
        Storage::disk('public')->assertExists('events/rollback.png');
        $this->assertDatabaseCount('stock_cash_flow_snapshots', 0);
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_new_migration_preserves_legacy_travel_as_final(): void
    {
        $pooled = require database_path('migrations/2026_09_24_140000_add_pooled_bs_funding.php');
        $approval = require database_path('migrations/2026_09_24_160000_add_bs_owner_approval.php');
        $approval->down();
        $pooled->down();
        $migration = require database_path('migrations/2026_09_16_100000_add_bs_cash_flow_and_travel_validation.php');
        $migration->down();
        DB::table('transaksi_perjalanan_dinas_header')->insert(['transaksi_id' => 'LEGACY', 'tanggal' => '2026-09-01', 'user_id' => 1, 'periode' => '2026-09']);
        $migration->up();
        $pooled->up();
        $approval->up();
        $this->assertSame(0, TransaksiPerjalananDinasHeader::firstOrFail()->approval_version);
        $this->assertNull(TransaksiPerjalananDinasHeader::firstOrFail()->submitted_at);
        $this->assertSame('VALIDATED', TransaksiPerjalananDinasHeader::firstOrFail()->status);
        $this->assertNull(TransaksiPerjalananDinasHeader::firstOrFail()->bs_owner_id);
        $this->assertSame('DRAFT', (new TransaksiPerjalananDinasHeader())->status);
    }

    public function test_number_repair_preserves_balances_links_and_legacy_numbers_and_is_idempotent(): void
    {
        Storage::fake('local');
        $legacy = $this->bs(['document_no' => 'BS-20260901-003'], false);
        $bs = $this->bs(['document_no' => 'BS-01M2PG9NE6ZWTJYZTSW18HN5NW']);
        DB::table('approval_bs_finance')->where('pengajuan_bs_id', $bs->id)->update(['document_no' => 'CAIR-01M2PGMWVZETQQJR2993T58VD6']);
        $header = $this->expense($bs, 'VALIDATED', attributes: ['transaksi_id' => 'OASXXT5VHYO']);
        $header->details()->create(['transaksi_id' => $header->transaksi_id, 'cost_category_id' => 1, 'remark' => 'Biaya dihapus', 'nominal' => 10]);
        DB::table('transaksi_operasional_area_sales_detail')->where('remark', 'Biaya dihapus')->update(['deleted_at' => now()]);
        $balance = app(BsCashFlowService::class)->calculate(1, '202609');

        $this->artisan('bs:repair-document-numbers')->assertSuccessful();
        $this->assertSame('BS-01M2PG9NE6ZWTJYZTSW18HN5NW', $bs->fresh()->document_no);
        $this->assertSame('OASXXT5VHYO', $header->fresh()->transaksi_id);
        $this->assertSame(0, DB::table('document_number_sequences')->count());
        $this->assertSame(0, DB::table('bs_activity_logs')->count());
        $this->assertDatabaseCount('bs_usage_logs', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles());

        $this->artisan('bs:repair-document-numbers', ['--apply' => true])->assertSuccessful();
        $this->assertSame('BS-20260901-004', $bs->fresh()->document_no);
        $this->assertSame('BS-20260901-003', $legacy->fresh()->document_no);
        $this->assertSame('CAIR-20260901-001', $bs->financeApprovals()->firstOrFail()->document_no);
        $this->assertSame('OAS26090101', $header->fresh()->transaksi_id);
        $this->assertSame(2, DB::table('transaksi_operasional_area_sales_detail')->where('transaksi_id', 'OAS26090101')->count());
        $this->assertSame(0, DB::table('transaksi_operasional_area_sales_detail')->where('transaksi_id', 'OASXXT5VHYO')->count());
        $this->assertSame($bs->id, $header->fresh()->bs_id);
        $this->assertSame('VALIDATED', $header->fresh()->status);
        $this->assertSame($balance, app(BsCashFlowService::class)->calculate(1, '202609'));
        $this->assertCount(1, Storage::disk('local')->allFiles());
        $this->assertSame(3, DB::table('bs_activity_logs')->where('activity_code', 'NUMBER_CORRECTED')->count());
        $this->get('/pengajuan-bs')->assertOk()->assertSee('BS-20260901-004')->assertDontSee('BS-01M2PG9NE6ZWTJYZTSW18HN5NW');
        $this->get('/transaksi-operasional/'.$header->id)->assertOk()->assertSee('OAS26090101');
        $this->actingAs(User::findOrFail(2));
        $this->get(route('approval-finance.detail', $bs))->assertOk()->assertSee('BS-20260901-004');

        $this->artisan('bs:repair-document-numbers', ['--apply' => true])->assertSuccessful();
        $this->assertCount(1, Storage::disk('local')->allFiles());
        $this->assertSame(3, DB::table('bs_activity_logs')->count());
    }

    public function test_transaction_details_only_show_expense_edit_controls_in_edit_mode(): void
    {
        $bs = $this->bs();
        foreach ([false, true] as $travel) {
            $document = $this->expense($bs, travel: $travel, attributes: $travel ? ['tujuan_dinas_id' => 1] : []);
            $prefix = $travel ? 'transaksi_perjalanan_dinas' : 'transaksi_operasional';
            $key = $travel ? $document->transaksi_id : $document->id;
            $url = route($prefix.'.show', $key);
            $this->get($url)->assertOk()->assertSee('Biaya uji')->assertSee('Edit Transaksi')
                ->assertDontSee('data-transaction-editor=', false)
                ->assertDontSee('data-add-expense', false)
                ->assertDontSee('data-remove-expense', false)
                ->assertDontSee('action="'.route($prefix.'.store_detail', $key).'"', false);
            $this->get($url.'?edit=1')->assertOk()
                ->assertSee('action="'.route($prefix.'.update', $key).'"', false)
                ->assertSee('data-add-expense', false)->assertSee('data-remove-expense', false)
                ->assertSee('name="tanggal"', false)->assertSee('name="description"', false)
                ->assertDontSee('action="'.route($prefix.'.store_detail', $key).'"', false);
            $document->update(['status' => 'VALIDATED']);
            $this->get($url.'?edit=1')->assertOk()->assertDontSee('data-transaction-editor=', false);
        }
    }

    public function test_transaction_edit_updates_header_and_existing_new_deleted_costs_together(): void
    {
        $bs = $this->bs();
        DB::table('cost_category')->insert(['id' => 2, 'nama' => 'Parkir']);
        DB::table('wilayah')->insert(['id' => 2, 'nama' => 'Tujuan Baru', 'teritory' => 'Luar Jawa']);
        foreach ([false, true] as $travel) {
            $document = $this->expense($bs, travel: $travel);
            $existing = $document->details()->firstOrFail();
            $deleted = $document->details()->create(['transaksi_id' => $document->transaksi_id, 'cost_category_id' => 1, 'remark' => 'Dihapus saat edit', 'nominal' => 100]);
            $prefix = $travel ? 'transaksi_perjalanan_dinas' : 'transaksi_operasional';
            $key = $travel ? $document->transaksi_id : $document->id;
            $payload = [
                'tanggal' => '2026-09-20', 'description' => 'Informasi transaksi diperbarui',
                'details' => [$existing->id => ['cost_category_id' => 2, 'remark' => 'Rincian diperbarui', 'nominal' => '125000.25']],
                'delete_details' => [$deleted->id],
                'new_details' => [2 => ['cost_category_id' => 1, 'remark' => 'Rincian baru A', 'nominal' => '25000.50'],
                    5 => ['cost_category_id' => 2, 'remark' => 'Rincian baru B', 'nominal' => '10000.75']],
            ];
            if ($travel) $payload += ['teritory' => 'Luar Jawa', 'tujuan_dinas_id' => 2, 'jarak_km' => '315.25', 'durasi_jam' => '9.5', 'bs_owner_id' => 1];
            $this->from(route($prefix.'.show', $key).'?edit=1')->put(route($prefix.'.update', $key), $payload)
                ->assertSessionHasNoErrors()->assertRedirect(route($prefix.'.show', $key));
            $document->refresh();
            $this->assertSame('2026-09-20', \Carbon\Carbon::parse($document->tanggal)->toDateString());
            $this->assertSame('Informasi transaksi diperbarui', $document->description);
            $this->assertSame('DRAFT', $document->status);
            $this->assertSame(3, $document->details()->count());
            $this->assertEquals(160001.50, $document->details()->sum('nominal'));
            $this->assertSame(2, (int) $existing->fresh()->cost_category_id);
            $this->assertFalse($document->details()->whereKey($deleted->id)->exists());
            if ($travel) {
                $this->assertSame(2, (int) $document->tujuan_dinas_id);
                $this->assertEquals(315.25, $document->jarak_km);
                $this->assertEquals(9.5, $document->durasi_jam);
            }
            $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
            $this->assertDatabaseCount('bs_usage_logs', 0);
        }
    }

    public function test_transaction_edit_validation_keeps_old_header_and_new_rows_without_saving(): void
    {
        $bs = $this->bs();
        foreach ([false, true] as $travel) {
            $document = $this->expense($bs, travel: $travel, attributes: $travel ? ['tujuan_dinas_id' => 1] : []);
            $prefix = $travel ? 'transaksi_perjalanan_dinas' : 'transaksi_operasional';
            $key = $travel ? $document->transaksi_id : $document->id;
            $url = route($prefix.'.show', $key);
            $payload = ['_transaction_edit' => $document->transaksi_id, 'tanggal' => '2026-09-22', 'description' => 'Masukan tetap tersedia',
                'new_details' => [4 => ['cost_category_id' => 1, 'remark' => 'Biaya belum tersimpan', 'nominal' => '-10']]];
            if ($travel) $payload += ['teritory' => 'Jawa', 'tujuan_dinas_id' => 1, 'jarak_km' => 350, 'durasi_jam' => 10];
            $this->from($url.'?edit=1')->put(route($prefix.'.update', $key), $payload)
                ->assertSessionHasErrors('new_details.4.nominal');
            $this->get($url)->assertOk()->assertSee('data-transaction-editor=', false)
                ->assertSee('Masukan tetap tersedia')->assertSee('Biaya belum tersimpan')
                ->assertSee('name="new_details[4][nominal]"', false)->assertSee('value="-10"', false);
            $this->assertSame('2026-09-01', \Carbon\Carbon::parse($document->fresh()->tanggal)->toDateString());
            $this->assertSame(1, $document->details()->count());
        }
    }

    public function test_transaction_edit_rejects_closed_target_period_and_final_documents(): void
    {
        Periode::create(['periode' => '2026-10', 'status' => 'CLOSED']);
        $bs = $this->bs();
        foreach ([false, true] as $travel) {
            $document = $this->expense($bs, travel: $travel);
            $prefix = $travel ? 'transaksi_perjalanan_dinas' : 'transaksi_operasional';
            $key = $travel ? $document->transaksi_id : $document->id;
            $payload = ['tanggal' => '2026-10-01', 'description' => 'Tidak boleh disimpan'];
            $this->putJson(route($prefix.'.update', $key), $payload)->assertUnprocessable();
            $this->assertSame('2026-09-01', \Carbon\Carbon::parse($document->fresh()->tanggal)->toDateString());
            $document->update(['status' => 'VALIDATED']);
            $this->putJson(route($prefix.'.update', $key), ['tanggal' => '2026-09-22'])->assertUnprocessable();
            $this->assertSame('2026-09-01', \Carbon\Carbon::parse($document->fresh()->tanggal)->toDateString());
        }
    }

    public function test_create_pages_and_operational_detail_render(): void
    {
        $header = $this->expense($this->bs());
        $this->get('/transaksi-operasional/create')->assertOk();
        $this->get('/transaksi-perjalanan-dinas/create')->assertOk()->assertSee('Simpan Draft');
        $this->get('/transaksi-operasional/'.$header->id)->assertOk();
        $header->update(['status' => 'VALIDATED']);
        $this->get('/transaksi-operasional/'.$header->id.'?edit=1')->assertOk()->assertDontSee('action="'.route('transaksi_operasional.update', $header->id).'"', false);
    }

    public function test_action_times_use_server_timestamps_and_render_in_wib(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);
        $this->travelTo(\Carbon\Carbon::parse('2026-09-17 01:39:30', 'UTC'));
        $this->post('/pengajuan-bs', [
            'tipe_pengajuan' => 'operasional_sales', 'nominal_pengajuan' => '1000000',
            'created_at' => '2000-01-01 00:00:00', 'tanggal' => '2026-09-17',
        ])->assertSessionHasNoErrors();
        $bs = PengajuanBs::firstOrFail();
        $this->assertSame('2026-09-17 01:39:30', $bs->getRawOriginal('created_at'));
        $this->get('/pengajuan-bs')->assertOk()->assertSee('17 Sep 2026, 08:39:30 WIB');
        $this->get(route('pengajuan-bs.show', $bs))->assertOk()->assertSee('17 Sep 2026, 08:39:30 WIB');

        $this->actingAs(User::findOrFail(2));
        $this->travelTo(\Carbon\Carbon::parse('2026-09-17 01:43:12', 'UTC'));
        $this->post('/approval-atasan/'.$bs->id.'/approve')->assertSessionHasNoErrors();
        $this->get(route('approval-atasan.detail', $bs))->assertOk()
            ->assertSee('17 Sep 2026, 08:39:30 WIB')->assertSee('17 Sep 2026, 08:43:12 WIB');

        $this->travelTo(\Carbon\Carbon::parse('2026-09-17 01:45:38', 'UTC'));
        $this->post('/approval-finance/'.$bs->id.'/disburse', ['nominal_approved' => '1000000'])->assertSessionHasNoErrors();
        $this->assertSame('2026-09-17 01:45:38', $bs->financeApprovals()->firstOrFail()->getRawOriginal('created_at'));
        foreach ([route('approval-finance.detail', $bs), route('approval-finance.index')] as $url) {
            $this->get($url)->assertOk()->assertSee('17 Sep 2026, 08:39:30 WIB')->assertSee('17 Sep 2026, 08:45:38 WIB');
        }
        $this->get(route('approval-atasan.index'))->assertOk()
            ->assertSee('17 Sep 2026, 08:43:12 WIB')->assertDontSee('17 Sep 2026, 08:45:38 WIB');

        $this->actingAs(User::findOrFail(1));
        $header = $this->expense($bs->fresh());
        $this->travelTo(\Carbon\Carbon::parse('2026-09-17 02:22:28', 'UTC'));
        $this->post('/transaksi-operasional/'.$header->id.'/validate', ['validated_at' => '2000-01-01 00:00:00'])->assertSessionHasNoErrors();
        $this->assertSame('2026-09-17 02:22:28', $header->fresh()->getRawOriginal('validated_at'));
        $this->get('/transaksi-operasional/'.$header->id)->assertOk()->assertSee('17 Sep 2026, 09:22:28 WIB');
        $this->assertDatabaseHas('bs_usage_logs', ['document_no' => $header->transaksi_id, 'created_at' => '2026-09-17 02:22:28']);
    }

    public function test_local_midnight_controls_document_dates_numbers_and_posting_periods(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);
        $this->travelTo(\Carbon\Carbon::parse('2026-09-30 17:05:06', 'UTC'));
        $this->post('/pengajuan-bs', ['tipe_pengajuan' => 'operasional_sales', 'nominal_pengajuan' => '1000000'])->assertSessionHasNoErrors();
        $bs = PengajuanBs::firstOrFail();
        $this->assertSame('BS-20261001-001', $bs->document_no);
        $this->assertSame('2026-10-01', $bs->tanggal->toDateString());
        $this->assertSame('2026-10', $bs->periode);
        $this->assertSame('2026-09-30 17:05:06', $bs->getRawOriginal('created_at'));

        $this->actingAs(User::findOrFail(2));
        $this->post('/approval-atasan/'.$bs->id.'/approve')->assertSessionHasNoErrors();
        $this->post('/approval-finance/'.$bs->id.'/disburse', ['nominal_approved' => '1000000'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('approval_bs_finance', [
            'document_no' => 'CAIR-20261001-001', 'tanggal_cair' => '2026-10-01', 'periode' => '2026-10',
            'created_at' => '2026-09-30 17:05:06',
        ]);
        $this->get(route('approval-finance.detail', $bs))->assertOk()->assertSee('01 Oct 2026, 00:05:06 WIB');
    }

    public function test_rejection_time_stays_at_the_decision_even_after_document_updates(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);
        $bs = $this->bs(['status' => 'APPROVED_ATASAN'], false);
        $this->actingAs(User::findOrFail(2));
        $this->travelTo(\Carbon\Carbon::parse('2026-09-17 01:43:12', 'UTC'));
        $this->post('/approval-finance/'.$bs->id.'/reject', ['rejection_reason' => 'Dokumen belum lengkap'])->assertSessionHasNoErrors();
        $this->travelTo(\Carbon\Carbon::parse('2026-09-18 05:00:00', 'UTC'));
        $bs->fresh()->update(['description' => 'Catatan tambahan']);
        foreach ([route('approval-finance.detail', $bs), route('approval-finance.index')] as $url) {
            $this->get($url)->assertOk()->assertSee('17 Sep 2026, 08:43:12 WIB')->assertDontSee('18 Sep 2026, 12:00:00 WIB');
        }
    }

    public function test_travel_validation_displays_action_time_and_preserves_business_date(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);
        $header = $this->expense($this->bs(['tipe_pengajuan' => 'perjalanan_dinas']), travel: true);
        $this->travelTo(\Carbon\Carbon::parse('2026-09-17 02:22:28', 'UTC'));
        $this->post('/transaksi-perjalanan-dinas/'.$header->transaksi_id.'/validate')->assertSessionHasNoErrors();
        $this->assertSame('2026-09-01', $header->fresh()->tanggal);
        $this->assertSame('2026-09-17 02:22:28', $header->fresh()->getRawOriginal('validated_at'));
        $this->get('/transaksi-perjalanan-dinas/'.$header->transaksi_id)->assertOk()->assertSee('17 Sep 2026, 09:22:28 WIB');
    }

    public function test_mixed_receipts_fund_both_modules_above_each_document_amount_and_report_one_balance(): void
    {
        $this->bs(['nominal_approved' => '1000000.00']);
        $this->bs(['nominal_approved' => '2000000.00', 'tipe_pengajuan' => 'perjalanan_dinas']);
        $oas = $this->transactionPayload();
        $oas['details'][0]['nominal'] = '1500000';
        $this->post('/transaksi-operasional', $oas + ['save_action' => 'confirm'])->assertSessionHasNoErrors();
        $pd = $this->transactionPayload(true);
        $pd['details'][0]['nominal'] = '500000';
        $this->post('/transaksi-perjalanan-dinas', $pd + ['save_action' => 'confirm'])->assertSessionHasNoErrors();
        $operational = TransaksiOperasionalHeader::firstOrFail();
        $travel = TransaksiPerjalananDinasHeader::firstOrFail();
        $this->assertNull($operational->bs_id);
        $this->assertNull($travel->bs_id);
        $this->confirmUsageFinance($operational);
        $this->confirmUsageFinance($travel);
        $this->assertSame('1000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '2026-09'));
        $this->assertDatabaseCount('bs_usage_logs', 2);
        $this->assertCount(2, app(BsCashFlowService::class)->details(1, '2026-09')['keluar']);
        $this->getJson(route('bs-balance.balances', ['module' => 'operasional_sales', 'periode' => '202609']))
            ->assertOk()->assertJsonCount(1, 'owners')->assertJsonPath('owners.0.balance', '1000000.00');
        $this->get('/cash-flow/202609')->assertOk()->assertSee($operational->transaksi_id)->assertSee($travel->transaksi_id);
        $book = $this->cashFlowWorkbook('/cash-flow/202609/export', 'stok-kas-202609.xlsx');
        $export = json_encode($book->getSheetByName('Uang Keluar')->toArray());
        $this->assertStringContainsString($operational->transaksi_id, $export);
        $this->assertStringContainsString($travel->transaksi_id, $export);
    }

    public function test_group_members_can_use_owner_pool_only_for_travel_and_access_is_rechecked(): void
    {
        $group = $this->bs(['nominal_approved' => '100000.00', 'tipe_pengajuan' => 'perjalanan_dinas']);
        $group->members()->attach(3);
        $this->bs(['nominal_approved' => '900000.00']);
        $this->bs(['nominal_approved' => '5000000.00', 'user_id' => 3]);
        $this->actingAs(User::findOrFail(3));
        $this->postJson('/transaksi-operasional', $this->transactionPayload() + ['bs_owner_id' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors('bs_owner_id');
        $data = $this->transactionPayload(true) + ['bs_owner_id' => 1];
        $data['details'][0]['nominal'] = '600000';
        $this->post('/transaksi-perjalanan-dinas', $data + ['save_action' => 'confirm'])->assertSessionHasNoErrors();
        $posted = TransaksiPerjalananDinasHeader::firstOrFail();
        $this->actingAs(User::findOrFail(1));
        $this->post(route('bs-usage-approvals.approve', $posted->id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $this->confirmUsageFinance($posted);
        $this->actingAs(User::findOrFail(3));
        $this->assertNull($posted->bs_id);
        $this->assertEquals(1, $posted->bs_owner_id);
        $this->assertSame('400000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
        $this->assertSame('5000000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(3, '202609'));
        $data['tanggal'] = '2026-09-17';
        $data['details'][0]['nominal'] = '450000';
        $this->post('/transaksi-perjalanan-dinas', $data + ['save_action' => 'confirm'])->assertSessionHasNoErrors();
        $draft = TransaksiPerjalananDinasHeader::where('status', 'PENDING_OWNER')->firstOrFail();
        $this->actingAs(User::findOrFail(1));
        $this->post(route('bs-usage-approvals.approve', $draft->id), ['approval_version' => 1])->assertSessionHasErrors('bs_balance');
        $this->actingAs(User::findOrFail(3));
        $this->post(route('transaksi_perjalanan_dinas.withdraw', $draft->transaksi_id), ['approval_version' => 1])->assertSessionHasNoErrors();
        $group->members()->detach(3);
        $this->post('/transaksi-perjalanan-dinas/'.$draft->transaksi_id.'/validate')->assertSessionHasErrors('bs_owner_id');
        $this->getJson(route('bs-balance.balances', ['module' => 'perjalanan_dinas', 'periode' => '202609']))
            ->assertOk()->assertJsonCount(1, 'owners')->assertJsonPath('owners.0.id', 3);
        $this->actingAs(User::findOrFail(1));
        $this->get('/transaksi-perjalanan-dinas/'.$posted->transaksi_id)->assertOk();
        $this->get('/transaksi-perjalanan-dinas')->assertOk()->assertDontSee($posted->transaksi_id);
        $this->assertStringContainsString($posted->transaksi_id,
            json_encode(app(BsCashFlowService::class)->details(1, '2026-09')['keluar']));
        $this->putJson('/transaksi-perjalanan-dinas/'.$draft->transaksi_id, $data)->assertForbidden();
        $this->assertDatabaseHas('bs_usage_logs', ['bs_owner_id' => 1, 'inputter_id' => 3, 'actor_id' => 1, 'nominal' => '600000.00']);
    }

    public function test_removed_bs_balance_page_is_unavailable_and_has_no_navigation_links(): void
    {
        $this->bs();
        $this->get('/saldo-bs?periode=2026-09')->assertNotFound();
        foreach ([route('dashboard.transaksi'), route('cash-flow.index'), '/cash-flow/202609'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Rincian Saldo BS')->assertDontSee('/saldo-bs');
        }
        DB::table('roles')->insert(['id' => 1, 'code' => 'FINANCE']);
        User::findOrFail(2)->update(['roles_id' => 1]);
        $this->actingAs(User::findOrFail(2));
        $this->get(route('laporan-bs.index'))->assertOk()->assertDontSee('Rincian Saldo BS')->assertDontSee('/saldo-bs');
    }

    public function test_balance_endpoints_do_not_expose_unrelated_owners_or_other_users_documents(): void
    {
        $this->bs();
        $other = $this->bs(['user_id' => 3, 'nominal_approved' => '9000000.00']);
        $otherDocument = $this->expense($other, travel: true);
        $this->getJson(route('bs-balance.balances', ['module' => 'perjalanan_dinas', 'periode' => '202609', 'bs_owner_id' => 3]))
            ->assertOk()->assertJsonCount(1, 'owners')->assertJsonPath('owners.0.id', 1);
        $this->getJson(route('bs-balance.balances', ['module' => 'perjalanan_dinas', 'periode' => '202609', 'transaction_id' => $otherDocument->id]))->assertForbidden();
        $this->getJson(route('bs-balance.balances', ['module' => 'invalid', 'periode' => '202609']))->assertUnprocessable();
        $this->getJson(route('bs-balance.balances', ['module' => 'operasional_sales', 'periode' => '202613']))->assertUnprocessable();
        User::findOrFail(1)->revokePermissionTo(['akses.cash-flow', 'akses.transaksi-perjalanan-dinas']);
        $this->actingAs(User::findOrFail(1));
        $this->getJson(route('bs-balance.balances', ['module' => 'perjalanan_dinas', 'periode' => '202609']))->assertForbidden();
    }

    public function test_two_modules_cannot_spend_the_same_pooled_balance(): void
    {
        $this->bs();
        foreach ([false, true] as $travel) {
            $data = $this->transactionPayload($travel);
            $data['details'][0]['nominal'] = '600000';
            $this->post($travel ? '/transaksi-perjalanan-dinas' : '/transaksi-operasional', $data)->assertSessionHasNoErrors();
        }
        $one = TransaksiOperasionalHeader::firstOrFail();
        $two = TransaksiPerjalananDinasHeader::firstOrFail();
        $this->post('/transaksi-operasional/'.$one->id.'/validate')->assertSessionHasNoErrors();
        $this->confirmUsageFinance($one);
        $this->post('/transaksi-perjalanan-dinas/'.$two->transaksi_id.'/validate')->assertSessionHasErrors('bs_balance');
        $this->assertSame('DRAFT', $two->fresh()->status);
        $this->assertDatabaseCount('bs_usage_logs', 1);
        $this->assertSame('400000.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202609'));
    }

    public function test_future_receipts_cannot_fund_earlier_pooled_expenses(): void
    {
        $this->bs(['tanggal' => '2026-10-01', 'periode' => '2026-10']);
        $this->post('/transaksi-operasional', $this->transactionPayload() + ['save_action' => 'confirm'])->assertSessionHasErrors('bs_balance');
        $this->assertDatabaseCount('transaksi_operasional_area_sales_header', 0);
        $this->assertDatabaseCount('bs_usage_logs', 0);
    }

    public function test_pooled_spending_rolls_forward_once_after_close_and_matches_combined_stock(): void
    {
        $this->bs();
        $this->post('/transaksi-operasional', $this->transactionPayload() + ['save_action' => 'confirm'])->assertSessionHasNoErrors();
        $this->confirmUsageFinance(TransaksiOperasionalHeader::firstOrFail());
        $this->travelTo(now()->setDate(2026, 10, 2));
        $this->assertSame('0.00', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202610'));
        app(BsPeriodClosingService::class)->close(Periode::where('periode', '2026-09')->value('id'));
        $this->assertSame('849999.75', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202610'));
        $data = $this->transactionPayload(true);
        $data['tanggal'] = '2026-10-02';
        $this->post('/transaksi-perjalanan-dinas', $data + ['save_action' => 'confirm'])->assertSessionHasNoErrors();
        $this->confirmUsageFinance(TransaksiPerjalananDinasHeader::firstOrFail());
        $this->assertSame('699999.50', app(BonSementaraService::class)->getRealtimeSisaSaldo(1, '202610'));
        $snapshot = app(StockCashFlowService::class)->recalculateSnapshot(1, '202610');
        $this->assertSame('849999.75', $snapshot->saldo_awal);
        $this->assertSame('150000.25', $snapshot->total_keluar);
        $this->assertNull($snapshot->saldo_akhir);
    }

}
