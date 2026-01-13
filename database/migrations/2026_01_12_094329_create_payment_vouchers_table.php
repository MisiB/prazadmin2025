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
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('voucher_number')->unique();
            $table->date('voucher_date');
            $table->string('currency'); // ZiG / USD / Other
            $table->decimal('exchange_rate', 15, 4)->nullable(); // Required if ZiG
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('DRAFT'); // Draft / Verified / Checked / Finance Recommended / CEO Approved / Rejected
            $table->string('prepared_by');
            $table->string('verified_by')->nullable();
            $table->string('checked_by')->nullable();
            $table->string('finance_approved_by')->nullable();
            $table->string('ceo_approved_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_vouchers');
    }
};
