<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['bs_cash_flow_snapshots', 'stock_cash_flow_snapshots'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->boolean('finance_confirmed')->default(false));
        }
    }

    public function down(): void
    {
        foreach (['bs_cash_flow_snapshots', 'stock_cash_flow_snapshots'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('finance_confirmed'));
        }
    }
};
