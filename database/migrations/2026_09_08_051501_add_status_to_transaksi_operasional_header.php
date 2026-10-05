<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_operasional_area_sales_header', function (Blueprint $table) {
            // 🎯 Status: DRAFT (belum valid) atau VALIDATED (sudah valid & mempengaruhi cash flow)
            $table->enum('status', ['DRAFT', 'VALIDATED'])->default('DRAFT')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_operasional_area_sales_header', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};