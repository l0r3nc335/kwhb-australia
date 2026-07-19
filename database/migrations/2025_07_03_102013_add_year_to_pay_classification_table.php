<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYearToPayClassificationTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pay_classification', function (Blueprint $table) {
            // Add the 'year' column *before* created_at
            $table->year('year')->after('ordinary_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pay_classification', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
}
