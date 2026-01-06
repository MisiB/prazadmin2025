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
        Schema::table('purchaserequisitionawards', function (Blueprint $table) {
            $table->integer('quantity_delivered')->default(0)->after('quantity');
            $table->date('delivery_date')->nullable()->after('quantity_delivered');
            $table->text('delivery_notes')->nullable()->after('delivery_date');
            $table->string('delivered_by')->nullable()->after('delivery_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchaserequisitionawards', function (Blueprint $table) {
            $table->dropColumn(['quantity_delivered', 'delivery_date', 'delivery_notes', 'delivered_by']);
        });
    }
};
