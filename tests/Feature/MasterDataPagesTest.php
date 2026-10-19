<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MasterDataPagesTest extends TestCase
{
    use \Tests\Concerns\UserPasswordVisibilityScenarios;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->createSchema();
        (require database_path('migrations/2026_07_16_075422_create_permission_tables.php'))->up();
        Permission::create(['name' => 'akses.master-data', 'guard_name' => 'web']);
        DB::table('user')->insert(['id' => 9999, 'nama' => 'Operator Master Data', 'username' => 'operator']);
        $this->user = User::findOrFail(9999);
    }

    public static function pages(): array
    {
        return [
            'roles' => ['roles', 'Daftar Master Role', ['Role Uji', 'Non-Aktif']],
            'levels' => ['levels', 'Daftar Master Level', ['Level Uji']],
            'weekday groups' => ['weekday_groups', 'Daftar Master Weekday Group', ['Kelompok Hari Uji']],
            'positions' => ['positions', 'Daftar Master Position', ['Jabatan Uji']],
            'cost categories' => ['cost_categories', 'Daftar Master Cost Category', ['Kategori Biaya Uji']],
            'companies' => ['companies', 'Daftar Master Company', ['Perusahaan Uji']],
            'users' => ['users', 'Daftar Master User', ['Pengguna Uji', 'COMPANY_TEST', 'LEVEL_TEST', 'POSITION_TEST']],
            'wilayah' => ['wilayah', 'Form Tambah Master Wilayah', ['Wilayah Uji Semarang', 'Wilayah Induk Uji']],
            'event hour groups' => ['event_hour_groups', 'Daftar Master Event Hour Group', ['Jam Event Uji']],
            'travel plafon' => ['setting_plafon_perjalanan_dinas', 'Daftar Setting Plafon Perjalanan Dinas', ['Plafon Dinas Uji', 'Wilayah Uji Semarang', 'Wilayah Induk Uji', 'Level Uji', '150.000']],
            'local event plafon' => ['plafon-event-dalam-kota', 'Daftar Plafon Event Dalam Kota', ['Plafon Dalam Kota Uji', 'Level Uji', 'Kelompok Hari Uji', '8 Jam', '150.000']],
            'out of town event plafon' => ['plafon-event-luar-kota', 'Daftar Plafon Event Luar Kota', ['Plafon Luar Kota Uji', 'Level Uji', 'Kelompok Hari Uji', '8 Jam', '150.000']],
        ];
    }

    #[DataProvider('pages')]
    public function test_index_and_create_display_forms_and_master_data(string $route, string $heading, array $values): void
    {
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);

        foreach (['index', 'create'] as $action) {
            $this->get(route($route.'.'.$action))
                ->assertOk()
                ->assertSeeText($heading)
                ->assertSee('action="'.route($route.'.store').'"', false)
                ->assertSee('<table', false);
        }

        $this->seedMasterData();

        foreach (['index', 'create'] as $action) {
            $this->get(route($route.'.'.$action))
                ->assertOk()
                ->assertSeeText($values)
                ->assertSeeText('Edit')
                ->assertSeeText('Hapus');
        }
    }

    public function test_master_data_pages_require_login(): void
    {
        foreach (self::pages() as [$route]) {
            foreach (['index', 'create'] as $action) {
                $this->get(route($route.'.'.$action))->assertRedirect(route('login'));
            }
        }
    }

    public function test_cost_category_creation_saves_the_code_in_nama(): void
    {
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);

        $this->post(route('cost_categories.store'), [
            'code' => 'OPRT SALES', 'description' => 'Operational Salesman', 'is_active' => 'on',
        ])->assertSessionHasNoErrors()->assertRedirect(route('cost_categories.create'));

        $this->assertDatabaseHas('cost_category', [
            'nama' => 'OPRT SALES', 'description' => 'Operational Salesman', 'is_active' => 1,
        ]);
    }

    public function test_cost_category_edit_displays_and_updates_the_existing_code(): void
    {
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);
        DB::table('cost_category')->insert(['id' => 7, 'nama' => 'OPRT SALES', 'description' => 'Operational Salesman']);

        $this->get(route('cost_categories.edit', 7))->assertOk()->assertSee('value="OPRT SALES"', false);
        $this->put(route('cost_categories.update', 7), [
            'code' => 'OPERASIONAL SALES', 'description' => 'Biaya Operasional Sales', 'is_active' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('cost_categories.create'));

        $this->assertDatabaseHas('cost_category', [
            'id' => 7, 'nama' => 'OPERASIONAL SALES', 'description' => 'Biaya Operasional Sales', 'is_active' => 1,
        ]);
    }

    public function test_cost_category_can_change_active_status_without_changing_transaction_references(): void
    {
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);
        DB::table('cost_category')->insert(['id' => 7, 'nama' => 'OPRT SALES', 'description' => 'Operational Salesman']);
        Schema::create('transaksi_operasional_area_sales_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_category_id')->constrained('cost_category')->restrictOnDelete();
        });
        DB::table('transaksi_operasional_area_sales_detail')->insert(['id' => 1, 'cost_category_id' => 7]);

        $data = ['code' => 'OPRT SALES', 'description' => 'Operational Salesman'];
        $this->put(route('cost_categories.update', 7), $data)
            ->assertSessionHasNoErrors()->assertRedirect(route('cost_categories.create'));
        $this->assertDatabaseHas('cost_category', ['id' => 7, 'nama' => 'OPRT SALES', 'is_active' => 0]);
        $this->assertDatabaseHas('transaksi_operasional_area_sales_detail', ['id' => 1, 'cost_category_id' => 7]);

        $this->put(route('cost_categories.update', 7), $data + ['is_active' => '1'])
            ->assertSessionHasNoErrors()->assertRedirect(route('cost_categories.create'));
        $this->assertDatabaseHas('cost_category', ['id' => 7, 'nama' => 'OPRT SALES', 'is_active' => 1]);
        $this->assertDatabaseHas('transaksi_operasional_area_sales_detail', ['id' => 1, 'cost_category_id' => 7]);
    }

    public function test_cost_category_duplicate_codes_are_rejected_on_create_and_update(): void
    {
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);
        DB::table('cost_category')->insert([
            ['id' => 7, 'nama' => 'OPRT SALES', 'description' => 'Operational Salesman'],
            ['id' => 8, 'nama' => 'HOTEL', 'description' => 'Hotel'],
        ]);
        $duplicate = ['code' => 'OPRT SALES', 'description' => 'Duplikat', 'is_active' => '1'];

        $this->from(route('cost_categories.create'))->post(route('cost_categories.store'), $duplicate)
            ->assertSessionHasErrors('code')->assertRedirect(route('cost_categories.create'));
        $this->from(route('cost_categories.edit', 8))->put(route('cost_categories.update', 8), $duplicate)
            ->assertSessionHasErrors('code')->assertRedirect(route('cost_categories.edit', 8));
        $this->assertDatabaseCount('cost_category', 2);
        $this->assertDatabaseHas('cost_category', ['id' => 8, 'nama' => 'HOTEL', 'description' => 'Hotel']);
    }

    public function test_cost_category_deletion_keeps_used_categories_and_removes_unused_categories(): void
    {
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);
        DB::table('cost_category')->insert([
            ['id' => 7, 'nama' => 'OPRT SALES', 'is_active' => false],
            ['id' => 8, 'nama' => 'Belum Dipakai', 'is_active' => false],
        ]);
        Schema::create('transaksi_operasional_area_sales_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_category_id')->constrained('cost_category')->restrictOnDelete();
        });
        DB::table('transaksi_operasional_area_sales_detail')->insert(['id' => 1, 'cost_category_id' => 7]);

        $this->get(route('cost_categories.index'))->assertOk()->assertSee('OPRT SALES')->assertSee('Non-Aktif');
        $this->delete(route('cost_categories.destroy', 7))->assertRedirect()->assertSessionHasErrors('error');
        $this->assertDatabaseHas('cost_category', ['id' => 7, 'is_active' => false]);
        $this->assertDatabaseHas('transaksi_operasional_area_sales_detail', ['id' => 1, 'cost_category_id' => 7]);

        $this->delete(route('cost_categories.destroy', 8))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('cost_category', ['id' => 8]);
    }

    public function test_master_data_pages_require_master_data_permission(): void
    {
        $this->actingAs($this->user);

        foreach (self::pages() as [$route]) {
            foreach (['index', 'create'] as $action) {
                $this->get(route($route.'.'.$action))->assertForbidden();
            }
        }
    }

    public function test_wilayah_index_preserves_search_and_pagination(): void
    {
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);
        $this->seedMasterData();
        for ($i = 1; $i <= 21; $i++) {
            DB::table('wilayah')->insert(['code' => sprintf('SEARCH-%02d', $i), 'nama' => sprintf('Cari Wilayah %02d', $i)]);
        }

        $nextPage = route('wilayah.index', ['search' => 'Cari Wilayah', 'page' => 2]);
        $this->get(route('wilayah.index', ['search' => 'Cari Wilayah']))
            ->assertOk()
            ->assertSeeText('Cari Wilayah 01')
            ->assertDontSeeText('Cari Wilayah 21')
            ->assertSee('href="'.e($nextPage).'"', false)
            ->assertSee('<strong>21</strong>', false)
            ->assertDontSeeText('Wilayah Uji Semarang');

        $this->get($nextPage)
            ->assertOk()
            ->assertSee('value="Cari Wilayah"', false)
            ->assertSeeText('Cari Wilayah 21')
            ->assertDontSeeText('Cari Wilayah 01');
    }

    public function test_travel_plafon_index_preserves_pagination(): void
    {
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);
        $this->seedMasterData();
        $plafon = (array) DB::table('setting_plafon_perjalanan_dinas')->first();
        unset($plafon['id']);
        for ($i = 1; $i <= 20; $i++) {
            DB::table('setting_plafon_perjalanan_dinas')->insert(array_replace($plafon, ['description' => 'Plafon Dinas Halaman '.$i]));
        }

        $nextPage = route('setting_plafon_perjalanan_dinas.index', ['page' => 2]);
        $this->get(route('setting_plafon_perjalanan_dinas.index'))
            ->assertOk()
            ->assertSee('href="'.$nextPage.'"', false)
            ->assertSeeText('Plafon Dinas Uji')
            ->assertDontSeeText('Plafon Dinas Halaman 20');
        $this->get($nextPage)
            ->assertOk()
            ->assertSeeText('Plafon Dinas Halaman 20')
            ->assertDontSeeText('Plafon Dinas Uji');
    }

    private function createSchema(): void
    {
        foreach (['roles', 'company', 'level', 'position'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('code');
                $table->string('nama')->nullable();
                $table->boolean('is_active')->default(true);
            });
        }
        foreach (['cost_category', 'weekday_group', 'event_hour_group'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                if ($name === 'cost_category') {
                    $table->string('nama')->nullable();
                } else {
                    $table->string('code')->nullable();
                }
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(true);
            });
        }
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('nama');
            $table->integer('parent_id')->nullable();
            $table->string('teritory')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username');
            $table->string('password')->nullable();
            $table->text('password_encrypted')->nullable();
            $table->string('NIK')->nullable();
            foreach (['company_id', 'roles_id', 'level_id', 'position_id', 'homebase_id', 'atasan_user_id'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->boolean('is_active')->default(true);
        });
        Schema::create('setting_plafon_perjalanan_dinas', function (Blueprint $table) {
            $table->id();
            $table->integer('provinsi_awal_id');
            $table->integer('provinsi_tujuan_id');
            $table->integer('level_id');
            $table->string('description');
            $table->decimal('default_value', 15, 2);
            $table->date('tanggal_berlaku');
        });
        foreach (['setting_plafon_event_marketing_dalam_kota', 'setting_plafon_event_marketing_luar_kota'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->date('tanggal_berlaku');
                $table->integer('level_id');
                $table->integer('kelompok_hari_kerja_id');
                $table->integer('jam_kerja_event_id');
                $table->string('hari_event')->nullable();
                $table->string('description');
                $table->decimal('default_value', 15, 2);
            });
        }
    }

    private function seedMasterData(): void
    {
        DB::table('roles')->insert(['id' => 1, 'code' => 'ROLE_TEST', 'nama' => 'Role Uji', 'is_active' => false]);
        DB::table('company')->insert(['id' => 1, 'code' => 'COMPANY_TEST', 'nama' => 'Perusahaan Uji']);
        DB::table('level')->insert(['id' => 1, 'code' => 'LEVEL_TEST', 'nama' => 'Level Uji']);
        DB::table('position')->insert(['id' => 1, 'code' => 'POSITION_TEST', 'nama' => 'Jabatan Uji']);
        DB::table('cost_category')->insert(['id' => 1, 'nama' => 'Kategori Biaya Uji', 'description' => 'Biaya Uji']);
        DB::table('weekday_group')->insert(['id' => 1, 'code' => '5 HK', 'description' => 'Kelompok Hari Uji']);
        DB::table('event_hour_group')->insert(['id' => 1, 'code' => '8 Jam', 'description' => 'Jam Event Uji']);
        DB::table('wilayah')->insert([
            ['id' => 1, 'code' => 'WIL-01', 'nama' => 'Wilayah Induk Uji', 'parent_id' => null, 'teritory' => 'Jawa'],
            ['id' => 2, 'code' => 'WIL-02', 'nama' => 'Wilayah Uji Semarang', 'parent_id' => 1, 'teritory' => 'Jawa'],
        ]);
        DB::table('user')->insert(['id' => 1, 'nama' => 'Pengguna Uji', 'username' => 'pengguna_uji', 'company_id' => 1, 'roles_id' => 1, 'level_id' => 1, 'position_id' => 1, 'homebase_id' => 2]);
        DB::table('setting_plafon_perjalanan_dinas')->insert(['provinsi_awal_id' => 1, 'provinsi_tujuan_id' => 2, 'level_id' => 1, 'description' => 'Plafon Dinas Uji', 'default_value' => 150000, 'tanggal_berlaku' => '2026-09-18']);
        foreach (['dalam' => 'Dalam', 'luar' => 'Luar'] as $name => $label) {
            DB::table('setting_plafon_event_marketing_'.$name.'_kota')->insert(['tanggal_berlaku' => '2026-09-18', 'level_id' => 1, 'kelompok_hari_kerja_id' => 1, 'jam_kerja_event_id' => 1, 'hari_event' => 'Reguler', 'description' => 'Plafon '.$label.' Kota Uji', 'default_value' => 150000]);
        }
    }
}
