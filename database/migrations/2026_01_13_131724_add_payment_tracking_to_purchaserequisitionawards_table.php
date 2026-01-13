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
            $table->integer('quantity_paid')->default(0)->after('quantity_delivered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchaserequisitionawards', function (Blueprint $table) {
            $table->dropColumn('quantity_paid');
        });
    }
};
