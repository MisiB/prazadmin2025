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
        Schema::create('bidbondrefundschedulelists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidbondrefundschedule_id')->constrained();
            $table->foreignId('customer_id');
            $table->string("bankname");
            $table->string("accountnumber");
            $table->string("accountname");
            $table->string("banchcode");
            $table->string("country");
            $table->foreignId('currency_id')->constrained();
            $table->string("status")->default("PENDING");
            $table->string("paymentstatus")->default("PENDING");
            $table->string("paymentreference")->nullable();
            $table->string("paymentmethod")->nullable();
            $table->string("paymentdate")->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bidbondrefundschedulelists');
    }
};
