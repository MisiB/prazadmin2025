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
        Schema::create('calenderworkusertasks', function (Blueprint $table) {
            $table->id();
            $table->integer('calendarweek_id');
            $table->string('user_id');
            $table->string('status')->default('pending');
            $table->string('supervisor_id')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calenderworkusertasks');
    }
};
