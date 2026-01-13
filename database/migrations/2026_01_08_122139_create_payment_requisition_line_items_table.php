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
        Schema::create('payment_requisition_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_requisition_id')->constrained('payment_requisitions')->cascadeOnDelete();
            $table->integer('quantity');
            $table->text('description');
            $table->decimal('unit_amount', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requisition_line_items');
    }
};
