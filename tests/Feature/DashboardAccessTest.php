<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BonSementaraService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('nama');
        });
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username')->unique();
            $table->string('password');
            $table->integer('roles_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('atasan_user_id')->nullable();
        });
        (require database_path('migrations/2026_07_16_075422_create_permission_tables.php'))->up();
        Permission::create(['name' => 'akses.master-data', 'guard_name' => 'web']);

        // Cash calculations have their own integration suite; these tests cover login and navigation.
        $this->mock(BonSementaraService::class, function (\Mockery\MockInterface $mock) {
            $mock->shouldReceive('getActivePeriode')->andReturn('202609');
            $mock->shouldReceive('getRealtimeSisaSaldo')->andReturn('0.00');
        });

        Schema::create('pengajuan_bs', function (Blueprint $table) {
            $table->id(); $table->integer('user_id'); $table->string('document_no');
            $table->string('periode'); $table->decimal('nominal_pengajuan', 15, 2)->default(0);
        });
        Schema::create('approval_bs_finance', function (Blueprint $table) {
            $table->id(); $table->integer('pengajuan_bs_id'); $table->string('document_no');
            $table->date('tanggal_cair'); $table->decimal('nominal_approved', 15, 2)->default(0);
        });

        // Empty transaction tables let the real dashboard render without operational data.
        foreach (['transaksi_operasional_area_sales_header', 'transaksi_perjalanan_dinas_header', 'perongkosan_event_dalam_kota', 'perongkosan_event_luar_kota'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->string('transaksi_id');
                $table->integer('user_id');
                $table->date('tanggal');
                $table->decimal('nominal', 15, 2)->default(0);
                $table->boolean('is_over_plafon')->default(false);
                $table->string('periode')->nullable();
                if (!str_starts_with($name, 'perongkosan_event')) {
                    $table->string('status')->default('DRAFT');
                    $table->timestamp('validated_at')->nullable();
                    $table->integer('validated_by')->nullable();
                }
            });
        }
        foreach (['transaksi_operasional_area_sales_detail', 'transaksi_perjalanan_dinas_detail'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->string('transaksi_id');
                $table->decimal('nominal', 15, 2);
                $table->integer('cost_category_id')->default(1);
                $table->string('remark')->nullable();
                if ($name === 'transaksi_perjalanan_dinas_detail') $table->softDeletes();
            });
        }
        Schema::create('cost_category', function (Blueprint $table) { $table->increments('id'); $table->string('nama'); $table->boolean('is_active')->default(true); });
        (require database_path('migrations/2026_09_25_150000_create_bs_usage_checks.php'))->up();
        (require database_path('migrations/2026_09_28_100000_extend_finance_checks_to_transaction_modules.php'))->up();
        (require database_path('migrations/2026_09_30_160000_add_finance_check_drafts.php'))->up();
        (require database_path('migrations/2026_10_07_160000_track_finance_approval_per_source_detail.php'))->up();
        (require database_path('migrations/2026_10_05_090000_track_finance_resubmissions.php'))->up();
    }

    private function createUser(?string $roleCode): User
    {
        $roleId = $roleCode === null ? null : DB::table('roles')->insertGetId(['code' => $roleCode, 'nama' => $roleCode]);
        $id = DB::table('user')->insertGetId([
            'nama' => 'Pengguna Uji', 'username' => 'pengguna', 'roles_id' => $roleId,
            'password' => Hash::make('password-uji'),
        ]);
        $user = User::findOrFail($id);
        if (in_array($roleCode, ['ADMIN', 'ADMINISTRATOR', 'HR'], true)) {
            $user->givePermissionTo('akses.master-data');
        }

        return $user;
    }

    public static function roles(): array
    {
        return [
            'admin' => ['ADMIN', true],
            'administrator' => ['ADMINISTRATOR', true],
            'sales' => ['SALES', false],
            'sales representative' => ['SR', false],
            'finance' => ['FINANCE', false],
            'director' => ['DIR', false],
            'hr with master data permission' => ['HR', false],
            'custom role' => ['SUPERVISOR', false],
            'no role' => [null, false],
        ];
    }

    #[DataProvider('roles')]
    public function test_login_dashboard_access_and_navigation_follow_the_role(?string $roleCode, bool $isAdmin): void
    {
        $user = $this->createUser($roleCode);
        $destination = route($isAdmin ? 'dashboard' : 'dashboard.transaksi');
        $this->post(route('proses_login'), ['username' => 'pengguna', 'password' => 'password-uji'])
            ->assertRedirect($destination);
        $this->assertAuthenticatedAs($user);

        // Already signed-in users also go straight to their dashboard from /login.
        $this->get(route('login'))->assertRedirect($destination);
        $this->post(route('proses_login'))->assertRedirect($destination);

        if ($isAdmin) {
            $this->get(route('dashboard'))->assertOk()->assertViewIs('dashboard.portal')
                ->assertSee('href="'.route('dashboard.transaksi').'"', false)
                ->assertSee('href="'.route('dashboard.master_data').'"', false);
            $this->get(route('dashboard.transaksi'))->assertOk()->assertSeeText('Dashboard Portal');
        } else {
            $this->get(route('dashboard'))->assertRedirect(route('dashboard.transaksi'));
            $response = $this->get(route('dashboard.transaksi'))->assertOk()->assertViewIs('dashboard.transaksi')
                ->assertSeeText('Dashboard Transaksi')->assertDontSeeText('Dashboard Portal')
                ->assertDontSee('href="'.route('dashboard').'"', false);
            if ($roleCode === 'HR') {
                $response->assertSee('href="'.route('dashboard.master_data').'"', false);
            }
        }
    }

    #[DataProvider('roles')]
    public function test_login_always_opens_the_role_dashboard_even_with_a_previous_destination(?string $roleCode, bool $isAdmin): void
    {
        $this->createUser($roleCode);
        $this->withSession(['url.intended' => route('dashboard.master_data')])
            ->post(route('proses_login'), ['username' => 'pengguna', 'password' => 'password-uji'])
            ->assertRedirect(route($isAdmin ? 'dashboard' : 'dashboard.transaksi'))
            ->assertSessionMissing('url.intended');
    }

    public function test_guests_must_log_in_before_accessing_either_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('dashboard.transaksi'))->assertRedirect(route('login'));
    }

    public function test_failed_login_does_not_grant_dashboard_access(): void
    {
        $this->createUser('ADMIN');
        $this->from(route('login'))->post(route('proses_login'), ['username' => 'pengguna', 'password' => 'salah'])
            ->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
