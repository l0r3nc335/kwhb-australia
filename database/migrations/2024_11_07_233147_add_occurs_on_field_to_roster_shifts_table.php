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
            $table->string('occurs_on')->nullable()->after('occurs_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roster_shifts', function (Blueprint $table) {
            $table->dropColumn('occurs_on');
        });
    }
};
