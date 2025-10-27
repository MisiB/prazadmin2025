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
        Schema::table('issuelogs', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('issuetype_id')->constrained('departments');
            $table->string('assigned_to')->nullable()->after('department_id');
            $table->string('assigned_by')->nullable()->after('assigned_to');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuelogs', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'assigned_to', 'assigned_by', 'assigned_at']);
        });
    }
};
