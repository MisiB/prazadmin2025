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
        Schema::create('strategysubprogrammeoutputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_id')->references('id')->on('strategies')->constrained();
            $table->foreignId('subprogramme_id')->references('id')->on('strategysubprogrammes')->constrained();
            $table->text('indicator');
            $table->integer("quantity");
            $table->integer("target");
            $table->integer("variance");
            $table->string("createdby");
            $table->string("approvedby")->nullable();
            $table->string("status")->default("PENDING");
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
        Schema::dropIfExists('strategysubprogrammeoutputs');
    }
};
