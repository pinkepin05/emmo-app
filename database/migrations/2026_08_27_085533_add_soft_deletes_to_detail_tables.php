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
