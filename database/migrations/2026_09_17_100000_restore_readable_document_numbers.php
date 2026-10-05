<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_number_sequences', function (Blueprint $table) {
            $table->string('type', 8);
            $table->date('date');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->primary(['type', 'date']);
        });

        // Allow the numeric suffix to grow beyond 99 without truncating IDs.
        foreach (['transaksi_operasional_area_sales', 'transaksi_perjalanan_dinas'] as $base) {
            $header = $base.'_header';
            $detail = $base.'_detail';
            $foreignKeys = array_filter(Schema::getForeignKeys($detail), fn ($key) => in_array('transaksi_id', $key['columns'], true));
            foreach ($foreignKeys as $key) {
                Schema::table($detail, fn (Blueprint $table) => $table->dropForeign($key['name']));
            }
            foreach ([$header, $detail] as $name) {
                Schema::table($name, fn (Blueprint $table) => $table->string('transaksi_id', 32)->change());
            }
            foreach ($foreignKeys as $key) {
                Schema::table($detail, function (Blueprint $table) use ($key) {
                    $table->foreign($key['columns'], $key['name'])
                        ->references($key['foreign_columns'])->on($key['foreign_table'])
                        ->onUpdate($key['on_update'])->onDelete($key['on_delete']);
                });
            }
        }
    }

    public function down(): void
    {
        // Keep the wider columns: issued document numbers must never be truncated.
        Schema::dropIfExists('document_number_sequences');
    }
};
