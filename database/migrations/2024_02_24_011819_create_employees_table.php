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
        Schema::create('employees', function (Blueprint $table) {
            //asa e butang ang manager
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->string('pronouns')->nullable();
            $table->string('indigenous')->nullable();
            $table->string('personal_phone')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('work_email')->nullable();
            $table->string('job_title');
            $table->string('status');
            $table->string('address')->nullable();
            $table->string('abn')->nullable();
            $table->string('tax_number')->nullable();

            $table->date('dob')->nullable();
            $table->date('date_hired')->nullable();

            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('division_id');
            $table->foreign('division_id')->references('id')->on('divisions');
            $table->unsignedBigInteger('employment_id');
            $table->foreign('employment_id')->references('id')->on('employments');
            //$table->unsignedBigInteger('manager_id');
            //$table->foreign('manager_id')->references('id')->on('employees');
            $table->integer('manager_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
