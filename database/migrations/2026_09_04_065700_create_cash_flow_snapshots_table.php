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