<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_has_permissions', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['permission_id']);
            
            // Drop the primary key
            $table->dropPrimary(['permission_id', 'model_id', 'model_type']);
            
            // Change the column type
            $table->char('model_id', 36)->change();
            
            // Re-add the primary key
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
            
            // Re-add the foreign key
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
            $table->dropPrimary(['permission_id', 'model_id', 'model_type']);
            $table->unsignedBigInteger('model_id')->change();
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });
    }
};