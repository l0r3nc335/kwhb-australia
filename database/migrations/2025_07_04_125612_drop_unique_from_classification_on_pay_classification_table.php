<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniqueFromClassificationOnPayClassificationTable extends Migration
{
    public function up()
    {
        Schema::table('pay_classification', function (Blueprint $table) {
            $table->dropUnique('pay_classification_classification_unique');
        });
    }

    public function down()
    {
        Schema::table('pay_classification', function (Blueprint $table) {
            $table->unique('classification');
        });
    }
}
