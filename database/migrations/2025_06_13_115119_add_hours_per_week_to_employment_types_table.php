<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHoursPerWeekToEmploymentTypesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employment_types', 'hours_per_week')) {
            Schema::table('employment_types', function (Blueprint $table) {
                $table->bigInteger('hours_per_week')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employment_types', 'hours_per_week')) {
            Schema::table('employment_types', function (Blueprint $table) {
                $table->dropColumn('hours_per_week');
            });
        }
    }
}
