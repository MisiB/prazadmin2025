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
        Schema::table('ts_allowance_configs', function (Blueprint $table) {
            // Drop the old columns
            $table->dropUnique('unique_active_config');
            $table->dropIndex(['allowance_category']);
            $table->dropColumn(['allowance_category', 'rate', 'effective_date', 'version']);

            // Add individual rate columns (all nullable)
            $table->decimal('out_of_station_subsistence_rate', 15, 2)->nullable()->after('grade_band_id');
            $table->decimal('overnight_allowance_rate', 15, 2)->nullable()->after('out_of_station_subsistence_rate');
            $table->decimal('bed_allowance_rate', 15, 2)->nullable()->after('overnight_allowance_rate');
            $table->decimal('breakfast_rate', 15, 2)->nullable()->after('bed_allowance_rate');
            $table->decimal('lunch_rate', 15, 2)->nullable()->after('breakfast_rate');
            $table->decimal('dinner_rate', 15, 2)->nullable()->after('lunch_rate');
            $table->decimal('fuel_rate', 15, 2)->nullable()->after('dinner_rate');
            $table->decimal('toll_gate_rate', 15, 2)->nullable()->after('fuel_rate');
            $table->decimal('mileage_rate_per_km', 15, 2)->nullable()->after('toll_gate_rate');
            $table->date('effective_from')->nullable()->after('mileage_rate_per_km');

            // Add new index for unique constraint (only grade_band and status)
            $table->index(['grade_band_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ts_allowance_configs', function (Blueprint $table) {
            // Drop the new columns
            $table->dropIndex(['grade_band_id', 'status']);
            $table->dropColumn([
                'out_of_station_subsistence_rate',
                'overnight_allowance_rate',
                'bed_allowance_rate',
                'breakfast_rate',
                'lunch_rate',
                'dinner_rate',
                'fuel_rate',
                'toll_gate_rate',
                'mileage_rate_per_km',
                'effective_from',
            ]);

            // Restore old columns
            $table->string('allowance_category')->after('id');
            $table->decimal('rate', 15, 2)->after('grade_band_id');
            $table->date('effective_date')->after('currency_id');
            $table->string('version')->nullable()->after('status');

            // Restore old indexes
            $table->unique(['allowance_category', 'grade_band_id', 'status'], 'unique_active_config');
            $table->index('allowance_category');
        });
    }
};
