<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bs_usage_checks', function (Blueprint $table) {
            $table->foreignId('revision_of_id')->nullable()->constrained('bs_usage_checks')->restrictOnDelete();
        });
        // Only confirmed documents belong to bs_usage_checks and its reporting relations.
        Schema::create('bs_usage_check_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_transaksi', 3);
            $table->unsignedInteger('transaksi_pemakaian_id');
            $table->string('no_referensi', 32);
            $table->integer('user_id');
            $table->integer('user_checker_id');
            $table->string('user_name');
            $table->string('checker_name');
            $table->date('tanggal_pengecekan');
            $table->string('periode', 7);
            $table->string('keputusan', 16);
            $table->text('catatan_keputusan')->nullable();
            $table->char('source_fingerprint', 64);
            $table->foreignId('revision_of_id')->nullable()->constrained('bs_usage_checks')->restrictOnDelete();
            $table->json('details');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('user')->restrictOnDelete();
            $table->foreign('user_checker_id')->references('id')->on('user')->restrictOnDelete();
            $table->unique(['jenis_transaksi', 'transaksi_pemakaian_id'], 'bs_check_one_draft_per_source');
        });
    }

    public function down(): void
    {
        if (DB::table('bs_usage_check_drafts')->exists() || DB::table('bs_usage_checks')->whereNotNull('revision_of_id')->exists()) {
            throw new RuntimeException('Draft and revision records must be archived before reverting this migration.');
        }
        Schema::dropIfExists('bs_usage_check_drafts');
        Schema::table('bs_usage_checks', fn (Blueprint $table) => $table->dropConstrainedForeignId('revision_of_id'));
    }
};
