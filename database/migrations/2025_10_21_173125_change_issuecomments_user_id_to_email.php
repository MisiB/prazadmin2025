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
        Schema::table('issuecomments', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);

            // Drop the user_id column
            $table->dropColumn('user_id');

            // Add user_email column
            $table->string('user_email')->after('issuelog_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuecomments', function (Blueprint $table) {
            // Drop user_email column
            $table->dropColumn('user_email');

            // Re-add user_id column
            $table->uuid('user_id')->after('issuelog_id');

            // Re-add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
