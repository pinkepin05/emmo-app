<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `pengajuan_bs` MODIFY COLUMN `status` ENUM('PENDING_ATASAN','APPROVED_ATASAN','REJECTED_ATASAN','REJECTED_FINANCE','DISBURSED','SETTLED') NULL DEFAULT 'PENDING_ATASAN'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `pengajuan_bs` MODIFY COLUMN `status` ENUM('PENDING_ATASAN','APPROVED_ATASAN','REJECTED_ATASAN','DISBURSED','SETTLED') NULL DEFAULT 'PENDING_ATASAN'");
    }
};