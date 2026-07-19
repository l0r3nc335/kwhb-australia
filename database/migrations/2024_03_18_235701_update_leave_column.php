<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->renameColumn('total_days', 'total_hours');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->float('total_hours')->change();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->float('balance')->change();
            $table->float('availed')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->integer('total_hours')->change();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->integer('balance')->change();
            $table->integer('availed')->change();
        });
    }
};
