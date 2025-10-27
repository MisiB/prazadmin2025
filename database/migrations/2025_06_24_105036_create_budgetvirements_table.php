<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgetvirements', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid");
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->integer('from_budgetitem_id');
            $table->integer('to_budgetitem_id');
            $table->decimal('amount', 10, 2);
            $table->text('description');
            $table->string('status')->default('PENDING');
            $table->string('user_id');
            $table->string('approvedby')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgetvirements');
    }
};
