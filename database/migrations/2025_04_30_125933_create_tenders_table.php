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
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->integer("tender_id")->nullable();
            $table->string("tender_number")->unique();
            $table->string("tender_title");
            $table->string("tender_description")->nullable();
            $table->date("closing_date");
            $table->time("closing_time");
            $table->integer('tendertype_id')->nullable();
            $table->string("status")->default("ACTIVE");
            $table->json("suppliercategories")->nullable();
            $table->string("source")->default("EGP");
            $table->string("tender_url")->nullable();
            $table->string("tender_file")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
