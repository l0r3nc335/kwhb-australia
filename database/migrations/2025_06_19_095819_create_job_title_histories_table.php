<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobTitleHistoriesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('job_title_histories')) {
            Schema::create('job_title_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_title_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action');
                $table->text('activity');
                $table->timestamps();

                $table->foreign('job_title_id')->references('id')->on('job_titles')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_title_histories');
    }
}
