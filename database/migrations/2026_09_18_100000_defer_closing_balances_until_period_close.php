<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bind period values so legacy tables with different collations remain compatible.
        $closedPeriods = DB::table('periode')->where('status', 'CLOSED')->pluck('periode');
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('saldo_akhir', 15, 2)->nullable()->default(null)->change();
            });

            // Only an explicitly closed period can have a final balance.
            DB::table($name)->whereNotIn('periode', $closedPeriods)->update(['saldo_akhir' => null]);
        }
    }

    public function down(): void
    {
        foreach (['bs_cash_flow_snapshots', 'cash_flow_snapshots'] as $name) {
            DB::table($name)->whereNull('saldo_akhir')->update(['saldo_akhir' => 0]);
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('saldo_akhir', 15, 2)->nullable(false)->default(0)->change();
            });
        }
    }
};
