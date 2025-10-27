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
        Schema::create('accounttype_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounttype_id')->cascadeOnDelete()->index();
            $table->foreignId('user_id')->cascadeOnDelete()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounttype_users');
    }
};
