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
        Schema::table('ts_allowances', function (Blueprint $table) {
            // Trip attachment (PDF or Word document)
            $table->string('trip_attachment_path')->nullable()->after('reason_for_allowances');

            // Meal options - specify which days meals apply
            // Options: 'all', 'first_day_only', 'last_day_only', 'first_and_last', 'none'
            $table->string('breakfast_option')->default('all')->after('breakfast');
            $table->string('lunch_option')->default('all')->after('lunch');
            $table->string('dinner_option')->default('all')->after('dinner');

            // Calculated meal days based on options
            $table->integer('breakfast_days')->default(0)->after('breakfast_option');
            $table->integer('lunch_days')->default(0)->after('lunch_option');
            $table->integer('dinner_days')->default(0)->after('dinner_option');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ts_allowances', function (Blueprint $table) {
            $table->dropColumn([
                'trip_attachment_path',
                'breakfast_option',
                'lunch_option',
                'dinner_option',
                'breakfast_days',
                'lunch_days',
                'dinner_days',
            ]);
        });
    }
};
