<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations: Add assignee_user_id field in employee_vaccinations table
     */
    public function up(): void
    {
        Schema::table('employee_compliances', function (Blueprint $table) {
            $table->integer('assignee_user_id')->default(null)->after('review_comments')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_compliances', function (Blueprint $table) {
            $table->dropColumn('assignee_user_id');
        });
    }
};
