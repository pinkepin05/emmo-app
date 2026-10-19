<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('stock_cash_flow_snapshots', function (Blueprint $table) {
        $table->decimal('total_keluar_operasional', 15, 2)->nullable()->after('total_keluar_bs');
        $table->decimal('total_keluar_perjalanan_dinas', 15, 2)->nullable()->after('total_keluar_operasional');
    });
}

    public function down()
    {
        Schema::table('stock_cash_flow_snapshots', function (Blueprint $table) {
            $table->dropColumn(['total_keluar_operasional', 'total_keluar_perjalanan_dinas']);
        });
    }
};