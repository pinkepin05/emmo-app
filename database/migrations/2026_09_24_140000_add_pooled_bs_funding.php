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
