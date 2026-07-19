<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidFromAndDivisionIdToLocationsTable extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->date('valid_from')->nullable()->after('id');
            $table->unsignedBigInteger('division_id')->nullable()->after('valid_from');

            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
            $table->dropColumn('valid_from');
        });
    }
}
