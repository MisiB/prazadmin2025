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
        Schema::create('payment_voucher_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_voucher_id')->constrained('payment_vouchers')->cascadeOnDelete();
            $table->string('action'); // CREATE / VERIFY / CHECK / APPROVE / REJECT
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('user_id');
            $table->string('role')->nullable();
            $table->text('comments')->nullable();
            $table->decimal('exchange_rate', 15, 4)->nullable(); // Snapshot of exchange rate
            $table->timestamp('timestamp');
            $table->timestamps();

            // Indexes for performance
            $table->index('payment_voucher_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_audit_logs');
    }
};
