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
        Schema::table('budgetitems', function (Blueprint $table) {
            $table->foreignId('strategyprogramme_id')->nullable()->after('expensecategory_id')->constrained('strategyprogrammes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgetitems', function (Blueprint $table) {
            $table->dropForeign(['strategyprogramme_id']);
            $table->dropColumn('strategyprogramme_id');
        });
    }
};
