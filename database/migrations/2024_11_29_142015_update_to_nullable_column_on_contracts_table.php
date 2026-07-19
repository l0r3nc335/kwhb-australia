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
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('owner_employee_id')->nullable()->change();
            $table->foreignId('assigned_employee_id')->nullable()->change();
            $table->foreignId('created_by_employee_id')->nullable()->change();
            $table->string('position_title')->nullable()->change();
            $table->date('expiry_date')->nullable()->change();
            $table->foreignId('location_id')->nullable()->change();
            $table->foreignId('employment_id')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
            $table->string('salary')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('owner_employee_id')->nullable(false)->change();
            $table->foreignId('assigned_employee_id')->nullable(false)->change();
            $table->string('position_title')->nullable(false)->change();
            $table->date('expiry_date')->nullable(false)->change();
            $table->foreignId('location_id')->nullable(false)->change();
            $table->foreignId('employment_id')->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
            $table->string('salary')->nullable(false)->change();
        });
    }
};
