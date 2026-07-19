<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pay_ranges', function (Blueprint $table) {
            $table->decimal('start_range', 20, 10)->change();
            $table->decimal('end_range', 20, 10)->change();
        });
    }

    public function down()
    {
        Schema::table('pay_ranges', function (Blueprint $table) {
            $table->string('start_range')->change();
            $table->string('end_range')->change();
        });
    }
};
