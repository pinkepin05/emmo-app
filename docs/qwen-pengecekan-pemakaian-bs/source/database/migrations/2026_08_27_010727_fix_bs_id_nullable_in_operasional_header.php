<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'transaksi_operasional_area_sales_header';
        $col = 'bs_id';
        $refTable = 'pengajuan_bs';
        $refCol = 'id';

        // 1. 🎯 CARI dan HAPUS semua foreign key yang menempel pada kolom ini
        $fks = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '{$table}' 
            AND COLUMN_NAME = '{$col}' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // 2. 🎯 UBAH tipe data menjadi INT UNSIGNED (agar sama dengan pengajuan_bs.id) 
        // dan buat menjadi NULLABLE.
        DB::statement("ALTER TABLE `{$table}` CHANGE `{$col}` `{$col}` INT UNSIGNED NULL DEFAULT NULL");

        // 3. 🎯 BUAT KEMBALI foreign key
        $fkName = 'fk_' . $table . '_' . $col;
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$col}`) REFERENCES `{$refTable}`(`{$refCol}`) ON DELETE SET NULL");
    }

    public function down(): void
    {
        $table = 'transaksi_operasional_area_sales_header';
        $col = 'bs_id';
        $refTable = 'pengajuan_bs';
        $refCol = 'id';
        $fkName = 'fk_' . $table . '_' . $col;

        // Hapus foreign key
        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");

        // Kembalikan ke BIGINT UNSIGNED dan NOT NULL (sesuai struktur awal)
        DB::statement("ALTER TABLE `{$table}` CHANGE `{$col}` `{$col}` BIGINT UNSIGNED NOT NULL");

        // Kembalikan foreign key
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$col}`) REFERENCES `{$refTable}`(`{$refCol}`) ON DELETE CASCADE");
    }
};