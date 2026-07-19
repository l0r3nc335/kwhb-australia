<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDivisionHistoriesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('location_histories')) {
            Schema::create('location_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('location_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action');
                $table->text('activity');
                $table->timestamps();
    
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }
    

    public function down(): void
    {
        Schema::dropIfExists('division_histories');
    }
}
