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
        Schema::create('purchaserequisitionawarddeliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchaserequisitionaward_id')
                ->constrained('purchaserequisitionawards')
                ->cascadeOnDelete()
                ->name('pr_award_deliveries_award_id_foreign');
            $table->integer('quantity_delivered');
            $table->date('delivery_date');
            $table->text('delivery_notes')->nullable();
            $table->string('delivered_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchaserequisitionawarddeliveries');
    }
};
