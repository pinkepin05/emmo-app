<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_bs', function (Blueprint $table) {
            $table->string('tipe_pengajuan', 30)
                  ->default('operasional_sales')
                  ->after('periode');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_bs', function (Blueprint $table) {
            $table->dropColumn('tipe_pengajuan');
        });
    }
};