<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCasualFieldsToPayClassificationTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pay_classification', function (Blueprint $table) {
            $table->string('casual_ot_weekday_sat_first_3')->nullable()->after('ordinary_hours');
            $table->string('casual_ot_weekday_sat_after_3')->nullable()->after('casual_ot_weekday_sat_first_3');
            $table->string('casual_ot_sun_ph_all')->nullable()->after('casual_ot_weekday_sat_after_3');
            $table->string('casual_ordinary_hours')->nullable()->after('casual_ot_sun_ph_all');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pay_classification', function (Blueprint $table) {
            $table->dropColumn([
                'casual_ot_weekday_sat_first_3',
                'casual_ot_weekday_sat_after_3',
                'casual_ot_sun_ph_all',
                'casual_ordinary_hours',
            ]);
        });
    }
}
