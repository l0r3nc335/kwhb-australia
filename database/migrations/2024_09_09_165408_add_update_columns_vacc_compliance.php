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
            $table->text('comments')->nullable()->after('file');
        });

        Schema::table('employee_compliances', function (Blueprint $table) {
            $table->string('expiry_date')->change();
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
