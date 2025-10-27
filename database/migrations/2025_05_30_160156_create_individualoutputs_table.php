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
        Schema::create('individualoutputs', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->foreignId('strategy_id')->references('id')->on('strategies')->constrained();
            $table->foreignId('subprogrammeoutput_id')->references('id')->on('strategysubprogrammeoutputs')->constrained();
            $table->text('output');
            $table->text('indicator');
            $table->integer("target");
            $table->integer("variance");
            $table->integer("weightage");
            $table->string("createdby");
            $table->string("approvedby")->nullable();
            $table->string("status")->default("PENDING");
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('individualoutputs')->onDelete('cascade');
            $table->json("comments")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individualoutputs');
    }
};
