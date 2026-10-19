<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_closing_logs', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->index();
            $table->string('mode', 24);
            $table->unsignedInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->text('reason')->nullable();
            $table->json('excluded_transactions');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_closing_logs');
    }
};
