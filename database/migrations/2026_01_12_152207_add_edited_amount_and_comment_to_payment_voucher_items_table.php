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
        Schema::table('payment_voucher_items', function (Blueprint $table) {
            $table->decimal('edited_amount', 15, 2)->nullable()->after('original_amount');
            $table->text('amount_change_comment')->nullable()->after('edited_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_voucher_items', function (Blueprint $table) {
            $table->dropColumn(['edited_amount', 'amount_change_comment']);
        });
    }
};
