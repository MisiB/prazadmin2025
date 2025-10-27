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
        Schema::create('leaverequests', function (Blueprint $table) {
            $table->id();
            $table->uuid("leaverequestuuid")->unique()->autoIncrement();
            $table->string("user_id");
            $table->string("leavetype_id");
            $table->string("startdate");
            $table->string("enddate");
            $table->string("returndate");
            $table->integer("daysappliedfor");
            $table->string("addressonleave");
            $table->string("reasonforleave");
            $table->string("attachment_src")->nullable();
            $table->binary('signature')->nullable();
            $table->char("status");
            $table->string("year");
            $table->string("actinghod_id")->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaverequests');
    }
};
