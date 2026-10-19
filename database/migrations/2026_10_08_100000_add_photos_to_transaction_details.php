<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan kolom ke tabel detail operasional dan perjalanan dinas
        foreach (['transaksi_operasional_area_sales_detail', 'transaksi_perjalanan_dinas_detail'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('photo_path')->nullable();
                $table->string('photo_name')->nullable();
            });
        }
        
        // Tambahkan kolom ke tabel history pemeriksaan (jika ada)
        Schema::table('bs_usage_check_details', function (Blueprint $table) {
            $table->string('source_photo_path')->nullable();
            $table->string('source_photo_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bs_usage_check_details', fn (Blueprint $table) => $table->dropColumn(['source_photo_path', 'source_photo_name']));
        foreach (['transaksi_operasional_area_sales_detail', 'transaksi_perjalanan_dinas_detail'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['photo_path', 'photo_name']));
        }
    }
};