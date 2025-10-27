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
        Schema::create('purchaserequisitionawarddocuments', function (Blueprint $table) {
            $table->id();
            $table->foreignId("purchaserequisitionaward_id");            
            $table->string("document");
            $table->string("filepath");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchaserequisitionawarddocuments');
    }
};
