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
        Schema::create('staff_welfare_loan_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_welfare_loan_id')->constrained('staff_welfare_loans')->cascadeOnDelete();
            $table->foreignId('workflowparameter_id')->constrained('workflowparameters');
            $table->string('user_id'); // Approver user ID
            $table->string('status'); // APPROVED or REJECTED
            $table->text('comment')->nullable();
            $table->string('authorization_code_hash')->nullable(); // Store hash for audit
            $table->boolean('authorization_code_validated')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('staff_welfare_loan_id');
            $table->index('workflowparameter_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_welfare_loan_approvals');
    }
};
