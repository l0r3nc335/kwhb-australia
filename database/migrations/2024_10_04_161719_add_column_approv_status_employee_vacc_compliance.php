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
            $table->string('approval_status')->nullable()->after('exempt_reason');
            $table->text('review_comments')->nullable()->after('approval_status');
        });

        Schema::table('employee_compliances', function (Blueprint $table) {
            $table->string('approval_status')->nullable()->after('file_status');
            $table->text('review_comments')->nullable()->after('approval_status');
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
