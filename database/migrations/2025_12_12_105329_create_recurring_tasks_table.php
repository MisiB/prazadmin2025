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
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->unsignedBigInteger('task_template_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('priority')->default('Medium');
            $table->decimal('duration', 8, 2);
            $table->string('uom')->default('hours');
            $table->unsignedBigInteger('individualworkplan_id')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->integer('day_of_week')->nullable()->comment('1=Monday, 5=Friday');
            $table->integer('day_of_month')->nullable()->comment('1-31');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('last_created_date')->nullable();
            $table->date('next_create_date');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('task_template_id')->references('id')->on('task_templates')->onDelete('set null');
            $table->foreign('individualworkplan_id')->references('id')->on('individualworkplans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_tasks');
    }
};
