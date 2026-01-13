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
        Schema::create('payment_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_voucher_id')->constrained('payment_vouchers')->cascadeOnDelete();
            $table->string('source_type'); // PAYMENT_REQUISITION / TNS / STAFF_WELFARE / OTHER
            $table->unsignedBigInteger('source_id'); // Origin header record ID
            $table->unsignedBigInteger('source_line_id')->nullable(); // Origin line item ID (NULL for TNS/STAFF_WELFARE)
            $table->text('description'); // Retrieved from source
            $table->string('original_currency'); // Retrieved from source
            $table->decimal('original_amount', 15, 2); // Retrieved from source
            $table->decimal('exchange_rate', 15, 4)->nullable(); // Voucher level rate
            $table->decimal('payable_amount', 15, 2); // Calculated: original_amount × exchange_rate
            $table->timestamps();

            // Indexes for performance
            $table->index(['source_type', 'source_id', 'source_line_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_items');
    }
};
