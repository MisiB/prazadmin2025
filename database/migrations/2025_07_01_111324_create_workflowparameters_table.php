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
        Schema::create('workflowparameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId("workflow_id")->constrained("workflows")->cascadeOnDelete();
            $table->string("status");
            $table->integer("order")->default(0);
            $table->foreignId("permission_id")->constrained("permissions")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflowparameters');
    }
};
