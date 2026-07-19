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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->integer('category');
            $table->date('date');
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->integer('total')->default(0);
            $table->integer('break')->default(0);
            $table->text('comment')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_submitted')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
