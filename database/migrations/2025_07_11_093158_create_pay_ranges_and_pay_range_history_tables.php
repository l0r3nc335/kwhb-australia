<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create pay_ranges table
        Schema::create('pay_ranges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classification_id');
            $table->string('start_range');
            $table->string('end_range');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending', 'expired'])->default('active');
            $table->timestamps();

            $table->foreign('classification_id')
                ->references('id')->on('pay_classification')
                ->onDelete('cascade');
        });

        // Create pay_range_history table
        Schema::create('pay_range_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pay_range_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action')->nullable();
            $table->string('activity')->nullable();
            $table->timestamps();

            $table->foreign('pay_range_id')
                ->references('id')->on('pay_ranges')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_range_history');
        Schema::dropIfExists('pay_ranges');
    }
};
