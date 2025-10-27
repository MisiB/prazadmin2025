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
        Schema::create('strategies', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->string('name');
            $table->integer('startyear');
            $table->integer('endyear');
            $table->string('createdby');
            $table->string('updatedby')->nullable();
            $table->string('approvedby')->nullable();            
            $table->string("status")->default("Draft");
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
        Schema::dropIfExists('strategies');
    }
};
