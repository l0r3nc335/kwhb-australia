<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations: Add position_id in employees table
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('position_id')->default(0)->after('job_title')->nullable(false);
            // Replace 'some_column' with the column after which you want to add position_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('position_id');
        });
    }
};
