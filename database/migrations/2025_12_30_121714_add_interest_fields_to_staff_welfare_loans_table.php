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
        Schema::table('staff_welfare_loans', function (Blueprint $table) {
            $table->decimal('interest_rate_applied', 5, 2)->nullable()->after('loan_amount_requested')->comment('Interest rate at time of loan');
            $table->decimal('interest_amount', 15, 2)->nullable()->after('interest_rate_applied')->comment('Total interest amount');
            $table->decimal('total_repayment_amount', 15, 2)->nullable()->after('interest_amount')->comment('Principal + Interest');
            $table->string('basic_salary_hash')->nullable()->after('basic_salary')->comment('Hashed basic salary for privacy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_welfare_loans', function (Blueprint $table) {
            $table->dropColumn([
                'interest_rate_applied',
                'interest_amount',
                'total_repayment_amount',
                'basic_salary_hash',
            ]);
        });
    }
};
