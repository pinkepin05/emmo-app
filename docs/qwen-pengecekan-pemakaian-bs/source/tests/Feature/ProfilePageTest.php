<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        foreach (['roles', 'company', 'level', 'position'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable();
                $table->string('nama')->nullable();
            });
        }
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('nama')->nullable();
        });
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('username');
            $table->string('NIK')->nullable();
            foreach (['roles_id', 'company_id', 'level_id', 'position_id', 'homebase_id', 'atasan_user_id'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->string('is_active')->default('Y');
        });
        Schema::create('pengajuan_bs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('atasan_user_id')->nullable();
            $table->string('status');
        });
        (require database_path('migrations/2026_07_16_075422_create_permission_tables.php'))->up();

        DB::table('user')->insert([
            ['id' => 1, 'nama' => 'Budi Supervisor', 'username' => 'budi'],
            ['id' => 2, 'nama' => 'Supervisor Lain', 'username' => 'supervisor_lain'],
        ]);
    }

    public function test_profile_lists_all_direct_reports_including_inactive_accounts_only_for_the_signed_in_user(): void
    {
        $this->seedTeam();
        $this->actingAs(User::findOrFail(1));

        // A supplied user ID must not change whose team is displayed.
        $response = $this->get(route('profile.index', ['user_id' => 2]))
            ->assertOk()
            ->assertSeeText('Bawahan Saya')
            ->assertSeeText('25 orang')
            ->assertSeeText(['NIK-001', 'Sales Representative', 'Semarang', 'Aktif', 'Nonaktif'])
            ->assertSeeText('Belum diatur')
            ->assertSee('href="#daftar-bawahan"', false)
            ->assertDontSeeText(['Anggota Tim Lain', 'Bawahan Tidak Langsung']);

        $names = array_map(fn ($i) => sprintf('Anggota %02d', $i), range(1, 25));
        $response->assertSeeTextInOrder($names);

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($dom);
        $rows = $xpath->query('//*[@id="daftar-bawahan"]//tbody/tr');
        $this->assertCount(25, $rows);
        $this->assertStringContainsString('Nonaktif', $rows->item(24)->textContent);

        $this->actingAs(User::findOrFail(2));
        $this->get(route('profile.index'))
            ->assertOk()
            ->assertSeeText(['1 orang', 'Anggota Tim Lain'])
            ->assertDontSeeText($names);
    }

    public function test_profile_displays_an_empty_state_when_no_users_report_to_the_signed_in_user(): void
    {
        $this->actingAs(User::findOrFail(1));
        $this->get(route('profile.index'))
            ->assertOk()
            ->assertSeeText('0 orang')
            ->assertSeeText('Belum ada pengguna yang terdaftar sebagai bawahan Anda.');
    }

    public function test_profile_requires_login(): void
    {
        $this->get(route('profile.index'))->assertRedirect(route('login'));
    }

    protected function seedTeam(): void
    {
        DB::table('position')->insert(['id' => 1, 'code' => 'SR', 'nama' => 'Sales Representative']);
        DB::table('wilayah')->insert(['id' => 1, 'code' => 'SMG', 'nama' => 'Semarang']);
        // More than a typical page size, inserted backwards to exercise complete, alphabetical output.
        foreach (range(25, 1) as $i) {
            DB::table('user')->insert([
                'id' => 10 + $i, 'nama' => sprintf('Anggota %02d', $i), 'username' => 'anggota_'.$i,
                'NIK' => sprintf('NIK-%03d', $i), 'atasan_user_id' => 1,
                'position_id' => $i === 1 ? 1 : null, 'homebase_id' => $i === 1 ? 1 : null,
                'is_active' => $i === 25 ? 'N' : 'Y',
            ]);
        }
        DB::table('user')->insert([
            ['id' => 100, 'nama' => 'Anggota Tim Lain', 'username' => 'tim_lain', 'atasan_user_id' => 2],
            ['id' => 101, 'nama' => 'Bawahan Tidak Langsung', 'username' => 'tidak_langsung', 'atasan_user_id' => 11],
        ]);
    }
}
