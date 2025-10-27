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
        Schema::create('purchaserequisitionapprovals', function (Blueprint $table) {
            $table->id();
            $table->foreignId("purchaserequisition_id")->constrained("purchaserequisitions");
            $table->foreignId('workflowparameter_id')->constrained("workflowparameters");
            $table->string("user_id");
            $table->string("status");
            $table->string("comment");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchaserequisitionapprovals');
    }
};
