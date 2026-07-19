<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. REMOVE THIS AFTER
     */
    public function up(): void
    {
        $tables = [
            'external_api_users',
            'oauth_access_tokens',
            'oauth_auth_codes',
            'oauth_clients',
            'oauth_refresh_tokens',
            'oauth_personal_access_clients',
        ];

        // Disable foreign key checks to avoid constraint errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
