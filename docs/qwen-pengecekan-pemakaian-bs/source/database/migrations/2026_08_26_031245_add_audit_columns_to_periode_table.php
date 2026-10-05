<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->unsignedInteger('closed_by')->nullable()->after('closed_at');
            $table->timestamp('reopened_at')->nullable()->after('closed_by');
            $table->string('reopen_reason', 255)->nullable()->after('reopened_at');
        });
    }

    public function down(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dropColumn(['closed_by', 'reopened_at', 'reopen_reason']);
        });
    }
};