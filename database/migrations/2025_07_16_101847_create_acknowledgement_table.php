<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcknowledgementTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('acknowledgement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamps();

            $table->foreign('resource_id')->references('id')->on('resources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('acknowledgement');
    }
}
