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
        Schema::create('ts_allowance_configs', function (Blueprint $table) {
            $table->id();
            $table->string('allowance_category'); // Out of Town, Overnight, Breakfast, Lunch, Dinner
            $table->foreignId('grade_band_id')->constrained('grade_bands');
            $table->decimal('rate', 15, 2); // USD amount
            $table->foreignId('currency_id')->nullable()->constrained('currencies');
            $table->date('effective_date');
            $table->string('status')->default('INACTIVE'); // ACTIVE, INACTIVE, PENDING_APPROVAL
            $table->string('version')->nullable(); // For versioning
            $table->string('created_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Only one active configuration per category and grade band
            $table->unique(['allowance_category', 'grade_band_id', 'status'], 'unique_active_config');
            $table->index('allowance_category');
            $table->index('grade_band_id');
            $table->index('status');
            $table->index('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_allowance_configs');
    }
};
