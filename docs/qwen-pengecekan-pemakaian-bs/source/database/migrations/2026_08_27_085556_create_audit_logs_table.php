<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus tabel jika ada sisa dari percobaan sebelumnya yang gagal
        Schema::dropIfExists('audit_logs');

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // 🎯 PERBAIKAN UTAMA: Gunakan integer() biasa agar sama persis dengan 'int(11)' di tabel 'user'
            $table->integer('user_id'); 
            
            $table->string('action'); 
            $table->string('table_name'); 
            $table->unsignedBigInteger('record_id')->nullable(); 
            $table->text('reason')->nullable(); 
            $table->json('old_value')->nullable(); 
            $table->timestamps(); 
            
            // Foreign Key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('user')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};