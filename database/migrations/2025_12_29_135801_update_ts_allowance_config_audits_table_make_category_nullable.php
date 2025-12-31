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
        Schema::table('ts_allowance_config_audits', function (Blueprint $table) {
            // Drop the allowance_category column as we no longer use categories
            $table->dropColumn('allowance_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ts_allowance_config_audits', function (Blueprint $table) {
            $table->string('allowance_category')->after('new_rate');
        });
    }
};
