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
        Schema::create('leavestatements', function (Blueprint $table) {
            $table->id();
            $table->foreignId("leavetype_id");
            $table->string("user_id");
            $table->integer("year");
            $table->string("month");
            $table->string("days");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leavestatements');
    }
};
