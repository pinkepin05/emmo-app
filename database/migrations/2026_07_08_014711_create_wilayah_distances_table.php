<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengecekan Defensif: Hanya eksekusi pembuatan tabel jika tabel belum ada di database
        if (!Schema::hasTable('wilayah_distances')) {
            Schema::create('wilayah_distances', function (Blueprint $table) {
                $table->id();
                
                // GANTI dengan unsignedInteger() karena tabel wilayah menggunakan int(11)
                $table->unsignedInteger('from_wilayah_id');
                $table->unsignedInteger('to_wilayah_id');
                
                $table->decimal('distance_km', 10, 2)->default(0)->comment('Jarak dalam KM');
                $table->timestamps();
                
                // Foreign key constraints
                $table->foreign('from_wilayah_id')
                      ->references('id')
                      ->on('wilayah')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
                      
                $table->foreign('to_wilayah_id')
                      ->references('id')
                      ->on('wilayah')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
                
                // Unique constraint
                $table->unique(['from_wilayah_id', 'to_wilayah_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_distances');
    }
};