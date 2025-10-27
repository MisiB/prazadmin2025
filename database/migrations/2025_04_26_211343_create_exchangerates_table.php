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
        Schema::create('exchangerates', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('primary_currency_id');
            $table->unsignedBigInteger('secondary_currency_id');
            $table->string('user_id');
            $table->string('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchangerates');
    }
};
