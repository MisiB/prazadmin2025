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
        Schema::create('strategyprogrammeoutcomeindicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategy_id')->references('id')->on('strategies')->constrained();
            $table->foreignId('programmeoutcome_id')->references('id')->on('strategyprogrammeoutcomes')->constrained();
            $table->text('indicator');
            $table->string("target");
            $table->string("uom");
            $table->string("variance");
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
        Schema::dropIfExists('strategyprogrammeoutcomeindicators');
    }
};
