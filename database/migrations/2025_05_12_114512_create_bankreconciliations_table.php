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
        Schema::create('bankreconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("currency_id")->constrained()->cascadeOnDelete();
            $table->foreignId("bankaccount_id")->constrained()->cascadeOnDelete();
            $table->string("year");
            $table->string("start_date");
            $table->string("end_date");
            $table->string("closing_balance");
            $table->string("opening_balance");
            $table->string("filename");
            $table->string("status")->default("PENDING");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankreconciliations');
    }
};
