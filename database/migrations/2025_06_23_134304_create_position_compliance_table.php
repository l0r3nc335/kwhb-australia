<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionComplianceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('position_compliance', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('compliance_id');
            $table->timestamps();

            // Composite primary key to prevent duplicates
            $table->primary(['position_id', 'compliance_id']);

            // Foreign key to positions table
            $table->foreign('position_id')
                  ->references('id')
                  ->on('positions')
                  ->onDelete('cascade');

            // Foreign key to compliances table
            $table->foreign('compliance_id')
                  ->references('id')
                  ->on('compliances')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('position_compliance');
    }
}
