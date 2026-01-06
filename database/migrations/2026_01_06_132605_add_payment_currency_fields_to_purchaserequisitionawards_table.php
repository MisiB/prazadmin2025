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
        Schema::table('purchaserequisitionawards', function (Blueprint $table) {
            $table->foreignId('payment_currency_id')->nullable()->after('currency_id')->constrained('currencies')->name('pr_award_payment_currency_id_foreign');
            $table->boolean('is_split_payment')->default(false)->after('payment_currency_id');
            $table->foreignId('second_payment_currency_id')->nullable()->after('is_split_payment')->constrained('currencies')->name('pr_award_second_payment_currency_id_foreign');
            $table->decimal('second_payment_amount', 10, 2)->nullable()->after('second_payment_currency_id');
            $table->boolean('pay_at_prevailing_rate')->default(false)->after('second_payment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchaserequisitionawards', function (Blueprint $table) {
            $table->dropForeign('pr_award_second_payment_currency_id_foreign');
            $table->dropForeign('pr_award_payment_currency_id_foreign');
            $table->dropColumn(['payment_currency_id', 'is_split_payment', 'second_payment_currency_id', 'second_payment_amount', 'pay_at_prevailing_rate']);
        });
    }
};
