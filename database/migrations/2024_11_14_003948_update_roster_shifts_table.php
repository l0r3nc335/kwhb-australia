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
        Schema::table('roster_shifts', function (Blueprint $table) {
            $table->dropColumn('occurs_at');
            $table->date('start_date')->nullable()->after('occurs_on');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roster_shifts', function (Blueprint $table) {
            $table->date('occurs_at')->nullable();
            $table->dropColumn('end_date');
            $table->dropColumn('start_date');
        });
    }
};
