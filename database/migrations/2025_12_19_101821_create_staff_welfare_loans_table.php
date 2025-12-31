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
        Schema::create('staff_welfare_loans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workflow_id')->constrained('workflows');
            $table->string('loan_number')->unique();
            $table->string('status')->default('DRAFT');

            // Applicant Section (Immutable After Submission)
            $table->string('employee_number');
            $table->string('applicant_user_id'); // Foreign key to users
            $table->string('full_name'); // Retrieved from users table
            $table->foreignId('department_id')->constrained('departments');
            $table->string('job_title');
            $table->date('date_joined');
            $table->decimal('loan_amount_requested', 15, 2);
            $table->text('loan_purpose');
            $table->integer('repayment_period_months');
            $table->boolean('applicant_digital_declaration')->default(false);
            $table->timestamp('submission_date')->nullable();

            // HR Section (Editable Only at HR Step)
            $table->string('employment_status')->nullable();
            $table->date('date_of_engagement')->nullable();
            $table->decimal('basic_salary', 15, 2)->nullable();
            $table->decimal('monthly_deduction_amount', 15, 2)->nullable();
            $table->decimal('existing_loan_balance', 15, 2)->nullable();
            $table->decimal('monthly_repayment', 15, 2)->nullable();
            $table->date('last_payment_date')->nullable();
            $table->text('hr_comments')->nullable();
            $table->boolean('hr_digital_confirmation')->default(false);
            $table->timestamp('hr_review_date')->nullable();

            // Finance Payment Section (Editable Only After MD Approval)
            $table->decimal('amount_paid', 15, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('proof_of_payment_path')->nullable();
            $table->string('finance_officer_user_id')->nullable();
            $table->boolean('finance_officer_confirmation')->default(false);
            $table->timestamp('payment_capture_date')->nullable();

            // Employee Acknowledgement Section (Final Step)
            $table->text('acknowledgement_of_debt_statement')->nullable();
            $table->boolean('employee_digital_acceptance')->default(false);
            $table->timestamp('acceptance_date')->nullable();

            // Audit Trail
            $table->json('comments')->nullable(); // For workflow comments
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('applicant_user_id');
            $table->index('loan_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_welfare_loans');
    }
};
