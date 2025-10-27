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
        Schema::create('paymentrequisitions', function (Blueprint $table) {
            $table->id();
            $table->integer("source_id");
            $table->string("source");
            $table->integer("quantity");
            $table->decimal("amount",10,2);
            $table->text("comment");
            $table->string("status")->default("PENDING");
            $table->string("created_by");
            $table->string("recommended_by")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paymentrequisitions');
    }
};
