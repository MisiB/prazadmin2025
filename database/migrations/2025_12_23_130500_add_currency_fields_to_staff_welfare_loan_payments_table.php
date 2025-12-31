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
        Schema::table('staff_welfare_loan_payments', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable()->after('staff_welfare_loan_id')->constrained('currencies');
            $table->foreignId('exchangerate_id')->nullable()->after('currency_id')->constrained('exchangerates');
            $table->decimal('amount_paid_original', 15, 2)->nullable()->after('exchangerate_id');
            $table->decimal('amount_paid_usd', 15, 2)->nullable()->after('amount_paid_original');
            $table->decimal('exchange_rate_used', 10, 4)->nullable()->after('amount_paid_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_welfare_loan_payments', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['exchangerate_id']);
            $table->dropColumn(['currency_id', 'exchangerate_id', 'amount_paid_original', 'amount_paid_usd', 'exchange_rate_used']);
        });
    }
};
