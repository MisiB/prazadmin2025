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
        Schema::create('workplantasks', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->foreignId('workplan_id')->constrained();
            $table->text('title');
            $table->date("start_date");
            $table->date("end_date");
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
        Schema::dropIfExists('workplantasks');
    }
};
