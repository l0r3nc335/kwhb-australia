<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToAcknowledgementTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('acknowledgement', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id')->nullable();

            // If you want to enforce foreign key constraint:
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('acknowledgement', function (Blueprint $table) {
            // Drop foreign key first if exists
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
