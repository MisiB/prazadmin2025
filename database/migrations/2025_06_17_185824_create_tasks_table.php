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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string("uuid");
            $table->integer("calendarday_id");
            $table->string("title");
            $table->string("user_id");
            $table->integer("individualoutputbreakdown_id")->nullable();
            $table->integer("contribution")->nullable();
            $table->text("description");
            $table->string("status")->default("pending");
            $table->date("start_date");
            $table->date("end_date");
            $table->string("priority"); 
            $table->string("approvalstatus")->default("pending");
            $table->string("created_by");
            $table->string("updated_by")->nullable();
            $table->string("approved_by")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
