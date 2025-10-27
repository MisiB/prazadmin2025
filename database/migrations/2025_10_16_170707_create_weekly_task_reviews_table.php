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
        Schema::create('weekly_task_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->unsignedBigInteger('calendarweek_id')->nullable();
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->integer('total_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->integer('incomplete_tasks')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('total_hours_planned', 8, 2)->default(0);
            $table->decimal('total_hours_completed', 8, 2)->default(0);
            $table->json('task_reviews')->nullable(); // Store individual task reviews
            $table->text('overall_comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_submitted')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'week_start_date']);
            $table->unique(['user_id', 'week_start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_task_reviews');
    }
};
