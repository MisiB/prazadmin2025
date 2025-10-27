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
        Schema::create('revenuepostingjobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventoryitem_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->string("start_date");
            $table->string("end_date");
            $table->string("status")->default("PENDING");
            $table->string("processed")->default("PENDING");
            $table->string("created_by");
            $table->string("approved_by")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenuepostingjobs');
    }
};
