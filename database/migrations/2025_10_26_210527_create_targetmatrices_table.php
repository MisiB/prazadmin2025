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
        Schema::create('targetmatrices', function (Blueprint $table) {
            $table->id();
            $table->integer('target_id');
            $table->string('month');
            $table->integer('target');
            $table->string('status')->default('PENDING');
            $table->string('createdby');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('targetmatrices');
    }
};
