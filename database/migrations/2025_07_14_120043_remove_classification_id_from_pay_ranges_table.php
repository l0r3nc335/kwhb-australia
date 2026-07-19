<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveClassificationIdFromPayRangesTable extends Migration
{
    public function up()
    {
        Schema::table('pay_ranges', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['classification_id']);
            // Now drop the column
            $table->dropColumn('classification_id');
        });
    }

    public function down()
    {
        Schema::table('pay_ranges', function (Blueprint $table) {
            // Re-add the column and foreign key if rolling back
            $table->unsignedBigInteger('classification_id')->nullable();
            $table->foreign('classification_id')->references('id')->on('pay_classifications');
        });
    }
}
