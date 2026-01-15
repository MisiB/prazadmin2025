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
        Schema::table('payment_requisitions', function (Blueprint $table) {
            $table->string('payee_type')->nullable()->after('source_id'); // CUSTOMER or USER
            $table->string('payee_regnumber')->nullable()->after('payee_type');
            $table->string('payee_name')->nullable()->after('payee_regnumber');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requisitions', function (Blueprint $table) {
            $table->dropColumn(['payee_type', 'payee_regnumber', 'payee_name']);
        });
    }
};
