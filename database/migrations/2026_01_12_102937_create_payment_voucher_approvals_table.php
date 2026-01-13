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
        Schema::create('payment_voucher_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_voucher_id')->constrained('payment_vouchers')->cascadeOnDelete();
            $table->foreignId('workflowparameter_id')->constrained('workflowparameters');
            $table->string('user_id');
            $table->string('status'); // APPROVED / REJECTED
            $table->text('comment')->nullable();
            $table->string('authorization_code_hash')->nullable();
            $table->boolean('authorization_code_validated')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_approvals');
    }
};
