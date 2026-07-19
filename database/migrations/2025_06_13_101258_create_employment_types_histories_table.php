<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmploymentTypesHistoriesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employment_types_histories')) {
            Schema::create('employment_types_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employment_type_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action');
                $table->text('activity');
                $table->timestamps();

                $table->foreign('employment_type_id')
                    ->references('id')
                    ->on('employment_types')
                    ->onDelete('cascade');

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_types_histories');
    }
}
