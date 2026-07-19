<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayClassificationHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('pay_classification_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pay_classification_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->text('activity');
            $table->timestamps();

            $table->foreign('pay_classification_id')
                  ->references('id')->on('pay_classification')
                  ->cascadeOnDelete();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_classification_history');
    }
}
