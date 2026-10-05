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
