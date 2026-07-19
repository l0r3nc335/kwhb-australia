<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayClassificationTable extends Migration
{
    public function up()
    {
        // Only create the table if it does not already exist
        if (! Schema::hasTable('pay_classification')) {
            Schema::create('pay_classification', function (Blueprint $table) {
                $table->id();

                // Classification identifiers & descriptions
                $table->string('classification')->unique();      // Classification Code
                $table->string('description');                   // Classification Description

                // Salary
                $table->string('annual_salary');                  // Annual Salary

                // Overtime bands (all stored as strings)
                $table->string('ot_weekday_sat_first_3');         // Mon–Fri & Sat: first 3 Additional Hrs worked
                $table->string('ot_weekday_sat_after_3');         // Mon–Fri & Sat: after 3 Additional Hrs worked
                $table->string('ot_sun_ph_all');                  // Sun & PH: all Additional Hrs worked

                // Public Holiday entitlement and ordinary hours
                $table->string('ph_time_off_ordinary_hours');     // Public Holidays Time off Ordinary Hours
                $table->string('ordinary_hours');                 // Ordinary Hours

                // Status
                $table->enum('status', ['active', 'inactive'])->default('active');

                $table->timestamps();  // created_at, updated_at
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pay_classification');
    }
}
