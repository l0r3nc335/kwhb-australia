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
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zip_code');
        });

        Schema::table('divisions', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->after('name');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->after('name');
        });

        Schema::table('employments', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->after('name');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
