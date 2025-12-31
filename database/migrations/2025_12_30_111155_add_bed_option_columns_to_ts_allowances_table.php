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
            // Add bed allowance options similar to meal options
            $table->string('bed_option')->default('all')->after('bed_allowance');
            $table->integer('bed_nights')->default(0)->after('bed_option');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ts_allowances', function (Blueprint $table) {
            $table->dropColumn(['bed_option', 'bed_nights']);
        });
    }
};
