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
        Schema::table('employee_vaccinations', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->after('file');
        });

        Schema::table('employee_compliances', function (Blueprint $table) {
            $table->boolean('is_active')->default(1)->after('comments');
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
