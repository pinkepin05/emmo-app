<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_cash_flow_snapshots', function (Blueprint $table) {
            $table->decimal('total_keluar_pribadi', 15, 2)->default(0);
            $table->decimal('total_masuk_reimbursement', 15, 2)->default(0);
            // Closed reports retain their original scope until explicitly reopened.
            $table->boolean('includes_personal_funds')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('stock_cash_flow_snapshots', function (Blueprint $table) {
            $table->dropColumn(['total_keluar_pribadi', 'total_masuk_reimbursement', 'includes_personal_funds']);
        });
    }
};
