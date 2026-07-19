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
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('location_id')->nullable()->change();
            $table->unsignedBigInteger('division_id')->nullable()->change();
            $table->unsignedBigInteger('employment_id')->nullable()->change();
            $table->string('job_title')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->unsignedBigInteger('location_id')->change();
            $table->unsignedBigInteger('division_id')->change();
            $table->unsignedBigInteger('employment_id')->change();
            $table->string('job_title')->change();
        });
    }
};
