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
        Schema::table('v2leaves', function (Blueprint $table) {
            $table->text('reason')->after('date')->nullable();
            $table->text('remarks')->after('reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2leaves', function (Blueprint $table) {
            $table->dropColumn('remarks');
            $table->dropColumn('reason');
        });
    }
};
