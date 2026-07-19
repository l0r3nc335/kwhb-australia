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
        Schema::create('roster_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roster_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->integer('type');
            $table->integer('recurrance')->nullable();
            $table->time('start');
            $table->time('end');
            $table->boolean('is_repeat')->default(false);
            $table->boolean('is_next_day_finish')->default(false);
            $table->date('occurs_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_shifts');
    }
};
