<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Services\UserPasswordService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

trait UserPasswordVisibilityScenarios
{
    private function passwordUserFixture(): User
    {
        $this->seedMasterData();
        $this->user->givePermissionTo('akses.master-data');
        $this->actingAs($this->user);

        return User::findOrFail(1);
    }

    private function passwordUserData(array $overrides = []): array
    {
        return array_replace([
            'nama' => 'Pengguna Uji', 'username' => 'pengguna_uji',
            'company_id' => 1, 'roles_id' => 1, 'level_id' => 1,
            'position_id' => 1, 'homebase_id' => 2,
        ], $overrides);
    }

    public function test_master_password_creation_stores_hash_and_encrypted_copy_without_exposing_them(): void
    {
        $this->passwordUserFixture();
        $password = 'Password-uji-2026';
        $this->post(route('users.store'), $this->passwordUserData([
            'username' => 'user_baru', 'password' => $password,
        ]))->assertSessionHasNoErrors()->assertRedirect(route('users.create'));

        $created = User::where('username', 'user_baru')->firstOrFail();
        $this->assertTrue(Auth::getProvider()->validateCredentials($created, ['password' => $password]));
        $this->assertNotSame($password, $created->password);
        $this->assertNotSame($password, $created->password_encrypted);
        $this->assertSame($password, Crypt::decryptString($created->password_encrypted));
        $this->assertArrayNotHasKey('password', $created->toArray());
        $this->assertArrayNotHasKey('password_encrypted', $created->toArray());

        foreach ([route('users.create'), route('users.edit', $created)] as $url) {
            $this->get($url)->assertOk()->assertDontSee($password, false)
                ->assertDontSee($created->password, false)->assertDontSee($created->password_encrypted, false);
        }
        $this->get(route('users.edit', $created))->assertSee('data-stored-password-toggle', false);
        $this->postJson(route('users.password.reveal', $created))->assertOk()->assertExactJson(['password' => $password]);
    }

    public function test_master_password_update_and_blank_password_keep_login_and_reveal_consistent(): void
    {
        $target = $this->passwordUserFixture();
        DB::table('user')->where('id', $target->id)->update(['password' => Hash::make('Password-lama')]);

        $this->put(route('users.update', $target), $this->passwordUserData(['password' => 'Password-baru']))
            ->assertSessionHasNoErrors()->assertRedirect(route('users.create'));
        $target->refresh();
        $this->assertTrue(Hash::check('Password-baru', $target->password));
        $this->assertFalse(Hash::check('Password-lama', $target->password));
        $this->postJson(route('users.password.reveal', $target))->assertOk()->assertExactJson(['password' => 'Password-baru']);
        $hash = $target->password;
        $encrypted = $target->password_encrypted;

        $this->put(route('users.update', $target), $this->passwordUserData([
            'nama' => 'Nama diperbarui', 'password' => '', 'password_encrypted' => 'injected-value',
        ]))->assertSessionHasNoErrors();
        $target->refresh();
        $this->assertSame('Nama diperbarui', $target->nama);
        $this->assertSame($hash, $target->password);
        $this->assertSame($encrypted, $target->password_encrypted);
        $this->postJson(route('users.password.reveal', $target))->assertOk()->assertExactJson(['password' => 'Password-baru']);
    }

    public function test_master_password_reveal_requires_login_permission_and_post(): void
    {
        $target = $this->passwordUserFixture();
        app(UserPasswordService::class)->assign($target, 'Password-private');
        $target->save();

        $this->actingAs($target)->postJson(route('users.password.reveal', $target))->assertForbidden()
            ->assertDontSee('Password-private');
        Auth::logout();
        $this->postJson(route('users.password.reveal', $target))->assertUnauthorized();
        $this->actingAs($this->user)->getJson(route('users.password.reveal', $target))->assertStatus(405);
    }

    public function test_master_password_reveal_does_not_allow_requests_without_csrf_token(): void
    {
        $target = $this->passwordUserFixture();
        app(UserPasswordService::class)->assign($target, 'Password-csrf');
        $target->save();
        $this->app['env'] = 'local';

        try {
            $this->postJson(route('users.password.reveal', $target))->assertStatus(419);
            $this->withSession(['_token' => 'password-test-token'])
                ->withHeader('X-CSRF-TOKEN', 'password-test-token')
                ->postJson(route('users.password.reveal', $target))->assertOk();
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    public function test_master_password_reveal_disables_cache_and_audits_only_user_ids(): void
    {
        $target = $this->passwordUserFixture();
        app(UserPasswordService::class)->assign($target, 'Password-audit');
        $target->save();
        Log::spy();

        $response = $this->postJson(route('users.password.reveal', $target))->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('Pragma', 'no-cache');
        Log::shouldHaveReceived('notice')->once()->with('Master User password viewed', [
            'actor_id' => $this->user->id, 'user_id' => $target->id,
        ]);
    }

    public function test_master_password_legacy_or_obsolete_encrypted_values_are_not_revealed(): void
    {
        $target = $this->passwordUserFixture();
        $target->password = Hash::make('Password-lama');
        $target->save();
        $this->get(route('users.edit', $target))->assertOk()
            ->assertSeeText('Password lama belum tersedia')->assertDontSee('data-stored-password-toggle', false);
        $this->postJson(route('users.password.reveal', $target))->assertStatus(409)->assertJsonMissingPath('password');

        foreach ([Crypt::encryptString('Password-berbeda'), 'invalid-ciphertext'] as $encrypted) {
            $target->password_encrypted = $encrypted;
            $target->save();
            $this->postJson(route('users.password.reveal', $target))->assertStatus(409)
                ->assertJsonMissingPath('password')->assertDontSee('Password-berbeda')->assertDontSee($encrypted);
        }
    }

    public function test_master_password_failed_validation_preserves_existing_credentials(): void
    {
        $target = $this->passwordUserFixture();
        app(UserPasswordService::class)->assign($target, 'Password-valid');
        $target->save();
        $hash = $target->password;
        $encrypted = $target->password_encrypted;

        $this->from(route('users.edit', $target))->put(route('users.update', $target), $this->passwordUserData([
            'password' => 'short',
        ]))->assertSessionHasErrors('password')->assertSessionMissing('_old_input.password');
        $target->refresh();
        $this->assertSame($hash, $target->password);
        $this->assertSame($encrypted, $target->password_encrypted);
    }

    public function test_master_password_migration_keeps_existing_login_hashes(): void
    {
        $target = $this->passwordUserFixture();
        $target->password = Hash::make('Legacy-password');
        $target->save();
        Schema::table('user', fn (Blueprint $table) => $table->dropColumn('password_encrypted'));
        $migration = require database_path('migrations/2026_10_16_100000_add_password_encrypted_to_user_table.php');
        $migration->up();
        $this->assertSame($target->password, $target->fresh()->password);
        $this->assertNull($target->fresh()->password_encrypted);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('user', 'password_encrypted'));
        $this->assertSame($target->password, $target->fresh()->password);
    }
}
