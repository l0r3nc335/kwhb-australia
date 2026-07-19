<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEndDateToJobTitlesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('job_titles') && !Schema::hasColumn('job_titles', 'end_date')) {
            Schema::table('job_titles', function (Blueprint $table) {
                $table->date('end_date')->nullable()->after('start_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('job_titles') && Schema::hasColumn('job_titles', 'end_date')) {
            Schema::table('job_titles', function (Blueprint $table) {
                $table->dropColumn('end_date');
            });
        }
    }
}
