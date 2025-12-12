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
        Schema::create('taskinstances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->date('date');
            $table->decimal('planned_hours', 8, 2)->default(0);
            $table->decimal('worked_hours', 8, 2)->default(0);
            $table->enum('status', ['ongoing', 'rolled_over', 'completed'])->default('ongoing');
            $table->timestamps();

            $table->index(['task_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taskinstances');
    }
};
