<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bs_usage_checks', function (Blueprint $table) {
            $table->id();
            $table->string('document_no', 64)->unique();
            $table->date('tanggal_pengecekan')->index();
            // Polymorphic reference to OPR/PD.id; checked and locked by the service.
            // Keeping this logical allows snapshots to survive deletion of a draft.
            $table->string('jenis_transaksi', 3);
            $table->unsignedInteger('transaksi_pemakaian_id');
            $table->string('no_referensi', 32)->index();
            // Legacy user.id is a SIGNED INT, not a Laravel unsigned BIGINT.
            $table->integer('user_id');
            $table->integer('user_checker_id');
            $table->string('user_name');
            $table->string('checker_name');
            $table->string('periode', 7)->index();
            $table->char('source_fingerprint', 64);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('user')->restrictOnDelete();
            $table->foreign('user_checker_id')->references('id')->on('user')->restrictOnDelete();
            $table->index(['jenis_transaksi', 'transaksi_pemakaian_id', 'id'], 'bs_check_source_history');
        });

        Schema::create('bs_usage_check_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bs_usage_check_id')->constrained('bs_usage_checks')->restrictOnDelete();
            $table->integer('cost_category_id');
            $table->string('cost_category_name');
            $table->decimal('nominal_asal', 15, 2);
            $table->decimal('nominal_disetujui', 15, 2);
            $table->text('catatan')->nullable();
            $table->foreign('cost_category_id')->references('id')->on('cost_category')->restrictOnDelete();
            $table->unique(['bs_usage_check_id', 'cost_category_id'], 'bs_check_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bs_usage_check_details');
        Schema::dropIfExists('bs_usage_checks');
    }
};
