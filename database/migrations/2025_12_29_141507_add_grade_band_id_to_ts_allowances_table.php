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
            $table->foreignId('grade_band_id')
                ->nullable()
                ->after('grade')
                ->constrained('grade_bands')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ts_allowances', function (Blueprint $table) {
            $table->dropForeign(['grade_band_id']);
            $table->dropColumn('grade_band_id');
        });
    }
};
