<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToEmploymentTypesTable extends Migration
{
    public function up(): void
    {
        Schema::table('employment_types', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('employment_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
