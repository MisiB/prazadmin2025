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
        Schema::create('staff_welfare_loan_configs', function (Blueprint $table) {
            $table->id();
            $table->decimal('interest_rate', 5, 2)->default(0.00)->comment('Annual interest rate percentage');
            $table->integer('max_repayment_months')->default(24)->comment('Maximum repayment period in months');
            $table->decimal('max_loan_amount', 15, 2)->nullable()->comment('Maximum loan amount allowed');
            $table->decimal('min_loan_amount', 15, 2)->default(0)->comment('Minimum loan amount allowed');
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_welfare_loan_configs');
    }
};
