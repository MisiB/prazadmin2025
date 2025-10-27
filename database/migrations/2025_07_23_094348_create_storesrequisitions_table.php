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
        Schema::create('storesrequisitions', function (Blueprint $table) {
            $table->id();
            $table->uuid('storesrequisition_uuid')->unique();
            $table->text('itemdetail');
            $table->integer('requiredquantity');
            $table->integer('issuedquantity')->nullable();
            $table->text('purposeofrequisition');
            $table->char('status');
            $table->string('initiator_id');
            $table->binary('initiatorsignature');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storesrequisitions');
    }
};
