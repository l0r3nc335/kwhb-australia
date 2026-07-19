<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobTitlesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('job_titles')) {
            Schema::create('job_titles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->unsignedBigInteger('division_id');
                $table->string('classification');
                $table->date('start_date');
                $table->boolean('is_active')->default(1);
                $table->timestamps();
                $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_titles');
    }
}
