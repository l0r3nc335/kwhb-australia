<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidFromAndAlterDescriptionInEmploymentTypesTable extends Migration
{
    public function up(): void
    {
        // Add 'valid_from' if not exists
        if (!Schema::hasColumn('employment_types', 'valid_from')) {
            Schema::table('employment_types', function (Blueprint $table) {
                $table->date('valid_from')->nullable()->after('description');
            });
        }

        // Change 'description' to BIGINT if it's not already BIGINT
        if (Schema::hasColumn('employment_types', 'description')) {
            // This will attempt to change it, but will only succeed if it's not BIGINT already
            Schema::table('employment_types', function (Blueprint $table) {
                $table->bigInteger('description')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Drop 'valid_from' if exists
        if (Schema::hasColumn('employment_types', 'valid_from')) {
            Schema::table('employment_types', function (Blueprint $table) {
                $table->dropColumn('valid_from');
            });
        }

        // Change 'description' back to string if exists
        if (Schema::hasColumn('employment_types', 'description')) {
            Schema::table('employment_types', function (Blueprint $table) {
                $table->string('description')->nullable()->change();
            });
        }
    }
}
