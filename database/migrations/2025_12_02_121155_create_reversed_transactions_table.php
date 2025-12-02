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
        Schema::create('reversed_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suspense_utilization_id');
            $table->unsignedBigInteger('suspense_id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('invoice_number');
            $table->string('receipt_number');
            $table->string('amount');
            $table->string('rate')->default('0.0004421157');
            $table->char('reversed_by', 36);
            $table->timestamp('reversed_at');
            $table->json('original_data')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('suspense_id')->references('id')->on('suspenses')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('reversed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reversed_transactions');
    }
};
