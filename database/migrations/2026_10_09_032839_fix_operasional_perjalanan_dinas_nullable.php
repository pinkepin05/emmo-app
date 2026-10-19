<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Ubah kolom jadi nullable
        Schema::table('stock_cash_flow_snapshots', function (Blueprint $table) {
            $table->decimal('total_keluar_operasional', 15, 2)->nullable()->change();
            $table->decimal('total_keluar_perjalanan_dinas', 15, 2)->nullable()->change();
        });

        // Preserve existing module totals, especially published closed periods.
    }

    public function down()
    {
        Schema::table('stock_cash_flow_snapshots', function (Blueprint $table) {
            $table->decimal('total_keluar_operasional', 15, 2)->default(0)->change();
            $table->decimal('total_keluar_perjalanan_dinas', 15, 2)->default(0)->change();
        });
    }
};
