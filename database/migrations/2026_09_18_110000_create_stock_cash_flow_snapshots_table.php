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
