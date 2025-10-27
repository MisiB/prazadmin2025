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
        Schema::create('payeeattempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payeedetail_id')->constrained('payeedetails')->onDelete('cascade');
            $table->integer('onlinepayment_id');
            $table->string('uuid');
            $table->string('method');
            $table->string('poll_url')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payeeattempts');
    }
};
