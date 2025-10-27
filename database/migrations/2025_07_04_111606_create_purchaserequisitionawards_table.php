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
        Schema::create('purchaserequisitionawards', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->integer('year');
            $table->string("item");
            $table->foreignId('purchaserequisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('currency_id');
            $table->decimal('amount', 10, 2);
            $table->integer('quantity');
            $table->string('tendernumber');
            $table->string('status')->default('PENDING');
            $table->string('created_by');
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchaserequisitionawards');
    }
};
