<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            // Existing final transactions retain their status and historical attribution.
            $table->unsignedInteger('approval_version')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('submitted_by')->nullable();
            $table->timestamp('owner_reviewed_at')->nullable();
            $table->unsignedInteger('owner_reviewed_by')->nullable();
            $table->text('owner_rejection_reason')->nullable();
        });
        Schema::create('bs_usage_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('transaction_id')->index();
            $table->string('document_no', 64);
            $table->unsignedInteger('approval_version');
            $table->unsignedInteger('bs_owner_id');
            $table->unsignedInteger('inputter_id');
            $table->unsignedInteger('actor_id');
            $table->string('action', 16);
            $table->decimal('nominal', 15, 2);
            $table->text('reason')->nullable();
            $table->timestamp('created_at');
            $table->unique(['transaction_id', 'approval_version', 'action'], 'bs_usage_review_action_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bs_usage_approval_logs');
        Schema::table('transaksi_perjalanan_dinas_header', function (Blueprint $table) {
            $table->dropColumn(['approval_version', 'submitted_at', 'submitted_by', 'owner_reviewed_at', 'owner_reviewed_by', 'owner_rejection_reason']);
        });
    }
};
