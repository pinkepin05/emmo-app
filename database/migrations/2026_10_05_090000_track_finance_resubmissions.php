<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['transaksi_operasional_area_sales_header', 'transaksi_perjalanan_dinas_header'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedInteger('submission_version')->default(0);
                $t->unsignedBigInteger('finance_correction_check_id')->nullable();
            });
        }
        if (Schema::hasTable('bs_usage_logs')) {
            Schema::table('bs_usage_logs', function (Blueprint $t) {
                $t->unsignedInteger('submission_version')->default(0);
                $t->dropUnique(['transaction_type', 'transaction_id']);
                $t->unique(['transaction_type', 'transaction_id', 'submission_version'], 'bs_usage_submission_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bs_usage_logs')) {
            if (\Illuminate\Support\Facades\DB::table('bs_usage_logs')->groupBy('transaction_type', 'transaction_id')->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException('Rollback tidak dapat menghapus riwayat pengajuan ulang yang sudah tersimpan.');
            }
            Schema::table('bs_usage_logs', function (Blueprint $t) {
                $t->dropUnique('bs_usage_submission_unique');
                $t->unique(['transaction_type', 'transaction_id']);
                $t->dropColumn('submission_version');
            });
        }
        foreach ($this->tables as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn(['submission_version', 'finance_correction_check_id']));
        }
    }
};
