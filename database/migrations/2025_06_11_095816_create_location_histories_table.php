<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationHistoriesTable extends Migration
{
    public function up(): void
    {
        // Check if the table already exists before creating
        if (!Schema::hasTable('location_histories')) {
            Schema::create('location_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('location_id');
                $table->unsignedBigInteger('user_id')->nullable(); // Must match users.id type
                $table->string('action'); // e.g., Created, Archived, Activated, Name Change
                $table->text('activity'); // Optional detailed message
                $table->timestamps();

                $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('location_histories');
    }
}
