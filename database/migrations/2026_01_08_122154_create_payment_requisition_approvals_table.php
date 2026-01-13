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
        Schema::create('payment_requisition_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_requisition_id')->constrained('payment_requisitions')->cascadeOnDelete();
            $table->foreignId('workflowparameter_id')->constrained('workflowparameters');
            $table->string('user_id');
            $table->string('status'); // APPROVED / REJECTED / RECOMMEND / NOT_RECOMMEND
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requisition_approvals');
    }
};
