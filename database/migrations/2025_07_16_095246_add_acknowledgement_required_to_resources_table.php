<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcknowledgementRequiredToResourcesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->boolean('acknowledgement_required')
                  ->default(false)
                  ->after('privacy')
                  ->comment('If true, employees must acknowledge this resource');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('acknowledgement_required');
        });
    }
}
