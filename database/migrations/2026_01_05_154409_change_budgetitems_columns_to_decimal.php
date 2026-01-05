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
        Schema::table('budgetitems', function (Blueprint $table) {
            // Change float to decimal for precise monetary calculations
            $table->decimal('unitprice', 15, 2)->default(0)->change();
            $table->decimal('total', 15, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgetitems', function (Blueprint $table) {
            $table->float('unitprice', 8, 2)->default(0)->change();
            $table->float('total', 8, 2)->default(0)->change();
        });
    }
};
