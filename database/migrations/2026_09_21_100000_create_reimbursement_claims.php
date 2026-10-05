<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reimbursement_claims', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type', 32);
            $table->unsignedInteger('transaction_id');
            $table->string('document_no', 64);
            $table->unsignedInteger('user_id')->index();
            $table->date('expense_date');
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->json('items');
            $table->string('status', 24)->default('PENDING_FINANCE')->index();
            $table->unsignedInteger('submitted_by');
            $table->timestamp('submitted_at');
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_reference', 150)->nullable();
            $table->timestamps();
            $table->unique(['transaction_type', 'transaction_id']);
        });
        Schema::create('reimbursement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reimbursement_claim_id')->constrained('reimbursement_claims')->cascadeOnDelete();
            $table->string('action', 24);
            $table->unsignedInteger('actor_id');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reimbursement_logs');
        Schema::dropIfExists('reimbursement_claims');
    }
};
