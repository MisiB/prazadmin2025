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
        Schema::create('bankreconciliationdatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("bankreconciliation_id")->constrained()->cascadeOnDelete();
            $table->string("tnxdate");
            $table->text("tnxdescription");
            $table->string("tnxreference");
            $table->string("tnxamount");
            $table->string("tnxtype");
            $table->string("balance");
            $table->string("status")->nullable();
            $table->integer("banktransaction_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankreconciliationdatas');
    }
};
