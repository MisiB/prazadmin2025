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
        Schema::create('staff_welfare_loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_welfare_loan_id')->constrained('staff_welfare_loans')->cascadeOnDelete();
            $table->decimal('amount_paid', 15, 2);
            $table->string('payment_method');
            $table->string('payment_reference');
            $table->date('payment_date');
            $table->string('proof_of_payment_path');
            $table->string('finance_officer_user_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('staff_welfare_loan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_welfare_loan_payments');
    }
};
