<?php

namespace Tests\Feature;

use App\Models\BsUsageCheck;
use App\Models\BsUsageCheckDetail;
use App\Models\TransaksiOperasionalHeader;
use App\Models\TransaksiPerjalananDinasHeader;
use App\Models\User;
use App\Services\BsUsageCheckService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BsUsageCheckTest extends TestCase
{
    use \Tests\Concerns\BsUsageCheckDraftScenarios;
    use \Tests\Concerns\LegacyFinanceFixtures;
    use \Tests\Concerns\FinanceItemApprovalScenarios;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->travelTo(now()->setDate(2026, 9, 25)->setTime(8, 0));
        Schema::create('user', function (Blueprint $t) {
            $t->increments('id'); $t->string('nama'); $t->integer('roles_id')->nullable(); $t->integer('atasan_user_id')->nullable();
        });
        Schema::create('roles', function (Blueprint $t) { $t->increments('id'); $t->string('code'); });
        Schema::create('periode', function (Blueprint $t) {
            $t->increments('id'); $t->string('periode')->unique(); $t->string('status'); $t->timestamps();
        });
        (require database_path('migrations/2026_10_12_100000_create_period_closing_logs_table.php'))->up();
        Schema::create('pengajuan_bs', function (Blueprint $t) {
            $t->increments('id'); $t->integer('user_id'); $t->string('status'); $t->string('settled_periode')->nullable();
            $t->decimal('nominal_reimburse', 15, 2)->default(0); $t->decimal('nominal_pengembalian', 15, 2)->default(0);
        });
        Schema::create('approval_bs_finance', function (Blueprint $t) {
            $t->increments('id'); $t->integer('pengajuan_bs_id'); $t->date('tanggal_cair'); $t->decimal('nominal_approved', 15, 2);
        });
        Schema::create('bs_cash_flow_snapshots', function (Blueprint $t) {
            $t->increments('id'); $t->integer('user_id'); $t->string('periode');
            foreach (['saldo_awal', 'total_masuk', 'total_keluar', 'penyesuaian'] as $column) $t->decimal($column, 15, 2)->default(0);
            $t->decimal('saldo_akhir', 15, 2)->nullable(); $t->boolean('opening_final')->default(false);
            $t->timestamp('last_updated')->nullable(); $t->unique(['user_id', 'periode']);
        });
        (require database_path('migrations/2026_09_18_110000_create_stock_cash_flow_snapshots_table.php'))->up();
        (require database_path('migrations/2026_09_23_110000_add_personal_funds_to_stock_cash_flow.php'))->up();
        (require database_path('migrations/2026_10_07_120000_mark_finance_based_cash_snapshots.php'))->up();
        (require database_path('migrations/2026_10_09_025352_add_operasional_and_perjalanan_dinas_columns_to_stock_cash_flow_snapshots_table.php'))->up();
        DB::table('periode')->insert([['periode' => '2026-08', 'status' => 'OPEN'], ['periode' => '2026-09', 'status' => 'OPEN']]);
        foreach ([1, 3] as $owner) {
            DB::table('pengajuan_bs')->insert(['id' => $owner, 'user_id' => $owner, 'status' => 'DISBURSED']);
            foreach (['2026-08', '2026-09'] as $month) DB::table('approval_bs_finance')->insert([
                'pengajuan_bs_id' => $owner, 'tanggal_cair' => $month.'-01', 'nominal_approved' => 1000000,
            ]);
        }
        Schema::create('cost_category', function (Blueprint $t) { $t->increments('id'); $t->string('nama'); $t->boolean('is_active')->default(true); });
        foreach (array_intersect_key(BsUsageCheckService::TYPES, array_flip(['OPR', 'PD'])) as $type => $model) {
            Schema::create((new $model)->getTable(), function (Blueprint $t) {
                $t->increments('id'); $t->string('transaksi_id', 32)->unique(); $t->date('tanggal');
                $t->integer('user_id'); $t->integer('bs_owner_id')->nullable(); $t->integer('bs_id')->nullable();
                $t->string('periode'); $t->string('status')->default('DRAFT'); $t->string('description')->nullable();
                $t->integer('validated_by')->nullable(); $t->timestamp('validated_at')->nullable();
            });
            Schema::create((new $model)->details()->getRelated()->getTable(), function (Blueprint $t) use ($type) {
                $t->increments('id'); $t->string('transaksi_id', 32); $t->integer('cost_category_id');
                $t->decimal('nominal', 15, 2); $t->string('remark')->nullable();
                if ($type === 'PD') $t->softDeletes();
            });
        }
        foreach (['perongkosan_event_dalam_kota', 'perongkosan_event_luar_kota'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->increments('id'); $t->string('transaksi_id'); $t->date('tanggal'); $t->string('periode');
                $t->integer('user_id'); $t->decimal('nominal', 15, 2); $t->string('description')->nullable();
            });
        }
        Schema::create('document_number_sequences', function (Blueprint $t) {
            $t->string('type', 8); $t->date('date'); $t->unsignedBigInteger('last_number'); $t->primary(['type', 'date']);
        });
        (require database_path('migrations/2026_07_16_075422_create_permission_tables.php'))->up();
        (require database_path('migrations/2026_09_25_150000_create_bs_usage_checks.php'))->up();
        (require database_path('migrations/2026_09_28_100000_extend_finance_checks_to_transaction_modules.php'))->up();
        (require database_path('migrations/2026_09_30_160000_add_finance_check_drafts.php'))->up();
        (require database_path('migrations/2026_10_05_090000_track_finance_resubmissions.php'))->up();
        (require database_path('migrations/2026_10_07_160000_track_finance_approval_per_source_detail.php'))->up();
        (require database_path('migrations/2026_10_08_100000_add_photos_to_transaction_details.php'))->up();
        DB::table('user')->insert([
            ['id' => 1, 'nama' => 'Pemilik Transaksi'], ['id' => 2, 'nama' => 'Finance'], ['id' => 3, 'nama' => 'Pemilik Dana'],
        ]);
        DB::table('cost_category')->insert([['id' => 1, 'nama' => 'Transport'], ['id' => 2, 'nama' => 'Makan']]);
        Permission::create(['name' => 'akses.approval-finance', 'guard_name' => 'web']);
        User::findOrFail(2)->givePermissionTo('akses.approval-finance');
        $this->actingAs(User::findOrFail(2));
    }

    private function source(string $type = 'OPR', array $amounts = [1 => '100.00', 2 => '50.00']): Model
    {
        $model = BsUsageCheckService::TYPES[$type];
        $source = $model::create([
            'transaksi_id' => ($type === 'OPR' ? 'OAS' : 'PD').str_pad((string) ($model::count() + 1), 8, '0', STR_PAD_LEFT),
            'tanggal' => '2026-08-01', 'periode' => '2026-08', 'user_id' => 1, 'bs_owner_id' => 3, 'status' => 'VALIDATED',
        ]);
        foreach ($amounts as $category => $amount) {
            $source->details()->create(['cost_category_id' => $category, 'nominal' => $amount, 'remark' => 'Biaya sumber']);
        }

        return $source;
    }

    private function payload(Model $source, string $type = 'OPR', array $approved = [], string $date = '2026-09-25'): array
    {
        $service = app(BsUsageCheckService::class);
        $snapshot = $service->snapshot($source);

        return [
            'tanggal_pengecekan' => $date,
            'source_token' => $service->token($type, $source->id, $snapshot['fingerprint']),
            'details' => $snapshot['items']->map(fn ($item) => [
                'source_detail_id' => $item['source_detail_id'],
                'cost_category_id' => $item['cost_category_id'],
                'nominal_disetujui' => $approved[$item['cost_category_id']] ?? $item['nominal_asal'],
                'catatan' => 'Hasil pengecekan',
            ])->all(),
        ];
    }

    private function save(Model $source, string $type = 'OPR', array $approved = [], string $date = '2026-09-25'): BsUsageCheck
    {
        $this->post(route('bs-usage-checks.store', [$type, $source->id]), $this->payload($source, $type, $approved, $date))
            ->assertSessionHasNoErrors()->assertRedirect();

        return BsUsageCheck::latest('id')->firstOrFail();
    }

    public function test_form_defaults_and_persistent_snapshots_use_source_owner_and_session_checker(): void
    {
        foreach (['OPR', 'PD'] as $type) {
            $source = $this->source($type);
            $source->details()->create(['cost_category_id' => 1, 'nominal' => '25.25', 'remark' => 'Kategori berulang']);
            if ($type === 'PD') {
                $removed = $source->details()->create(['cost_category_id' => 1, 'nominal' => '999', 'remark' => 'Dihapus']);
                $removed->delete();
            }
            $this->get(route('bs-usage-checks.create', [$type, $source->id]))->assertOk()
                ->assertSee('Simpan Konfirmasi')->assertSee('Pemilik Transaksi')->assertSee('value="100.00"', false)->assertSee('value="25.25"', false);
            $data = $this->payload($source, $type);
            $data['details'][0]['nominal_disetujui'] = '94.90';
            $data += ['user_id' => 3, 'user_checker_id' => 1, 'periode' => '1999-01', 'no_referensi' => 'PALSU', 'jenis_transaksi' => 'EVENT', 'transaksi_pemakaian_id' => 999, 'document_no' => 'PALSU'];
            $this->post(route('bs-usage-checks.store', [$type, $source->id]), $data)->assertSessionHasNoErrors();
            $check = BsUsageCheck::latest('id')->firstOrFail();
            $this->assertSame($source->transaksi_id, $check->no_referensi);
            $this->assertSame($type, $check->jenis_transaksi);
            $this->assertEquals(1, $check->user_id);
            $this->assertEquals(2, $check->user_checker_id);
            $this->assertSame('2026-09', $check->periode);
            $this->assertCount(3, $check->details);
            $this->assertSame('100.00', $check->details->first()->nominal_asal);
            $this->assertSame('94.90', $check->details->first()->nominal_disetujui);
            $this->assertSame('5.10', $check->details->first()->selisih);
            $this->assertSame($source::class, $check->source::class);
            $this->assertSame('VALIDATED', $source->fresh()->status);
            $this->get(route('bs-usage-checks.show', $check))->assertOk()->assertSee('25,25')->assertSee('Kategori berulang')->assertSee('Finance');
        }
    }

    public function test_lookup_contains_only_sources_and_computes_check_status_with_colliding_ids(): void
    {
        $opr = $this->source();
        $pd = $this->source('PD');
        $this->assertSame($opr->id, $pd->id);
        $this->save($opr);
        $this->get(route('bs-usage-checks.index'))->assertOk()->assertSee($opr->transaksi_id)->assertSee($pd->transaksi_id);
        $this->get(route('bs-usage-checks.index', ['status_cek' => 'sudah']))->assertOk()->assertSee($opr->transaksi_id)->assertDontSee($pd->transaksi_id);
        $this->get(route('bs-usage-checks.index', ['status_cek' => 'belum', 'jenis' => 'PD']))->assertOk()->assertSee($pd->transaksi_id)->assertDontSee($opr->transaksi_id);
        $this->get(route('bs-usage-checks.index', ['referensi' => $opr->transaksi_id, 'user_id' => 1]))->assertOk()->assertSee($opr->transaksi_id)->assertDontSee($pd->transaksi_id);
        $this->assertTrue($opr->financeChecks()->exists());
        $this->assertFalse($pd->financeChecks()->exists());
        $this->get('/pengecekan-pemakaian-bs/EVENT/1/create')->assertNotFound();
        $this->postJson('/pengecekan-pemakaian-bs/EVENT/1', [])->assertNotFound();
    }

    public function test_recheck_creates_new_document_and_preserves_old_details_even_after_source_deletion(): void
    {
        $source = $this->source();
        $first = $this->save($source, approved: [1 => '90.00']);
        $source->details()->where('cost_category_id', 1)->update(['nominal' => '200']);
        $second = $this->save($source);
        $this->assertNotSame($first->document_no, $second->document_no);
        $this->assertSame('CEKBS-20260925-001', $first->document_no);
        $this->assertSame('CEKBS-20260925-002', $second->document_no);
        $this->assertSame('100.00', $first->details()->first()->nominal_asal);
        $this->assertSame('200.00', $second->details()->first()->nominal_asal);
        $this->assertDatabaseCount('bs_usage_checks', 2);
        $source->details()->delete();
        $source->delete();
        DB::table('cost_category')->where('id', 1)->update(['nama' => 'Nama Baru']);
        $this->get(route('bs-usage-checks.show', $first))->assertOk()->assertSee('sudah tidak tersedia')->assertSee('Transport')->assertSee($second->document_no)->assertSee('100,00');
        $this->get(route('bs-usage-checks.report', ['mode' => 'semua']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 4);
        $this->putJson(route('bs-usage-checks.show', $first), [])->assertMethodNotAllowed();
        $this->deleteJson(route('bs-usage-checks.show', $first))->assertMethodNotAllowed();
    }

    public function test_latest_global_is_selected_before_filters_and_history_totals_are_exact(): void
    {
        $opr = $this->source();
        $pd = $this->source('PD', [1 => '200.00']);
        $a = $this->save($opr, approved: [1 => '90'], date: '2026-09-01');
        $b = $this->save($opr, date: '2026-09-10');
        $c = $this->save($pd, 'PD', [1 => '180'], '2026-09-05');
        $report = $this->get(route('bs-usage-checks.report'))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 3);
        $this->assertEquals(350, $report->viewData('totals')->nominal_asal);
        $this->assertEquals(330, $report->viewData('totals')->nominal_disetujui);
        $this->assertEquals(20, $report->viewData('totals')->selisih);
        $this->assertEqualsCanonicalizing([$b->id, $c->id], $report->viewData('rows')->pluck('bs_usage_check_id')->unique()->all());
        $all = $this->get(route('bs-usage-checks.report', ['mode' => 'semua']))->assertOk();
        $this->assertEquals(500, $all->viewData('totals')->nominal_asal);
        $this->assertEquals(470, $all->viewData('totals')->nominal_disetujui);
        $this->assertEquals(30, $all->viewData('totals')->selisih);
        $date = ['tanggal_awal' => '2026-09-01', 'tanggal_akhir' => '2026-09-05'];
        $this->get(route('bs-usage-checks.report', $date))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->bs_usage_check_id === $c->id);
        $this->get(route('bs-usage-checks.report', $date + ['mode' => 'semua']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 3);
        $this->get(route('bs-usage-checks.report', ['status_selisih' => 'sesuai']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 2);
        $this->get(route('bs-usage-checks.report', ['status_selisih' => 'tidak_sesuai']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
        $this->get(route('bs-usage-checks.report', ['periode' => '202609', 'jenis' => 'PD', 'user_id' => 1, 'referensi' => $pd->transaksi_id]))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
        $d = $this->save($opr, date: '2026-08-01');
        $this->get(route('bs-usage-checks.report', ['source_id' => $opr->id, 'jenis_source' => 'OPR']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->first()->bs_usage_check_id === $d->id);
    }

    public function test_offsetting_differences_are_not_considered_matching_and_notes_are_escaped(): void
    {
        $source = $this->source();
        $data = $this->payload($source, approved: [1 => '90', 2 => '60']);
        $data['details'][0]['catatan'] = '<script>alert(1)</script>';
        $this->post(route('bs-usage-checks.store', ['OPR', $source->id]), $data)->assertSessionHasNoErrors();
        $this->get(route('bs-usage-checks.report', ['status_selisih' => 'tidak_sesuai']))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 2)->assertViewHas('totals', fn ($totals) => (float) $totals->selisih === 0.0)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('bs-usage-checks.report', ['status_selisih' => 'sesuai']))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
    }

    public function test_money_item_validation_and_readonly_detail_cannot_be_tampered_with(): void
    {
        $source = $this->source();
        $url = route('bs-usage-checks.store', ['OPR', $source->id]);
        foreach (['-1', '1.001', '1e2', '10000000000000', 'abc', ''] as $amount) {
            $this->postJson($url, $this->payload($source, approved: [1 => $amount]))->assertUnprocessable()->assertJsonValidationErrors('details.0.nominal_disetujui');
        }
        $data = $this->payload($source);
        $data['details'][0]['nominal_asal'] = '1';
        $this->postJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('details.0');
        foreach ([[['cost_category_id' => 1, 'nominal_disetujui' => 1]], [['cost_category_id' => 99, 'nominal_disetujui' => 1]], [], [['cost_category_id' => 1, 'nominal_disetujui' => 1], ['cost_category_id' => 1, 'nominal_disetujui' => 1]]] as $details) {
            $data = $this->payload($source);
            $data['details'] = $details;
            $this->postJson($url, $data)->assertUnprocessable();
        }
        $data = $this->payload($source);
        $data['tanggal_pengecekan'] = '2026-02-30';
        $this->postJson($url, $data)->assertUnprocessable();
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $this->assertDatabaseCount('bs_usage_check_details', 0);
        $this->assertDatabaseCount('document_number_sequences', 0);
        $this->save($source, approved: [1 => '0', 2 => '60.99']);
    }

    public function test_stale_token_and_changed_reference_are_rejected(): void
    {
        $source = $this->source();
        $pd = $this->source('PD');
        $data = $this->payload($source);
        $this->postJson(route('bs-usage-checks.store', ['PD', $pd->id]), $data)->assertUnprocessable()->assertJsonValidationErrors('source_token');
        $source->details()->first()->update(['nominal' => 101]);
        $this->postJson(route('bs-usage-checks.store', ['OPR', $source->id]), $data)->assertUnprocessable()->assertJsonValidationErrors('source_token');
        $data['source_token'] = 'tampered';
        $this->postJson(route('bs-usage-checks.store', ['OPR', $source->id]), $data)->assertUnprocessable();
        $empty = $this->source(amounts: []);
        $this->get(route('bs-usage-checks.create', ['OPR', $empty->id]))->assertOk()->assertSee('belum mempunyai rincian');
        $this->postJson(route('bs-usage-checks.store', ['OPR', $empty->id]), $this->payload($empty))->assertUnprocessable();
        $data = $this->payload($source);
        $source->delete();
        $this->postJson(route('bs-usage-checks.store', ['OPR', $source->id]), $data)->assertNotFound();
        $this->get(route('bs-usage-checks.create', ['OPR', $source->id]))->assertNotFound();
        $this->assertDatabaseCount('bs_usage_checks', 0);
    }

    public function test_rollback_removes_header_details_and_number_when_detail_insert_fails(): void
    {
        $source = $this->source();
        BsUsageCheckDetail::creating(function ($detail) {
            if ($detail->cost_category_id === 2) throw new \RuntimeException('Simulated detail failure');
        });
        try {
            app(BsUsageCheckService::class)->store(User::findOrFail(2), 'OPR', $source->id, $this->payload($source));
            $this->fail('Expected an insert failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated detail failure', $exception->getMessage());
        } finally {
            BsUsageCheckDetail::flushEventListeners();
        }
        $this->assertDatabaseCount('bs_usage_checks', 0);
        $this->assertDatabaseCount('bs_usage_check_details', 0);
        $this->assertDatabaseCount('document_number_sequences', 0);
        $this->assertSame('CEKBS-20260925-001', $this->save($source)->document_no);
    }

    public function test_all_endpoints_require_finance_permission(): void
    {
        $source = $this->source();
        $check = $this->save($source);
        $urls = [route('bs-usage-checks.index'), route('bs-usage-checks.create', ['OPR', $source->id]), route('bs-usage-checks.report'), route('bs-usage-checks.show', $check)];
        $this->actingAs(User::findOrFail(1));
        foreach ($urls as $url) $this->get($url)->assertForbidden();
        $this->postJson(route('bs-usage-checks.store', ['OPR', $source->id]), $this->payload($source))->assertForbidden();
        auth()->logout();
        foreach ($urls as $url) $this->get($url)->assertRedirect(route('login'));
        $this->assertDatabaseCount('bs_usage_checks', 1);
    }

    public function test_unified_finance_list_combines_sources_and_latest_results_without_counting_history_twice(): void
    {
        $opr = $this->source();
        $pd = $this->source('PD', [1 => '200']);
        $this->save($opr, approved: [1 => '80']);
        $latest = $this->save($opr, approved: [1 => '90']);
        foreach (['DRAFT' => 999, 'VALIDATED' => 300] as $status => $amount) {
            DB::table('perongkosan_event_dalam_kota')->insert([
                'transaksi_id' => 'EDK-'.$status, 'tanggal' => '2026-08-01', 'periode' => '2026-08',
                'user_id' => 3, 'nominal' => $amount, 'status' => $status,
            ]);
        }
        $response = $this->get(route('bs-usage-checks.index'))->assertOk()
            ->assertSee('Transaksi dan hasil Finance terakhir')->assertDontSee('Rincian &amp; riwayat', false)
            ->assertSee('EDK-VALIDATED')->assertDontSee('EDK-DRAFT')->assertDontSee('Laporan Pengecekan BS')
            ->assertViewHas('sources', fn ($sources) => $sources->total() === 3);
        $rows = $response->viewData('sources')->keyBy('jenis');
        $this->assertEquals($latest->id, $rows['OPR']->check_id);
        $this->assertEquals(140, $rows['OPR']->nominal_finance);
        $this->assertEquals(10, $rows['OPR']->selisih);
        $this->assertSame('PENDING', $rows['PD']->finance_status);
        $this->assertNull($rows['PD']->selisih);
        $this->assertSame('VALIDATED', $rows['OPR']->source_status);
        $this->assertEquals(650, $response->viewData('totals')->nominal_asal);
        $this->assertEquals(140, $response->viewData('totals')->nominal_disetujui);
        $this->assertEquals(2, $response->viewData('totals')->unresolved_count);
        $this->get(route('bs-usage-checks.index', ['status_finance' => 'PENDING']))->assertOk()
            ->assertViewHas('sources', fn ($sources) => $sources->total() === 2);
        $this->get(route('bs-usage-checks.index', ['source_id' => $pd->id, 'jenis_source' => 'PD']))->assertOk()
            ->assertViewHas('sources', fn ($sources) => $sources->total() === 1 && $sources->first()->jenis === 'PD');
    }

    public function test_unified_finance_excludes_stale_and_provisional_amounts_from_final_totals(): void
    {
        $changed = $this->source(amounts: [1 => '100']);
        $this->save($changed, approved: [1 => '80']);
        $changed->details()->update(['nominal' => 120]);
        $revision = $this->source(amounts: [1 => '200']);
        $this->legacyFinanceRevision($revision, 'OPR', '150', 'Lengkapi rincian');
        $rejected = $this->source(amounts: [1 => '300']);
        $rejectedData = $this->payload($rejected);
        $rejectedData += ['keputusan' => 'REJECTED', 'catatan_keputusan' => 'Tidak disetujui'];
        $this->post(route('bs-usage-checks.store', ['OPR', $rejected->id]), $rejectedData)->assertSessionHasNoErrors();
        $response = $this->get(route('bs-usage-checks.index'))->assertOk();
        $sources = $response->viewData('sources')->keyBy('source_id');
        $this->assertSame('PENDING', $sources[$changed->id]->finance_status);
        $this->assertNull($sources[$changed->id]->selisih);
        $this->assertSame('REVISION', $sources[$revision->id]->finance_status);
        $this->assertNull($sources[$revision->id]->selisih);
        $this->assertSame('REJECTED', $sources[$rejected->id]->finance_status);
        $this->assertEquals(300, $sources[$rejected->id]->selisih);
        $this->assertEquals(620, $response->viewData('totals')->nominal_asal);
        $this->assertEquals(0, $response->viewData('totals')->nominal_disetujui);
        $this->assertEquals(300, $response->viewData('totals')->selisih);
        $this->assertEquals(2, $response->viewData('totals')->unresolved_count);
        foreach (['PENDING' => $changed->id, 'REVISION' => $revision->id, 'REJECTED' => $rejected->id] as $status => $id) {
            $this->get(route('bs-usage-checks.index', ['status_finance' => $status]))->assertOk()
                ->assertViewHas('sources', fn ($sources) => $sources->total() === 1 && $sources->first()->source_id === $id);
        }
        $this->get(route('bs-usage-checks.index', ['status_finance' => 'PENDING', 'status_selisih' => 'tidak_sesuai']))
            ->assertOk()->assertViewHas('sources', fn ($sources) => $sources->isEmpty());
        $this->get(route('bs-usage-checks.index', ['status_finance' => 'RECHECK']))->assertOk()
            ->assertDontSee('Perlu Cek Ulang')->assertDontSee('value="RECHECK"', false)
            ->assertViewHas('filters', fn ($filters) => $filters['status_finance'] === 'PENDING')
            ->assertViewHas('sources', fn ($sources) => $sources->total() === 1
                && $sources->first()->source_id === $changed->id && $sources->first()->finance_status === 'PENDING');
    }

    public function test_unified_finance_filters_use_latest_saved_check_and_item_differences(): void
    {
        $opr = $this->source();
        $this->save($opr, date: '2026-09-25');
        $latest = $this->save($opr, approved: [1 => '90', 2 => '60'], date: '2026-08-01');
        $this->source('PD'); // A pending source must not be treated as matching or checked in September.
        $this->get(route('bs-usage-checks.index', ['periode' => '2026-09']))->assertOk()
            ->assertViewHas('sources', fn ($sources) => $sources->isEmpty());
        $this->get(route('bs-usage-checks.index', ['tanggal_awal' => '2026-09-01']))->assertOk()
            ->assertViewHas('sources', fn ($sources) => $sources->isEmpty());
        $this->get(route('bs-usage-checks.index', ['periode' => '202608', 'tanggal_akhir' => '2026-08-01', 'status_selisih' => 'tidak_sesuai']))
            ->assertOk()->assertViewHas('sources', fn ($sources) => $sources->total() === 1 && $sources->first()->check_id === $latest->id)
            ->assertViewHas('totals', fn ($totals) => (float) $totals->selisih === 0.0);
        $this->get(route('bs-usage-checks.index', ['status_selisih' => 'sesuai']))->assertOk()
            ->assertViewHas('sources', fn ($sources) => $sources->isEmpty());
        foreach ([['tampilan' => 'bad'], ['status_finance' => 'bad'], ['mode' => 'bad'], ['periode' => '2026-13'], ['source_id' => 1], ['page' => 0]] as $filters) {
            $this->getJson(route('bs-usage-checks.index', $filters))->assertUnprocessable();
        }
    }

    public function test_unified_finance_history_preserves_deleted_sources_and_legacy_report_links(): void
    {
        $source = $this->source(amounts: [1 => '100']);
        $first = $this->save($source, approved: [1 => '80']);
        $last = $this->save($source, approved: [1 => '90']);
        $source->delete();
        $filters = ['tampilan' => 'rincian', 'mode' => 'semua', 'source_id' => $source->id, 'jenis_source' => 'OPR'];
        foreach (['bs-usage-checks.index', 'bs-usage-checks.report'] as $route) {
            $this->get(route($route, $filters))->assertOk()->assertViewIs('bs-usage-checks.index')
                ->assertSee('id="finance-workspace"', false)->assertSee($first->document_no)->assertSee($last->document_no)
                ->assertDontSee('Laporan Pengecekan BS')
                ->assertViewHas('rows', fn ($rows) => $rows->total() === 2)
                ->assertViewHas('totals', fn ($totals) => (float) $totals->nominal_disetujui === 170.0);
        }
        $this->get(route('bs-usage-checks.index', array_replace($filters, ['mode' => 'terakhir'])))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->bs_usage_check_id === $last->id);
        $this->actingAs(User::findOrFail(1));
        $this->get(route('bs-usage-checks.index', $filters))->assertForbidden();
    }

    public function test_unified_finance_totals_and_filters_cover_all_source_pages(): void
    {
        for ($i = 0; $i < 21; $i++) $this->source(amounts: [1 => '1.01']);
        $filters = ['jenis' => 'OPR', 'user_id' => 1, 'status_finance' => 'PENDING', 'page' => 2];
        $response = $this->get(route('bs-usage-checks.index', $filters))->assertOk()
            ->assertViewHas('sources', fn ($sources) => $sources->total() === 21 && $sources->count() === 1)
            ->assertViewHas('totals', fn ($totals) => abs($totals->nominal_asal - 21.21) < 0.00001);
        $this->assertStringContainsString('status_finance=PENDING', $response->viewData('sources')->url(1));
        $this->assertStringContainsString('jenis=OPR', $response->viewData('sources')->url(1));
        $this->assertStringContainsString('user_id=1', $response->viewData('sources')->url(1));
    }

    public function test_footer_totals_cover_all_pages_and_invalid_filters_are_rejected(): void
    {
        $source = $this->source(amounts: [1 => '1.01']);
        for ($i = 0; $i < 31; $i++) $this->save($source);
        $this->get(route('bs-usage-checks.report', ['mode' => 'semua', 'page' => 2]))->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 31 && $rows->count() === 1)
            ->assertViewHas('totals', fn ($totals) => abs($totals->nominal_asal - 31.31) < 0.00001)
            ->assertSee('31,31')->assertSee('mode=semua');
        foreach ([['mode' => 'bad'], ['jenis' => 'EVENT'], ['periode' => '2026-13'], ['status_selisih' => 'bad'], ['tanggal_awal' => '2026-09-10', 'tanggal_akhir' => '2026-09-01'], ['source_id' => 1]] as $filters) {
            $this->getJson(route('bs-usage-checks.report', $filters))->assertUnprocessable();
        }
        $this->get(route('bs-usage-checks.report', ['periode' => '2027-01']))->assertOk()->assertViewHas('totals', fn ($totals) => (float) $totals->nominal_asal === 0.0);
    }
}
