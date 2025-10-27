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
        Schema::create('wallettopups', function (Blueprint $table) {
            $table->id();
            $table->integer("customer_id");
            $table->integer('currency_id');
            $table->string('year');
            $table->string('type');
            $table->string('accountnumber');
            $table->string('amount');
            $table->string('status')->default("PENDING");
            $table->string('initiatedby');
            $table->string('approvedby')->nullable();
            $table->text('reason');
            $table->text('rejectedreason')->nullable();
            $table->integer("banktransaction_id")->nullable();
            $table->integer("suspense_id")->nullable();
            $table->string('linkedby')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallettopups');
    }
};
