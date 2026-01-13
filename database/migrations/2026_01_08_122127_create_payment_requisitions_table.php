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
        Schema::create('payment_requisitions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference_number')->unique();
            $table->string('source_type'); // USER / PURCHASE_REQUISITION
            $table->unsignedBigInteger('source_id')->nullable(); // FK to source (nullable for USER)
            $table->foreignId('budget_id')->constrained('budgets');
            $table->foreignId('budget_line_item_id')->constrained('budgetitems');
            $table->string('purpose');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('DRAFT'); // Draft / Submitted / HOD_RECOMMENDED / ADMIN_REVIEWED / ADMIN_RECOMMENDED / AWAITING_PAYMENT_VOUCHER / Rejected
            $table->string('created_by');
            $table->string('recommended_by_hod')->nullable();
            $table->string('reviewed_by_admin')->nullable();
            $table->string('recommended_by_admin')->nullable();
            $table->string('approved_by_final')->nullable();
            $table->json('comments')->nullable();
            $table->integer('year');
            $table->foreignId('workflow_id')->nullable()->constrained('workflows');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requisitions');
    }
};
