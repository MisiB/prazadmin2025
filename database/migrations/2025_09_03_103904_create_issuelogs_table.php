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
        Schema::create('issuelogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuegroup_id')->constrained();
            $table->foreignId('issuetype_id')->constrained();
            $table->string('ticketnumber');
            $table->string('regnumber');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('pending');
            $table->string('priority')->default('low');          
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuelogs');
    }
};
