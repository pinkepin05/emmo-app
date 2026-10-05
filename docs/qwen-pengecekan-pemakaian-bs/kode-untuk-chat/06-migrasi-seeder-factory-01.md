# Kode existing: migrasi-seeder-factory, bagian 1

Snapshot referensi. Bukan implementasi fitur baru. Semua file di bawah disertakan utuh.

## FILE: database/factories/UserFactory.php

SHA-256: `50bdc543f1b09d1a57260be55ad0e1a68aee4b34f63e1109dc94d9bcd3080526`

~~~~php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

~~~~

## FILE: database/migrations/0001_01_01_000000_create_users_table.php

SHA-256: `380c085f6088eeeb0ec8d5541c7b6e832fe566c5f27ebf3eb87b2d855ca70e85`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        

        

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

~~~~

## FILE: database/migrations/0001_01_01_000001_create_cache_table.php

SHA-256: `2ca8398390ecef8eaf72aeac7fefce67f7de631b6f2ec7b7081c8db8953215dc`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};

~~~~

## FILE: database/migrations/0001_01_01_000002_create_jobs_table.php

SHA-256: `7c9c712adf4091c87bf2612f4d0dccdfa1df4501553e4ac068b83d7bd5462fa5`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};

~~~~

## FILE: database/migrations/2026_06_17_050908_create_personal_access_tokens_table.php

SHA-256: `c81ecde85ea5072188c468dcaf79776728b6eb8f684d63fc0d88a4908484ca75`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

~~~~

## FILE: database/migrations/2026_07_07_065552_add_jarak_durasi_to_perongkosan_event_luar_kota_table.php

SHA-256: `7e0bb3f364612b03420e16a960a2d31df030bdf706a8d08d88bb6e505a860755`

~~~~php
// database/migrations/xxxx_xx_xx_add_jarak_durasi_to_perongkosan_event_luar_kota_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perongkosan_event_luar_kota', function (Blueprint $table) {
            $table->decimal('jarak_km', 10, 2)->default(0)->after('lokasi_event_id');
            $table->decimal('durasi_jam', 5, 2)->default(0)->after('jarak_km');
        });
    }

    public function down(): void
    {
        Schema::table('perongkosan_event_luar_kota', function (Blueprint $table) {
            $table->dropColumn(['jarak_km', 'durasi_jam']);
        });
    }
};

~~~~

## FILE: database/migrations/2026_07_07_095857_add_jarak_durasi_to_transaksi_perjalanan_dinas_header_table.php

SHA-256: `90fcba0c3a52c5c47feacb0185e8acab76594730a9e6316a36ae1a805b7e5c64`

~~~~php
// database/migrations/xxxx_xx_xx_add_jarak_durasi_to_transaksi_perjalanan_dinas_header_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            $table->decimal('jarak_km', 10, 2)->default(0)->after('tujuan_dinas_id');
            $table->decimal('durasi_jam', 5, 2)->default(0)->after('jarak_km');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            $table->dropColumn(['jarak_km', 'durasi_jam']);
        });
    }
};
~~~~

## FILE: database/migrations/2026_07_08_014711_create_wilayah_distances_table.php

SHA-256: `60197d3d4c929d6f33a9ee4fb044d6c440aa03f8cb7d7f5aa7d912933400925e`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengecekan Defensif: Hanya eksekusi pembuatan tabel jika tabel belum ada di database
        if (!Schema::hasTable('wilayah_distances')) {
            Schema::create('wilayah_distances', function (Blueprint $table) {
                $table->id();
                
                // GANTI dengan unsignedInteger() karena tabel wilayah menggunakan int(11)
                $table->unsignedInteger('from_wilayah_id');
                $table->unsignedInteger('to_wilayah_id');
                
                $table->decimal('distance_km', 10, 2)->default(0)->comment('Jarak dalam KM');
                $table->timestamps();
                
                // Foreign key constraints
                $table->foreign('from_wilayah_id')
                      ->references('id')
                      ->on('wilayah')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
                      
                $table->foreign('to_wilayah_id')
                      ->references('id')
                      ->on('wilayah')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
                
                // Unique constraint
                $table->unique(['from_wilayah_id', 'to_wilayah_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_distances');
    }
};
~~~~

## FILE: database/migrations/2026_07_16_075422_create_permission_tables.php

SHA-256: `9be723cec2a8625506c8e0073a4bbabca1fbb08dbbbfb67ab55fe3e93d7298f0`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), Exception::class, 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            // $table->engine('InnoDB');
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            // $table->engine('InnoDB');
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }

        });

        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};

~~~~

## FILE: database/migrations/2026_08_26_031245_add_audit_columns_to_periode_table.php

SHA-256: `2a9ec41d49888b2c7b54df61e105b8ae86b16947b00a1bd2966f9607eeaa1215`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->unsignedInteger('closed_by')->nullable()->after('closed_at');
            $table->timestamp('reopened_at')->nullable()->after('closed_by');
            $table->string('reopen_reason', 255)->nullable()->after('reopened_at');
        });
    }

    public function down(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dropColumn(['closed_by', 'reopened_at', 'reopen_reason']);
        });
    }
};
~~~~

## FILE: database/migrations/2026_08_27_010727_fix_bs_id_nullable_in_operasional_header.php

SHA-256: `30d2d34f48fa1df58ec2704c51d9aa82211000500a00dc40990fb5fd1044df19`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'transaksi_operasional_area_sales_header';
        $col = 'bs_id';
        $refTable = 'pengajuan_bs';
        $refCol = 'id';

        // 1. 🎯 CARI dan HAPUS semua foreign key yang menempel pada kolom ini
        $fks = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '{$table}' 
            AND COLUMN_NAME = '{$col}' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // 2. 🎯 UBAH tipe data menjadi INT UNSIGNED (agar sama dengan pengajuan_bs.id) 
        // dan buat menjadi NULLABLE.
        DB::statement("ALTER TABLE `{$table}` CHANGE `{$col}` `{$col}` INT UNSIGNED NULL DEFAULT NULL");

        // 3. 🎯 BUAT KEMBALI foreign key
        $fkName = 'fk_' . $table . '_' . $col;
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$col}`) REFERENCES `{$refTable}`(`{$refCol}`) ON DELETE SET NULL");
    }

    public function down(): void
    {
        $table = 'transaksi_operasional_area_sales_header';
        $col = 'bs_id';
        $refTable = 'pengajuan_bs';
        $refCol = 'id';
        $fkName = 'fk_' . $table . '_' . $col;

        // Hapus foreign key
        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");

        // Kembalikan ke BIGINT UNSIGNED dan NOT NULL (sesuai struktur awal)
        DB::statement("ALTER TABLE `{$table}` CHANGE `{$col}` `{$col}` BIGINT UNSIGNED NOT NULL");

        // Kembalikan foreign key
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$col}`) REFERENCES `{$refTable}`(`{$refCol}`) ON DELETE CASCADE");
    }
};
~~~~

## FILE: database/migrations/2026_08_27_085533_add_soft_deletes_to_detail_tables.php

SHA-256: `12f660a74f45952a4622fc49adb4fac6d0e95a76db608706df64d016daefd6fb`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    // Tambahkan ke tabel detail perjalanan dinas
    Schema::table('transaksi_perjalanan_dinas_detail', function (Blueprint $table) {
        $table->softDeletes(); // Menambahkan kolom deleted_at
    });
    
    // (Opsional: Lakukan hal yang sama untuk transaksi_operasional_area_sales_detail, dll)
}

public function down()
{
    Schema::table('transaksi_perjalanan_dinas_detail', function (Blueprint $table) {
        $table->dropSoftDeletes();
    });
}
};

~~~~

## FILE: database/migrations/2026_08_27_085556_create_audit_logs_table.php

SHA-256: `e9ba638ae0c715da30153f7034b0021e4c6ddbdde06b0e4f8b871c3c956307e7`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus tabel jika ada sisa dari percobaan sebelumnya yang gagal
        Schema::dropIfExists('audit_logs');

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // 🎯 PERBAIKAN UTAMA: Gunakan integer() biasa agar sama persis dengan 'int(11)' di tabel 'user'
            $table->integer('user_id'); 
            
            $table->string('action'); 
            $table->string('table_name'); 
            $table->unsignedBigInteger('record_id')->nullable(); 
            $table->text('reason')->nullable(); 
            $table->json('old_value')->nullable(); 
            $table->timestamps(); 
            
            // Foreign Key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('user')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
~~~~

## FILE: database/migrations/2026_09_04_065700_create_cash_flow_snapshots_table.php

SHA-256: `907d0d235c07728f598e64f82da3111b1834579ceca6f134daf5973c8ab1e20c`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🧹 Bersihkan sisa tabel dari percobaan yang gagal sebelumnya
        Schema::dropIfExists('cash_flow_snapshots');

        Schema::create('cash_flow_snapshots', function (Blueprint $table) {
            $table->id();
            
            // 🎯 TANPA foreign key constraint.
            // Tabel `user` adalah tabel legacy: tipe id-nya (INT) tidak cocok
            // dengan foreignId() (BIGINT UNSIGNED) → errno 150.
            // Relasi Eloquent tetap berfungsi normal tanpa FK di database.
            $table->unsignedInteger('user_id');
            
            $table->string('periode', 7); // Format: 2026-08
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('total_masuk', 15, 2)->default(0);
            $table->decimal('total_keluar', 15, 2)->default(0);
            $table->decimal('saldo_akhir', 15, 2)->default(0);
            $table->timestamp('last_updated')->useCurrent();

            // 🎯 1 snapshot per user per periode
            $table->unique(['user_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flow_snapshots');
    }
};
~~~~

## FILE: database/migrations/2026_09_08_015951_add_tipe_pengajuan_to_pengajuan_bs_table.php

SHA-256: `52e5560e7362c933521a17e5468ed74ba4eda4b5452817aa4fac149e66bf60b5`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_bs', function (Blueprint $table) {
            $table->string('tipe_pengajuan', 30)
                  ->default('operasional_sales')
                  ->after('periode');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_bs', function (Blueprint $table) {
            $table->dropColumn('tipe_pengajuan');
        });
    }
};
~~~~

## FILE: database/migrations/2026_09_08_051501_add_status_to_transaksi_operasional_header.php

SHA-256: `5f4243ab2305a73bce1391e55699d981252a3af454e17052853b56355525e704`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_operasional_area_sales_header', function (Blueprint $table) {
            // 🎯 Status: DRAFT (belum valid) atau VALIDATED (sudah valid & mempengaruhi cash flow)
            $table->enum('status', ['DRAFT', 'VALIDATED'])->default('DRAFT')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_operasional_area_sales_header', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
~~~~

## FILE: database/migrations/2026_09_10_044520_add_rejected_finance_to_pengajuan_bs_status_enum.php

SHA-256: `618a6600fc1bfec74ee8607cb9692b145eae877a430655160a3c82daaf5acc6a`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `pengajuan_bs` MODIFY COLUMN `status` ENUM('PENDING_ATASAN','APPROVED_ATASAN','REJECTED_ATASAN','REJECTED_FINANCE','DISBURSED','SETTLED') NULL DEFAULT 'PENDING_ATASAN'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `pengajuan_bs` MODIFY COLUMN `status` ENUM('PENDING_ATASAN','APPROVED_ATASAN','REJECTED_ATASAN','DISBURSED','SETTLED') NULL DEFAULT 'PENDING_ATASAN'");
    }
};
~~~~

## FILE: database/migrations/2026_09_16_100000_add_bs_cash_flow_and_travel_validation.php

SHA-256: `5cc665129b757a33b16f36c2f5deb001f2f51cbce0e9c48d0d5b8e9383b7a87b`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bs_cash_flow_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('periode', 7);
            foreach (['saldo_awal', 'total_masuk', 'total_keluar', 'penyesuaian', 'saldo_akhir'] as $column) {
                $table->decimal($column, 15, 2)->default(0);
            }
            $table->boolean('opening_final')->default(false);
            $table->timestamp('last_updated')->nullable();
            $table->unique(['user_id', 'periode']);
        });
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            // Existing travel records were posted immediately by the old workflow.
            // New records explicitly use DRAFT in the model and controller.
            $table->string('status', 16)->default('VALIDATED');
            $table->timestamp('validated_at')->nullable();
            $table->unsignedInteger('validated_by')->nullable();
        });
        Schema::table('transaksi_operasional_area_sales_header', function (Blueprint $table) {
            $table->timestamp('validated_at')->nullable();
            $table->unsignedInteger('validated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_operasional_area_sales_header', fn (Blueprint $table) => $table->dropColumn(['validated_at', 'validated_by']));
        Schema::table('transaksi_perjalanan_dinas_header', fn (Blueprint $table) => $table->dropColumn(['status', 'validated_at', 'validated_by']));
        Schema::dropIfExists('bs_cash_flow_snapshots');
    }
};

~~~~

## FILE: database/migrations/2026_09_17_100000_restore_readable_document_numbers.php

SHA-256: `33af6df1e3509287c521343ea18313781e34874c2709e8cf9097f9d7900e11b1`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_number_sequences', function (Blueprint $table) {
            $table->string('type', 8);
            $table->date('date');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->primary(['type', 'date']);
        });

        // Allow the numeric suffix to grow beyond 99 without truncating IDs.
        foreach (['transaksi_operasional_area_sales', 'transaksi_perjalanan_dinas'] as $base) {
            $header = $base.'_header';
            $detail = $base.'_detail';
            $foreignKeys = array_filter(Schema::getForeignKeys($detail), fn ($key) => in_array('transaksi_id', $key['columns'], true));
            foreach ($foreignKeys as $key) {
                Schema::table($detail, fn (Blueprint $table) => $table->dropForeign($key['name']));
            }
            foreach ([$header, $detail] as $name) {
                Schema::table($name, fn (Blueprint $table) => $table->string('transaksi_id', 32)->change());
            }
            foreach ($foreignKeys as $key) {
                Schema::table($detail, function (Blueprint $table) use ($key) {
                    $table->foreign($key['columns'], $key['name'])
                        ->references($key['foreign_columns'])->on($key['foreign_table'])
                        ->onUpdate($key['on_update'])->onDelete($key['on_delete']);
                });
            }
        }
    }

    public function down(): void
    {
        // Keep the wider columns: issued document numbers must never be truncated.
        Schema::dropIfExists('document_number_sequences');
    }
};

~~~~

## FILE: database/migrations/2026_09_18_100000_defer_closing_balances_until_period_close.php

SHA-256: `03757568f09bb5ac9dff1c54855282c575802e43c651e02cd5dd11c38f249204`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bind period values so legacy tables with different collations remain compatible.
        $closedPeriods = DB::table('periode')->where('status', 'CLOSED')->pluck('periode');
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('saldo_akhir', 15, 2)->nullable()->default(null)->change();
            });

            // Only an explicitly closed period can have a final balance.
            DB::table($name)->whereNotIn('periode', $closedPeriods)->update(['saldo_akhir' => null]);
        }
    }

    public function down(): void
    {
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $name) {
            DB::table($name)->whereNull('saldo_akhir')->update(['saldo_akhir' => 0]);
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('saldo_akhir', 15, 2)->nullable(false)->default(0)->change();
            });
        }
    }
};

~~~~

## FILE: database/migrations/2026_09_18_110000_create_stock_cash_flow_snapshots_table.php

SHA-256: `3cd77f67b98821bc8fa8e06bc010cd844f35ff083e9b61b2feb77c0964d369a5`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_cash_flow_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('periode', 7);
            foreach (['saldo_awal', 'total_masuk', 'total_keluar_bs', 'total_event_dalam', 'total_event_luar', 'total_keluar', 'penyesuaian'] as $column) {
                $table->decimal($column, 15, 2)->default(0);
            }
            $table->decimal('saldo_akhir', 15, 2)->nullable();
            $table->boolean('opening_final')->default(false);
            $table->timestamp('last_updated')->nullable();
            $table->unique(['user_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_cash_flow_snapshots');
    }
};

~~~~

## FILE: database/migrations/2026_09_21_100000_create_reimbursement_claims.php

SHA-256: `fa663e3f516bea03122c5189edda04a4b208f9657405acacc70c2b87d73e97d1`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reimbursement_claims', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type', 32);
            $table->unsignedInteger('transaction_id');
            $table->string('document_no', 64);
            $table->unsignedInteger('user_id')->index();
            $table->date('expense_date');
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->json('items');
            $table->string('status', 24)->default('PENDING_FINANCE')->index();
            $table->unsignedInteger('submitted_by');
            $table->timestamp('submitted_at');
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_reference', 150)->nullable();
            $table->timestamps();
            $table->unique(['transaction_type', 'transaction_id']);
        });
        Schema::create('reimbursement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reimbursement_claim_id')->constrained('reimbursement_claims')->cascadeOnDelete();
            $table->string('action', 24);
            $table->unsignedInteger('actor_id');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reimbursement_logs');
        Schema::dropIfExists('reimbursement_claims');
    }
};

~~~~

## FILE: database/migrations/2026_09_23_100000_add_payment_proofs_to_reimbursement_claims.php

SHA-256: `e5a300a20348926eafd05d18bc12875220f702d0c01d3191c3b310e00b860cd7`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursement_claims', function (Blueprint $table) {
            $table->json('payment_proofs')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reimbursement_claims', function (Blueprint $table) {
            $table->dropColumn('payment_proofs');
        });
    }
};

~~~~

## FILE: database/migrations/2026_09_23_110000_add_personal_funds_to_stock_cash_flow.php

SHA-256: `b0cb5cd3dccaf448598c71161e8f463510fdd0b960a2bff8aa1be6704813b33e`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_cash_flow_snapshots', function (Blueprint $table) {
            $table->decimal('total_keluar_pribadi', 15, 2)->default(0);
            $table->decimal('total_masuk_reimbursement', 15, 2)->default(0);
            // Closed reports retain their original scope until explicitly reopened.
            $table->boolean('includes_personal_funds')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('stock_cash_flow_snapshots', function (Blueprint $table) {
            $table->dropColumn(['total_keluar_pribadi', 'total_masuk_reimbursement', 'includes_personal_funds']);
        });
    }
};

~~~~

## FILE: database/migrations/2026_09_24_140000_add_pooled_bs_funding.php

SHA-256: `dd26cf4043f4c8f275a1b736a300675b99a7f65f6b3d65444bf3913c74289bc2`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['transaksi_operasional_area_sales_header' => 'oas', 'transaksi_perjalanan_dinas_header' => 'pd'] as $name => $prefix) {
            Schema::table($name, function (Blueprint $table) use ($prefix) {
                // NULL preserves legacy BS attribution and archived non-BS expenses.
                $table->unsignedInteger('bs_owner_id')->nullable();
                $table->index(['bs_owner_id', 'periode', 'status'], $prefix.'_bs_owner_period_index');
            });
        }

        Schema::create('bs_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type', 32);
            $table->unsignedInteger('transaction_id');
            $table->string('document_no', 64);
            $table->unsignedInteger('bs_owner_id')->index();
            $table->unsignedInteger('inputter_id');
            $table->unsignedInteger('actor_id');
            $table->string('periode', 7);
            $table->decimal('nominal', 15, 2);
            $table->timestamp('created_at');
            $table->unique(['transaction_type', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bs_usage_logs');
        foreach (['transaksi_operasional_area_sales_header' => 'oas', 'transaksi_perjalanan_dinas_header' => 'pd'] as $name => $prefix) {
            Schema::table($name, function (Blueprint $table) use ($prefix) {
                $table->dropIndex($prefix.'_bs_owner_period_index');
                $table->dropColumn('bs_owner_id');
            });
        }
    }
};

~~~~

## FILE: database/migrations/2026_09_24_160000_add_bs_owner_approval.php

SHA-256: `d9a2a761004bf749485236d5d853328021854b8d327b659d57321c74352b8606`

~~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            // Existing final transactions retain their status and historical attribution.
            $table->unsignedInteger('approval_version')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('submitted_by')->nullable();
            $table->timestamp('owner_reviewed_at')->nullable();
            $table->unsignedInteger('owner_reviewed_by')->nullable();
            $table->text('owner_rejection_reason')->nullable();
        });
        Schema::create('bs_usage_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('transaction_id')->index();
            $table->string('document_no', 64);
            $table->unsignedInteger('approval_version');
            $table->unsignedInteger('bs_owner_id');
            $table->unsignedInteger('inputter_id');
            $table->unsignedInteger('actor_id');
            $table->string('action', 16);
            $table->decimal('nominal', 15, 2);
            $table->text('reason')->nullable();
            $table->timestamp('created_at');
            $table->unique(['transaction_id', 'approval_version', 'action'], 'bs_usage_review_action_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bs_usage_approval_logs');
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            $table->dropColumn(['approval_version', 'submitted_at', 'submitted_by', 'owner_reviewed_at', 'owner_reviewed_by', 'owner_rejection_reason']);
        });
    }
};

~~~~

## FILE: database/seeders/BsPermissionSeeder.php

SHA-256: `bb5b4523303778762bbdcf150455dc03525a46ac9fea706ea171168627e91a09`

~~~~php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'bs.create',
            'bs.approve.atasan',
            'bs.approve.finance',
            'bs.report'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Sesuaikan nama Role dengan yang ada di database Anda
        // Role::findByName('Sales')->givePermissionTo('bs.create');
        // Role::findByName('Manager')->givePermissionTo('bs.approve.atasan');
        // Role::findByName('Finance')->givePermissionTo('bs.approve.finance');
    }
}
~~~~

## FILE: database/seeders/DatabaseSeeder.php

SHA-256: `cf8e4b6e48360218bb5534f66419c955288f78338eb8a3dba9d8ac3394ec3dbc`

~~~~php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}

~~~~

## FILE: database/seeders/WilayahSeeder.php

SHA-256: `466e1b6c253766f8a6e0ca1111d7eeb9e78aa443b875bdfd19451cc58208f8a4`

~~~~php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil semua data dari tabel mentah Kepmendagri
        // Kita urutkan dari kodenya (asc) agar Provinsi masuk lebih dulu, 
        // lalu Kabupaten, Kecamatan, dst. Ini wajib agar parent_id selalu ketemu.
        $dataKepmendagri = DB::table('wilayah_kepmendagri')->orderBy('kode', 'asc')->get();

        foreach ($dataKepmendagri as $data) {
            $kode = $data->kode;
            $nama = $data->nama;
            $parentId = null; // Default kosong (untuk level Provinsi)

            // 2. LOGIKA PARENT-CHILD (Memecah Titik)
            // Cek apakah ada karakter titik ('.') di dalam kode wilayah
            if (strpos($kode, '.') !== false) {
                // Pecah kode berdasarkan titik (Contoh: '11.01.05' jadi array ['11', '01', '05'])
                $bagian = explode('.', $kode);
                
                // Buang bagian paling akhir untuk mencari kode Bapak/Parent-nya (Jadi ['11', '01'])
                array_pop($bagian);
                
                // Gabungkan lagi dengan titik (Jadi '11.01')
                $kodeParent = implode('.', $bagian);

                // Cari ID dari Bapak/Parent tersebut di tabel 'wilayah' yang baru
                $parent = DB::table('wilayah')->where('code', $kodeParent)->first();
                
                if ($parent) {
                    $parentId = $parent->id; // Masukkan ID si Bapak ke data anak
                }
            }

            // 3. LOGIKA TERITORI (Jawa / Luar Jawa)
            $teritory = 'Luar Jawa'; // Anggap semua luar jawa dulu
            
            // Ambil 2 angka pertama dari kode (Ini adalah kode Provinsi)
            $kodeProvinsi = substr($kode, 0, 2);
            
            // Daftar kode provinsi di pulau Jawa (Banten, DKI, Jabar, Jateng, DIY, Jatim)
            $provinsiJawa = ['31', '32', '33', '34', '35', '36'];
            
            if (in_array($kodeProvinsi, $provinsiJawa)) {
                $teritory = 'Jawa'; // Ubah jadi Jawa jika kodenya cocok
            }

            // 4. MASUKKAN DATA KE TABEL WILAYAH (EMMO)
            DB::table('wilayah')->insert([
                'code' => $kode,
                'nama' => $nama,
                'parent_id' => $parentId,
                'teritory' => $teritory,
                'is_active' => 1
            ]);
        }
        
        $this->command->info('Data wilayah berhasil diadaptasi ke sistem EMMO!');
    }
}
~~~~
