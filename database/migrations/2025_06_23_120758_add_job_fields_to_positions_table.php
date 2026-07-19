<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobFieldsToPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('positions')) {
            return;
        }

        Schema::table('positions', function (Blueprint $table) {
            // division_id (nullable first, you can remove ->nullable() once all rows have a division)
            if (! Schema::hasColumn('positions', 'division_id')) {
                $table->unsignedBigInteger('division_id')
                      ->nullable()
                      ->after('name');
                $table->foreign('division_id')
                      ->references('id')
                      ->on('divisions')
                      ->onDelete('cascade');
            }

            // classification (give default so existing rows get "")
            if (! Schema::hasColumn('positions', 'classification')) {
                $table->string('classification')
                      ->default('')
                      ->after('division_id');
            }

            // start_date (nullable to avoid zero-date errors)
            if (! Schema::hasColumn('positions', 'start_date')) {
                $table->date('start_date')
                      ->nullable()
                      ->after('classification');
            }

            // end_date (already nullable)
            if (! Schema::hasColumn('positions', 'end_date')) {
                $table->date('end_date')
                      ->nullable()
                      ->after('start_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('positions')) {
            return;
        }

        Schema::table('positions', function (Blueprint $table) {
            if (Schema::hasColumn('positions', 'division_id')) {
                $table->dropForeign(['division_id']);
                $table->dropColumn('division_id');
            }
            if (Schema::hasColumn('positions', 'classification')) {
                $table->dropColumn('classification');
            }
            if (Schema::hasColumn('positions', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('positions', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
}
