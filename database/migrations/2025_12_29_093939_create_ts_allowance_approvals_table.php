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
        Schema::create('ts_allowance_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ts_allowance_id')->constrained('ts_allowances');
            $table->foreignId('workflowparameter_id')->constrained('workflowparameters');
            $table->string('user_id'); // UUID from users table
            $table->string('status'); // APPROVED, REJECTED
            $table->text('comment')->nullable();
            $table->string('authorization_code_hash')->nullable();
            $table->boolean('authorization_code_validated')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('ts_allowance_id');
            $table->index('workflowparameter_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_allowance_approvals');
    }
};
