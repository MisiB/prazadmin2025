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
        Schema::create('departmentoutputs', function (Blueprint $table) {
            $table->id();
            $table->integer('output_id');
            $table->integer('department_id');
            $table->integer('weightage');
            $table->string('status')->default('PENDING');
            $table->string('createdby');
            $table->string('approvedby')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departmentoutputs');
    }
};
