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
        Schema::create('ts_allowance_config_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ts_allowance_config_id')->constrained('ts_allowance_configs');
            $table->decimal('previous_rate', 15, 2)->nullable();
            $table->decimal('new_rate', 15, 2);
            $table->string('allowance_category');
            $table->foreignId('grade_band_id')->constrained('grade_bands');
            $table->string('changed_by');
            $table->timestamp('change_date');
            $table->date('effective_date');
            $table->string('approval_reference')->nullable();
            $table->text('change_reason')->nullable();
            $table->timestamps();

            $table->index('ts_allowance_config_id');
            $table->index('changed_by');
            $table->index('change_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_allowance_config_audits');
    }
};
