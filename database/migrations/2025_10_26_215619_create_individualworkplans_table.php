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
        Schema::create('individualworkplans', function (Blueprint $table) {
            $table->id();
            $table->integer('strategy_id');
            $table->integer('year');
            $table->string('approver_id');  
            $table->string('user_id');  
            $table->integer('targetmatrix_id');  
            $table->string('month');
            $table->text('output');
            $table->text('indicator');
            $table->integer('weightage');
            $table->integer('target');
            $table->string('status')->default('PENDING');
            $table->string('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individualworkplans');
    }
};
