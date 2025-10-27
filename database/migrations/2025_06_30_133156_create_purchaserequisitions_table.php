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
        Schema::create('purchaserequisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budgetitem_id')->constrained('budgetitems');
            $table->integer("year");
            $table->uuid("uuid");
            $table->foreignId("department_id")->constrained("departments");
            $table->foreignId("workflow_id")->constrained("workflows");
            $table->string("prnumber");
            $table->integer("quantity");
            $table->text("description");
            $table->string("purpose");
            $table->string("requested_by");
            $table->string("recommended_by")->nullable();
            $table->string("fundavailable")->default("N");
            $table->string("status")->default("PENDING");
            $table->json("comments")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchaserequisitions');
    }
};
