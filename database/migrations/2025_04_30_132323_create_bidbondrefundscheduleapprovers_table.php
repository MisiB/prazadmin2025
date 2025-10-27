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
        Schema::create('bidbondrefundscheduleapprovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidbondrefundschedule_id')->constrained();
            $table->string('approvallevel');
            $table->foreignId('user_id')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('PENDING');
            $table->string('referencenumber');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bidbondrefundscheduleapprovers');
    }
};
