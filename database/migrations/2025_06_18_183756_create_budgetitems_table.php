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
        Schema::create('budgetitems', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid");
            $table->foreignId('budget_id')->constrained();
            $table->foreignId('department_id')->constrained();
            $table->string('activity');
            $table->text('description');
            $table->foreignId('expensecategory_id')->constrained();
            $table->foreignId('strategysubprogramme_id')->constrained(); 
            $table->foreignId('sourceoffund_id')->constrained(); 
            $table->integer('quantity')->default(1);
            $table->float('unitprice', 8,2)->default(0.00);
            $table->float('total', 8,2)->default(0.00);
            $table->foreignId('currency_id')->constrained();
            $table->date("focusdate");
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('status')->default('PENDING');
            $table->string('type')->default('NEW');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgetitems');
    }
};
