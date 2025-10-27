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
        Schema::create('bidbonds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained();
            $table->foreignId('currency_id')->constrained();
            $table->foreignId('tenderfee_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->date("maturity_date");
            $table->string("refundstatus")->default("PENDING");
            $table->decimal("amount", 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bidbonds');
    }
};
