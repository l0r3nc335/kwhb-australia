<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations: Test Data
     */
    public function up()
    {
        // Option 1: Use DB::table (Query Builder)
        DB::table('employee_vaccinations')
            ->whereNull('assignee_user_id')
            ->update(['assignee_user_id' => 119]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::table('employee_vaccinations')
            ->where('assignee_user_id', 119)
            ->update(['assignee_user_id' => null]);
    }
};
