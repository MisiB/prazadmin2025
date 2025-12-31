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
        Schema::create('ts_allowances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('workflow_id')->constrained('workflows');
            $table->string('application_number')->unique();
            $table->string('status')->default('DRAFT'); // DRAFT, SUBMITTED, UNDER_REVIEW, RECOMMENDED, APPROVED, FINANCE_VERIFIED, PAYMENT_PROCESSED, REJECTED, ARCHIVED

            // Applicant Section (Immutable After Submission)
            $table->string('full_name');
            $table->string('job_title');
            $table->foreignId('department_id')->constrained('departments');
            $table->string('grade');
            $table->date('trip_start_date');
            $table->date('trip_end_date');
            $table->text('reason_for_allowances');
            $table->boolean('applicant_digital_signature')->default(false);
            $table->timestamp('submission_date')->nullable();
            $table->string('applicant_user_id'); // Foreign key to users

            // Allowance Breakdown Section (Applicant Captured)
            $table->decimal('out_of_station_subsistence', 15, 2)->default(0);
            $table->decimal('overnight_allowance', 15, 2)->default(0);
            $table->decimal('bed_allowance', 15, 2)->default(0);
            $table->decimal('breakfast', 15, 2)->default(0);
            $table->decimal('lunch', 15, 2)->default(0);
            $table->decimal('dinner', 15, 2)->default(0);
            $table->decimal('fuel', 15, 2)->default(0);
            $table->decimal('toll_gates', 15, 2)->default(0);
            $table->decimal('mileage_estimated_distance', 15, 2)->default(0);
            $table->decimal('calculated_subtotal', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->integer('number_of_days')->default(0);

            // Recommendation Section (HOD Only)
            $table->string('recommendation_decision')->nullable(); // RECOMMENDED, NOT_RECOMMENDED
            $table->string('hod_name')->nullable();
            $table->string('hod_designation')->nullable();
            $table->boolean('hod_digital_signature')->default(false);
            $table->timestamp('recommendation_date')->nullable();
            $table->string('hod_user_id')->nullable(); // Foreign key to users
            $table->text('hod_comment')->nullable();

            // Approval Section (CEO Only)
            $table->string('approval_decision')->nullable(); // APPROVED, REJECTED
            $table->boolean('ceo_digital_signature')->default(false);
            $table->timestamp('approval_date')->nullable();
            $table->string('ceo_user_id')->nullable(); // Foreign key to users
            $table->text('ceo_comment')->nullable();

            // Finance Verification Section
            $table->json('verified_allowance_rates')->nullable(); // Store verified rates
            $table->decimal('verified_total_amount', 15, 2)->nullable();
            $table->foreignId('exchange_rate_id')->nullable()->constrained('exchangerates');
            $table->decimal('exchange_rate_applied', 15, 4)->nullable();
            $table->string('finance_officer_name')->nullable();
            $table->boolean('finance_digital_signature')->default(false);
            $table->timestamp('verification_date')->nullable();
            $table->string('finance_officer_user_id')->nullable(); // Foreign key to users
            $table->text('finance_comment')->nullable();

            // Payment Section
            $table->foreignId('currency_id')->nullable()->constrained('currencies');
            $table->decimal('amount_paid_usd', 15, 2)->nullable();
            $table->decimal('amount_paid_original', 15, 2)->nullable(); // Original currency amount
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('proof_of_payment_path')->nullable();
            $table->timestamp('payment_capture_date')->nullable();
            $table->text('payment_notes')->nullable();

            // Audit Trail
            $table->json('comments')->nullable(); // For workflow comments
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('applicant_user_id');
            $table->index('application_number');
            $table->index('trip_start_date');
            $table->index('trip_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_allowances');
    }
};
