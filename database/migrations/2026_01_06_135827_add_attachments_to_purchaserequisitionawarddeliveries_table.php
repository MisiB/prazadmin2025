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
        Schema::table('purchaserequisitionawarddeliveries', function (Blueprint $table) {
            $table->string('invoice_filepath')->nullable()->after('delivery_notes');
            $table->string('delivery_note_filepath')->nullable()->after('invoice_filepath');
            $table->string('tax_clearance_filepath')->nullable()->after('delivery_note_filepath');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchaserequisitionawarddeliveries', function (Blueprint $table) {
            $table->dropColumn(['invoice_filepath', 'delivery_note_filepath', 'tax_clearance_filepath']);
        });
    }
};
